@props(['value', 'label', 'icon' => null])

<div class="rounded-xl border border-slate-800 bg-slate-900 p-5 text-center">
    @if($icon)
        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600/20 text-teal-400">
            {!! $icon !!}
        </div>
    @endif
    <div class="text-2xl font-bold text-white">{{ $value }}</div>
    <div class="mt-1 text-sm text-slate-400">{{ $label }}</div>
</div>
