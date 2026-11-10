<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Production\ProcedureCatalogService;
use App\Http\Resources\Api\V1\ProcedureCodeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** GET /api/v1/procedure-codes — OpenDental's code list for the key's office. */
class ProcedureCodeController extends ApiController
{
    public function index(Request $request, ProcedureCatalogService $catalog): AnonymousResourceCollection
    {
        $maxPerPage = (int) config('developer_api.max_per_page');

        $data = $request->validate([
            'codes' => ['nullable', 'string', 'max:2000', 'regex:/^[A-Za-z0-9.\-]+(,[A-Za-z0-9.\-]+)*$/'],
            'per_page' => ['nullable', 'integer', 'min:1', "max:{$maxPerPage}"],
            'cursor' => ['nullable', 'string'],
        ]);

        $page = $catalog->paginate(
            (int) $this->apiKey($request)->office_id,
            filled($data['codes'] ?? null) ? explode(',', $data['codes']) : [],
            (int) ($data['per_page'] ?? config('developer_api.per_page')),
        )->withQueryString();

        $this->recordRows($request, $page->count());

        return ProcedureCodeResource::collection($page);
    }
}
