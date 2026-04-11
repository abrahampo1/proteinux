@props(['protein'])

<a href="{{ route('proteins.show', $protein['protein_id']) }}"
   class="group block border border-ink-300 bg-ink-100/60 p-5 transition-all hover:border-signal-mint/60 hover:bg-ink-100">
    <div class="mb-3 flex items-start justify-between gap-3">
        <h3 class="font-serif text-lg leading-tight text-ink-900 group-hover:text-signal-mint-deep">
            {{ $protein['protein_name'] }}
        </h3>
        <x-category-badge :category="$protein['category']" />
    </div>

    <p class="mb-4 line-clamp-2 font-serif text-sm leading-relaxed text-ink-600">
        {{ $protein['description'] ?? $protein['organism'] ?? 'Sin descripción.' }}
    </p>

    <dl class="grid grid-cols-2 gap-x-4 gap-y-1 border-t border-ink-300 pt-3 font-mono text-[10px] uppercase tracking-wider text-ink-500">
        <div class="flex justify-between">
            <dt>longitud</dt>
            <dd class="text-ink-800 tabular-nums">{{ $protein['length'] }} aa</dd>
        </div>
        <div class="flex justify-between">
            <dt>pdb</dt>
            <dd class="text-ink-800">{{ $protein['pdb_id'] ?? '—' }}</dd>
        </div>
    </dl>
</a>
