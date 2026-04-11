@props(['protein'])

<a href="{{ route('proteins.show', $protein['protein_id']) }}"
   class="group block border border-ink-700 bg-ink-900/60 p-5 transition-all hover:border-signal-mint/60 hover:bg-ink-900">
    <div class="mb-3 flex items-start justify-between gap-3">
        <h3 class="font-serif text-lg leading-tight text-ink-50 group-hover:text-signal-mint">
            {{ $protein['protein_name'] }}
        </h3>
        <x-category-badge :category="$protein['category']" />
    </div>

    <p class="mb-4 line-clamp-2 font-serif text-sm leading-relaxed text-ink-300">
        {{ $protein['description'] ?? $protein['organism'] ?? 'No description available.' }}
    </p>

    <dl class="grid grid-cols-2 gap-x-4 gap-y-1 border-t border-ink-700 pt-3 font-mono text-[10px] uppercase tracking-wider text-ink-400">
        <div class="flex justify-between">
            <dt>length</dt>
            <dd class="text-ink-100 tabular-nums">{{ $protein['length'] }} aa</dd>
        </div>
        <div class="flex justify-between">
            <dt>pdb</dt>
            <dd class="text-ink-100">{{ $protein['pdb_id'] ?? '—' }}</dd>
        </div>
    </dl>
</a>
