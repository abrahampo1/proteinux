@props(['post'])

<article class="border border-ink-300 bg-ink-100/60 p-4 sm:p-5" id="post-{{ $post->id }}">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-3">
            <x-federated-author :author="$post->author()" />
            <x-origin-badge :domain="$post->origin_domain" />
        </div>
        <time class="font-mono text-[10px] uppercase tracking-wider text-ink-400" datetime="{{ $post->created_at->toIso8601String() }}">
            {{ $post->created_at->format('Y-m-d H:i') }}
        </time>
    </div>

    <div class="font-serif text-sm leading-relaxed text-ink-800 sm:text-base">
        {!! nl2br(e($post->body)) !!}
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-dashed border-ink-300 pt-3">
        @auth
            <button type="button"
                    class="font-mono text-[10px] uppercase tracking-wider text-ink-500 transition-colors hover:text-signal-mint"
                    onclick="document.getElementById('reply-form-{{ $post->id }}')?.classList.toggle('hidden')">
                responder
            </button>

            @if(auth()->id() === $post->user_id)
                <form action="{{ route('forum.posts.destroy', $post) }}" method="POST" class="inline"
                      onsubmit="return confirm('Eliminar esta respuesta?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="font-mono text-[10px] uppercase tracking-wider text-ink-400 transition-colors hover:text-signal-rust">
                        eliminar
                    </button>
                </form>
            @endif
        @endauth
    </div>

    @auth
        <form action="{{ route('forum.posts.store', $post->thread->slug) }}" method="POST"
              id="reply-form-{{ $post->id }}" class="mt-3 hidden">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $post->id }}">
            <textarea name="body" rows="3" required
                      class="input-lab mb-2"
                      placeholder="Escribe tu respuesta..."></textarea>
            <button type="submit" class="btn-primary">enviar respuesta</button>
        </form>
    @endauth

    @if($post->relationLoaded('replies') && $post->replies->isNotEmpty())
        <div class="mt-4 space-y-3 border-l-2 border-signal-mint/30 pl-4">
            @foreach($post->replies as $reply)
                <x-forum-post :post="$reply" />
            @endforeach
        </div>
    @endif
</article>
