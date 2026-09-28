<?php

namespace App\Domain\SiteBuilder;

use Anthropic\Beta\Messages\BetaMessage;
use Anthropic\Client;

/**
 * Site content from Claude (Messages API, official PHP SDK).
 *
 * - Structured outputs: every call returns JSON matching a schema from SitePrompts.
 * - Prompt caching: the static system text and the per-version brief/design carry cache
 *   breakpoints, so a run's page calls re-read them from cache.
 * - Refusal fallbacks: `fallbacks: 'default'` lets the API retry a declined request on
 *   its server-defined fallback model inside the same call.
 * - Adaptive thinking is the model default and counts toward max_tokens.
 */
final class ClaudeSiteContentGenerator implements SiteContentGenerator
{
    private const FALLBACK_BETA = 'server-side-fallback-2026-07-01';

    public function __construct(
        private readonly Client $client,
        private readonly SitePrompts $prompts,
        private readonly string $model,
        private readonly int $maxTokens,
        private readonly float $timeout,
    ) {}

    public function design(array $brief, ?array $logo): array
    {
        $content = [];
        if ($logo !== null) {
            $content[] = [
                'type' => 'image',
                'source' => ['type' => 'base64', 'mediaType' => $logo['media_type'], 'data' => $logo['data']],
            ];
        }
        $content[] = ['type' => 'text', 'text' => $this->prompts->designRequest()];

        $result = $this->call(
            system: [
                $this->cached($this->prompts->system()),
                $this->cached($this->prompts->brief($brief, $brief['logo_url'] ?? null)),
            ],
            content: $content,
            schema: $this->prompts->designSchema(),
        );

        return $result;
    }

    public function page(array $brief, array $design, array $page, ?string $revisionNotes): array
    {
        return $this->call(
            system: [
                $this->cached($this->prompts->system()),
                ['type' => 'text', 'text' => $this->prompts->brief($brief, $brief['logo_url'] ?? null)],
                $this->cached($this->prompts->designBlock($design)),
            ],
            content: [['type' => 'text', 'text' => $this->prompts->pageRequest($page, $revisionNotes)]],
            schema: $this->prompts->pageSchema(),
        );
    }

    /** @return array<string, mixed> the decoded JSON fields plus `usage` */
    private function call(array $system, array $content, array $schema): array
    {
        $message = $this->client->beta->messages->create(
            maxTokens: $this->maxTokens,
            messages: [['role' => 'user', 'content' => $content]],
            model: $this->model,
            fallbacks: 'default',
            outputConfig: ['format' => ['type' => 'json_schema', 'schema' => $schema]],
            system: $system,
            betas: [self::FALLBACK_BETA],
            requestOptions: ['timeout' => $this->timeout],
        );

        return $this->decode($message) + ['usage' => [
            'input' => $message->usage->inputTokens + (int) $message->usage->cacheCreationInputTokens,
            'output' => $message->usage->outputTokens,
            'cache_read' => (int) $message->usage->cacheReadInputTokens,
        ]];
    }

    private function decode(BetaMessage $message): array
    {
        if ($message->stopReason === 'refusal') {
            throw new SiteGenerationException('The model declined to write this content.');
        }
        if ($message->stopReason === 'max_tokens') {
            throw new SiteGenerationException('The response was cut off at the output limit (site_builder.max_tokens).');
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $data = json_decode($block->text, true);
                if (is_array($data)) {
                    return $data;
                }
                break;
            }
        }

        throw new SiteGenerationException('The response was not valid JSON.');
    }

    /** A system text block with a cache breakpoint after it. */
    private function cached(string $text): array
    {
        return ['type' => 'text', 'text' => $text, 'cacheControl' => ['type' => 'ephemeral']];
    }
}
