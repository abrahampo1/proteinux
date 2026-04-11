@props(['category'])

@php
    $colors = [
        'enzyme'          => 'border-violet-500/50 text-violet-700 bg-violet-50',
        'transport'       => 'border-sky-500/50 text-sky-700 bg-sky-50',
        'signaling'       => 'border-signal-mint/50 text-signal-mint-deep bg-signal-mint/5',
        'immune'          => 'border-rose-500/50 text-rose-700 bg-rose-50',
        'hormone'         => 'border-signal-amber/50 text-signal-amber-dim bg-signal-amber/5',
        'reporter'        => 'border-emerald-500/50 text-emerald-700 bg-emerald-50',
        'structural'      => 'border-ink-400 text-ink-600 bg-ink-150/40',
        'oncology'        => 'border-signal-rust/50 text-signal-rust bg-signal-rust/5',
        'dna-replication' => 'border-indigo-500/50 text-indigo-700 bg-indigo-50',
    ];
    $labels = [
        'enzyme'          => 'enzima',
        'transport'       => 'transporte',
        'signaling'       => 'señalización',
        'immune'          => 'inmune',
        'hormone'         => 'hormona',
        'reporter'        => 'reportero',
        'structural'      => 'estructural',
        'oncology'        => 'oncología',
        'dna-replication' => 'replicación adn',
    ];
    $classes = $colors[$category] ?? 'border-ink-400 text-ink-600 bg-ink-150/40';
    $label = $labels[$category] ?? str_replace('-', ' ', $category);
@endphp

<span class="inline-flex border px-2 py-0.5 font-mono text-[10px] uppercase tracking-wider {{ $classes }}">
    {{ $label }}
</span>
