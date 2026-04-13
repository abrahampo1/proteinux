@props(['domain' => null])

@if($domain)
    <span class="inline-flex items-center gap-1 border border-signal-violet/40 bg-signal-violet/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-violet">
        <span class="status-dot bg-signal-violet"></span>
        {{ $domain }}
    </span>
@endif
