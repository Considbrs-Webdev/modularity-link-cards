@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
    {{ $postTitle }}
    @endtypography
@endif

@if (!empty($cards))
<div class="mod-link-cards">
    <div class="mod-link-cards__grid mod-link-cards__grid--{{ $columns }}">
        @foreach ($cards as $card)
            @include('partials.card', ['card' => $card])
        @endforeach
    </div>
</div>
@endif
