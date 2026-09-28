{{--
  x-front-desk.pill — a status pill.

  Props (either):
    outcome — a \App\Domain\FrontDesk\FrontDeskTaxonomy::OUTCOMES key; label and tone come from the taxonomy
    label + tone — free text with a tone (emerald | sky | amber | rose | slate | violet)
--}}
@props([
    'outcome' => null,
    'label' => null,
    'tone' => 'slate',
])

@php
    if ($outcome !== null) {
        $def = \App\Domain\FrontDesk\FrontDeskTaxonomy::outcome($outcome);
        $label ??= $def['label'];
        $tone = $def['tone'];
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-semibold whitespace-nowrap '.\App\Domain\FrontDesk\FrontDeskTaxonomy::toneClasses($tone)]) }}>{{ $label }}</span>
