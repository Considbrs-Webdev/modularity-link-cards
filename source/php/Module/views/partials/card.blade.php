<a href="{{ $card['link']['url'] ?? '#' }}" target="{{ $card['link']['target'] ?? '_self' }}" class="mod-link-cards__card"
    @if (($card['link']['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif>
    <div class="mod-link-cards__icon-wrapper" style="background-color: {{ $card['iconBackgroundColor'] }};">
        @if (!empty($card['icon']))
            @icon([
                'icon' => $card['icon'],
                'size' => 'lg',
                'classList' => [$card['iconClass']]
            ])
            @endicon
        @endif
    </div>

    <div class="mod-link-cards__content">
        @if (!empty($card['title']))
            @typography([
                'element' => 'h3',
                'variant' => 'h4',
                'classList' => ['mod-link-cards__title']
            ])
                {{ $card['title'] }}
            @endtypography
        @endif

        @if (!empty($card['description']))
            @typography([
                'element' => 'p',
                'classList' => ['mod-link-cards__description']
            ])
                {{ $card['description'] }}
            @endtypography
        @endif
    </div>
</a>
