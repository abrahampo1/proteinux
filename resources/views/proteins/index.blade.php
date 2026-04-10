@extends('layouts.app')

@section('title', 'Protein Catalog')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Protein Catalog</h1>
        <p class="mt-2 text-slate-400">Browse {{ count($proteins) }} proteins with real metadata from UniProt and PDB.</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('proteins.index') }}" class="mb-8 flex flex-col gap-3 sm:flex-row">
        <input type="text" name="search" value="{{ $currentSearch }}"
               placeholder="Search by name, organism, or keyword..."
               class="flex-1 rounded-lg border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
        <select name="category"
                class="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-slate-200 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $currentCategory === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $cat)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-500">
            Search
        </button>
        @if($currentSearch || $currentCategory)
            <a href="{{ route('proteins.index') }}" class="rounded-lg border border-slate-700 px-4 py-2.5 text-sm text-slate-400 hover:text-white">
                Clear
            </a>
        @endif
    </form>

    {{-- Grid --}}
    @if(!empty($proteins))
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($proteins as $protein)
                <x-protein-card :protein="$protein" />
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-slate-800 bg-slate-900 py-16 text-center">
            <p class="text-slate-400">No proteins found matching your search.</p>
            <a href="{{ route('proteins.index') }}" class="mt-3 inline-block text-sm text-teal-400 hover:underline">View all proteins</a>
        </div>
    @endif
</div>
@endsection
