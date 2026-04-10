@props(['category'])

@php
    $colors = [
        'enzyme'          => 'bg-violet-500/20 text-violet-300 border-violet-500/30',
        'transport'       => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
        'signaling'       => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
        'immune'          => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
        'hormone'         => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
        'reporter'        => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
        'structural'      => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        'oncology'        => 'bg-red-500/20 text-red-300 border-red-500/30',
        'dna-replication' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
    ];
    $classes = $colors[$category] ?? 'bg-slate-500/20 text-slate-300 border-slate-500/30';
@endphp

<span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize {{ $classes }}">
    {{ str_replace('-', ' ', $category) }}
</span>
