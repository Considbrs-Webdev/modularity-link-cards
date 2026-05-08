@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
        {{ $postTitle }}
    @endtypography
@elseif (!empty($cards))
    <h2 class="screen-reader-text">
        {{ ($postTitle ?? '') !== '' ? $postTitle : __('Link cards', 'modularity-link-cards') }}
    </h2>
@endif

@if (!empty($cards))
    <div class="mod-link-cards">
        <ul class="mod-link-cards__grid mod-link-cards__grid--{{ $columns }}">
            @foreach ($cards as $card)
                @include('partials.card', ['card' => $card])
            @endforeach
        </ul>
    </div>
@endif
