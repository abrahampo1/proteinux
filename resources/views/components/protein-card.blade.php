@props(['protein'])

<a href="{{ route('proteins.show', $protein['protein_id']) }}"
   class="group block rounded-xl border border-slate-800 bg-slate-900 p-5 transition-all hover:border-teal-500/40 hover:bg-slate-800/80">
    <div class="mb-3 flex items-start justify-between">
        <h3 class="font-semibold text-white group-hover:text-teal-400 transition-colors">
            {{ $protein['protein_name'] }}
        </h3>
        <x-category-badge :category="$protein['category']" />
    </div>

    <p class="mb-3 text-sm text-slate-400 line-clamp-2">
        {{ $protein['description'] ?? $protein['organism'] ?? 'No description' }}
    </p>

    <div class="flex items-center gap-3 text-xs text-slate-500">
        <span>{{ $protein['length'] }} aa</span>
        <span>&middot;</span>
        <span class="italic">{{ $protein['organism'] ?? 'Unknown' }}</span>
        @if(!empty($protein['pdb_id']))
            <span>&middot;</span>
            <span>PDB: {{ $protein['pdb_id'] }}</span>
        @endif
    </div>
</a>
