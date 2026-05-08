<li class="mod-link-cards__card">
    <div class="mod-link-cards__icon-wrapper"
        style="background-color:{{ $card['iconBackgroundColor'] }};color:{{ $card['iconColor'] }};">
        @if (!empty($card['icon']))
            @icon([
                'icon' => $card['icon'],
                'size' => 'lg',
                'classList' => ['mod-link-cards__icon']
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
                @if ($card['hasLink'])
                    <a href="{{ $card['link']['url'] }}" target="{{ $card['link']['target'] ?? '_self' }}"
                        @if (($card['link']['target'] ?? '_self') === '_blank') rel="noopener noreferrer" @endif
                        class="mod-link-cards__link">{{ $card['title'] }}</a>
                @else
                    {{ $card['title'] }}
                @endif
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
</li>
