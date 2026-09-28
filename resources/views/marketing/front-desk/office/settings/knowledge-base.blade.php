<p class="text-[13px] text-slate-600 -mt-2">Configure and manage your knowledge base.</p>

<x-marketing.panel title="Knowledge Base" subtitle="This information will be used by your AI assistant to answer questions about your practice and guide responses. You can include facts, examples of conversations, and instructions about verbiage or tone.">
    <details class="rounded-lg border border-slate-200 mb-4">
        <summary class="px-4 py-2.5 cursor-pointer text-[13px] font-medium text-slate-800">Show examples and best practices</summary>
        <div class="px-4 pb-4 text-[12px] text-slate-700 leading-relaxed space-y-3">
            <div>
                <p class="font-medium">Do not:</p>
                <p>· Write long-winded answers. These usually sound unnatural and can be confusing.</p>
                <p>· Add details about scheduling such as appointment durations, what information to collect, etc.</p>
            </div>
            <div>
                <p class="font-medium">Do:</p>
                <p>· End your answers/examples with a question, this will keep the conversation flowing naturally.</p>
                <p>· If you want to transfer certain scenarios to the front desk, add examples where the assistant informs the caller they will transfer them.</p>
            </div>
            <div>
                <p>You can add a section for Q/A like this:</p>
                <p class="font-mono">Q: Do ya'll have parking?<br>A: Yes, we have a parking lot in the back of the building. Can I help you with anything else?</p>
            </div>
            <div>
                <p>You can also add facts like this:</p>
                <p>· Our FAX number is 5 5 5 - 5 5 5 - 5 5 5 5</p>
            </div>
        </div>
    </details>

    <textarea rows="18" placeholder="Q: Where are you located?&#10;A: …"
        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-[13px] leading-relaxed text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['content'] }}</textarea>
</x-marketing.panel>

@include('marketing.front-desk.office.settings._save')
