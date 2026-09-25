{{--
  x-marketing.wordpress-install — install instructions for a WordPress site, with this
  site's real key already in each example so nothing has to be edited by hand.

  Props: site-key (required), script-url (required)
--}}
@props(['siteKey', 'scriptUrl'])

@php
    $snippet = "<!-- DDS Growth Engine -->\n"
        ."<script>\n"
        ."  (function (w, d, k) {\n"
        ."    w.ddsq = w.ddsq || [];\n"
        ."    w.dds = w.dds || function () { w.ddsq.push(arguments); };\n"
        ."    var s = d.createElement('script');\n"
        ."    s.async = 1;\n"
        ."    s.src = '{$scriptUrl}';\n"
        ."    s.dataset.key = k;\n"
        ."    d.head.appendChild(s);\n"
        ."  })(window, document, '{$siteKey}');\n"
        ."</script>";

    $functionsPhp = "add_action('wp_head', function () { ?>\n"
        .$snippet."\n"
        ."<?php }, 1);";
@endphp

<details class="mt-4 border border-slate-200 rounded-xl overflow-hidden group">
    <summary class="flex items-center justify-between gap-3 px-4 py-2.5 bg-slate-50 cursor-pointer select-none">
        <span class="flex items-center gap-2 text-[12px] font-bold text-slate-800">
            {{-- lucide dropped brand icons, so this uses a generic one --}}
            <i data-lucide="layout-template" class="w-4 h-4 text-slate-400"></i>
            Installing on WordPress
        </span>
        <span class="text-[10px] font-semibold text-slate-400 group-open:hidden">Show</span>
        <span class="text-[10px] font-semibold text-slate-400 hidden group-open:inline">Hide</span>
    </summary>

    <div class="p-4 space-y-5">

        <div>
            <h4 class="text-[12px] font-bold text-slate-900">Option 1 — a header plugin (no code, recommended)</h4>
            <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                Install <span class="font-semibold">WPCode</span> (free) or <span class="font-semibold">Insert Headers and Footers</span>, then:
                <span class="font-semibold">WordPress admin → Code Snippets → Header &amp; Footer → Header</span>, paste the snippet above, Save.
                It loads on every page and survives theme updates.
            </p>
        </div>

        <div>
            <h4 class="text-[12px] font-bold text-slate-900">Option 2 — child theme <code class="text-[10px] bg-slate-100 px-1 py-0.5 rounded">functions.php</code></h4>
            <pre class="mt-1.5 text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>{{ $functionsPhp }}</code></pre>
        </div>

        <div>
            <h4 class="text-[12px] font-bold text-slate-900">Option 3 — page builder settings</h4>
            <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                Elementor: <span class="font-semibold">Site Settings → Custom Code → &lt;head&gt;</span>.
                Divi: <span class="font-semibold">Theme Options → Integration → head</span>.
                Astra, GeneratePress and most premium themes have a "header scripts" box.
            </p>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <h4 class="text-[12px] font-bold text-slate-900">Then fire the signup when a form is submitted</h4>
            <p class="text-[11px] text-slate-600 mt-1 mb-2 leading-relaxed">
                The snippet alone records visits. This is what turns a visitor into a lead — add it in the same place, below the snippet.
                Field names differ per form, so check them in the form editor first.
            </p>

            <p class="text-[11px] font-bold text-slate-700 mt-3 mb-1">Contact Form 7</p>
@verbatim
            <pre class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>&lt;script&gt;
document.addEventListener('wpcf7mailsent', function (e) {
  var f = {};
  (e.detail.inputs || []).forEach(function (i) { f[i.name] = i.value; });
  dds('signup', {
    email: f['your-email'],
    phone: f['your-phone'],
    name:  f['your-name'],
    form:  'cf7-' + e.detail.contactFormId
  });
});
&lt;/script&gt;</code></pre>

            <p class="text-[11px] font-bold text-slate-700 mt-3 mb-1">WPForms</p>
            <pre class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>&lt;script&gt;
jQuery(document).on('wpformsAjaxSubmitSuccess', function (e) {
  var $f = jQuery(e.target);
  dds('signup', {
    email: $f.find('input[type=email]').val(),
    phone: $f.find('input[type=tel]').val(),
    form:  'wpforms-' + $f.data('formid')
  });
});
&lt;/script&gt;</code></pre>

            <p class="text-[11px] font-bold text-slate-700 mt-3 mb-1">Gravity Forms</p>
            <pre class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>&lt;script&gt;
jQuery(document).on('gform_confirmation_loaded', function (e, formId) {
  dds('signup', { form: 'gravity-' + formId });
});
&lt;/script&gt;</code></pre>

            <p class="text-[11px] font-bold text-slate-700 mt-3 mb-1">Elementor Forms</p>
            <pre class="text-[11px] leading-relaxed bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto"><code>&lt;script&gt;
jQuery(document).on('submit_success', function (e) {
  var $f = jQuery(e.target);
  dds('signup', {
    email: $f.find('input[type=email]').val(),
    phone: $f.find('input[type=tel]').val(),
    form:  'elementor'
  });
});
&lt;/script&gt;</code></pre>
@endverbatim
        </div>

        <ul class="text-[11px] text-slate-600 space-y-1.5 list-disc pl-4 leading-relaxed border-t border-slate-100 pt-4">
            <li><span class="font-semibold text-slate-800">Clear the cache</span> after installing (WP Rocket, LiteSpeed, Cloudflare), or the change will not show for a while.</li>
            <li><span class="font-semibold text-slate-800">Do not</span> paste the snippet into a single post or page — it would track only that page.</li>
            <li><span class="font-semibold text-slate-800">Do not</span> edit the parent theme's <code class="text-[10px] bg-slate-100 px-1 py-0.5 rounded">header.php</code>: a theme update wipes it.</li>
            <li>If the form redirects to a thank-you page instead of submitting by AJAX, fire the signup on that page instead.</li>
        </ul>
    </div>
</details>
