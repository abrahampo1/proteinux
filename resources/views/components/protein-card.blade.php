@props(['protein'])

<a href="{{ route('proteins.show', $protein['protein_id']) }}"
   class="group block border border-ink-300 bg-ink-100/60 p-4 transition-all hover:border-signal-mint/60 hover:bg-ink-100 sm:p-5">
    <div class="mb-3 flex items-start justify-between gap-3">
        <h3 class="min-w-0 font-serif text-base leading-tight text-ink-900 group-hover:text-signal-mint-deep sm:text-lg">
            {{ $protein['protein_name'] }}
        </h3>
        <div class="shrink-0">
            <x-category-badge :category="$protein['category']" />
        </div>
    </div>

    <p class="mb-4 line-clamp-2 font-serif text-sm leading-relaxed text-ink-600">
        {{ $protein['description'] ?? $protein['organism'] ?? 'Sin descripción.' }}
    </p>

    <dl class="grid grid-cols-1 gap-x-4 gap-y-1 border-t border-ink-300 pt-3 font-mono text-[10px] uppercase tracking-wider text-ink-500 sm:grid-cols-2">
        <div class="flex justify-between gap-2">
            <dt>longitud</dt>
            <dd class="tabular-nums text-ink-800">{{ $protein['length'] }} aa</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt>pdb</dt>
            <dd class="truncate text-ink-800">{{ $protein['pdb_id'] ?? '—' }}</dd>
        </div>
    </dl>
</a>
