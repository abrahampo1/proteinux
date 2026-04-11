@props(['category'])

@php
    $colors = [
        'enzyme'          => 'border-violet-400/40 text-violet-300',
        'transport'       => 'border-sky-400/40 text-sky-300',
        'signaling'       => 'border-signal-mint/40 text-signal-mint',
        'immune'          => 'border-rose-400/40 text-rose-300',
        'hormone'         => 'border-signal-amber/40 text-signal-amber',
        'reporter'        => 'border-emerald-400/40 text-emerald-300',
        'structural'      => 'border-ink-500 text-ink-300',
        'oncology'        => 'border-signal-rust/40 text-signal-rust',
        'dna-replication' => 'border-indigo-400/40 text-indigo-300',
    ];
    $classes = $colors[$category] ?? 'border-ink-500 text-ink-300';
@endphp

<span class="inline-flex border px-2 py-0.5 font-mono text-[10px] uppercase tracking-wider {{ $classes }}">
    {{ str_replace('-', ' ', $category) }}
</span>
