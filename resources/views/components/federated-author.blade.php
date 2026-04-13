@props(['author' => null, 'showDomain' => true])

@php
    use App\Models\Federation\RemoteUser;
    use App\Models\User;

    $isRemote = $author instanceof RemoteUser;

    if ($author instanceof User) {
        $name = $author->name ?? $author->username;
        $fedId = $author->federatedId();
    } elseif ($author instanceof RemoteUser) {
        $name = $author->display_name ?? $author->username;
        $fedId = $author->federatedId();
    } else {
        $name = 'anon';
        $fedId = 'desconocido@local';
    }
@endphp

<span class="inline-flex items-center gap-1.5 font-mono text-[11px] tracking-wider">
    <span class="text-ink-900">{{ $name }}</span>
    @if($showDomain)
        <span class="text-ink-400">{{ '@'.Str::after($fedId, '@') }}</span>
    @endif
    @if($isRemote)
        <span class="inline-flex items-center gap-1 border border-signal-violet/40 bg-signal-violet/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-violet">
            remoto
        </span>
    @endif
</span>
