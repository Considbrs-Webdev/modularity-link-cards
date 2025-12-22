@php
    $linkUrl = $card['link']['url'] ?? '#';
    $linkTarget = $card['link']['target'] ?? '_self';
    $hasIcon = !empty($card['icon']);
    $iconBgColor = $card['iconBackgroundColor'] ?? '#7B5B3C';
@endphp

<a 
    href="{{ $linkUrl }}" 
    target="{{ $linkTarget }}"
    class="mod-link-cards__card"
    @if($linkTarget === '_blank')
        rel="noopener noreferrer"
    @endif
>
    <div class="mod-link-cards__icon-wrapper" style="background-color: {{ $iconBgColor }};">
        @if ($hasIcon)
            @icon([
                'icon' => $card['icon'],
                'size' => 'lg',
                'color' => 'white'
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

