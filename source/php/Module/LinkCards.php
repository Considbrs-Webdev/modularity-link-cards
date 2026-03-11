<?php

declare(strict_types=1);

namespace ModularityLinkCards\Module;

/**
 * Class LinkCards
 * @package ModularityLinkCards\Module
 */
class LinkCards extends \Modularity\Module
{
    public $slug = 'link-cards';
    public $supports = [];

    public function init(): void
    {
        $this->nameSingular = __('Link Cards', 'modularity-link-cards');
        $this->namePlural = __('Link Cards', 'modularity-link-cards');
        $this->description = __('A grid of clickable link cards with icons.', 'modularity-link-cards');
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array
    {
        // Append field config
        $data = $this->getFields();

        // Process cards data
        $data['cards'] = $this->prepareCards($data['cards'] ?? []);
        $data['columns'] = $data['columns'] ?? '2';
        $data['gridClass'] = $this->getGridClass($data['columns']);

        return $data;
    }

    /**
     * Color theme mappings: background color => icon color
     * Kept for backward-compatibility when old string values are stored.
     */
    private const LEGACY_COLOR_THEMES = [
        '764a0f' => 'e7d6bf',
        '233b1f' => 'a0b990',
        'a0b990' => '2b512b',
        'ba8a48' => '5f3a0b',
    ];

    /**
     * Prepare cards data for the template
     * 
     * @param array $cards Raw cards from ACF
     * @return array Processed cards
     */
    private function prepareCards(array $cards): array
    {
        if (empty($cards)) {
            return [];
        }

        return array_map(function ($card) {
            $colorThemeRaw = $card['color_theme'] ?? '';

            [$bgColor, $iconColor] = $this->resolveColors($colorThemeRaw);

            $link    = $card['link'] ?? [];
            $hasLink = !empty($link['url']);

            return [
                'title'              => $card['title'] ?? '',
                'description'        => $card['description'] ?? '',
                'link'               => $link,
                'hasLink'            => $hasLink,
                'tag'                => $hasLink ? 'a' : 'div',
                'icon'               => $card['icon'] ?? '',
                'iconBackgroundColor' => $bgColor,
                'iconColor'          => $iconColor,
            ];
        }, $cards);
    }

    /**
     * Resolve background and icon colours from the stored field value.
     *
     * Accepts:
     *  - JSON string  {"mode":"theme","theme":"brown","backgroundColor":"#764a0f","iconColor":"#e7d6bf"}
     *  - JSON string  {"mode":"custom","backgroundColor":"#aabbcc","iconColor":"#112233"}
     *  - Array already decoded by ACF format_value
     *  - Legacy plain hex string (backward-compat) e.g. "764a0f"
     *
     * @param mixed $raw
     * @return array{string, string}  [bgHex, iconHex]
     */
    private function resolveColors(mixed $raw): array
    {
        // Already decoded by ACF format_value hook (array)
        if (is_array($raw)) {
            $bg   = $raw['backgroundColor'] ?? '#764a0f';
            $icon = $raw['iconColor']       ?? '#e7d6bf';
            return [$this->ensureHash($bg), $this->ensureHash($icon)];
        }

        // JSON string
        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $bg   = $decoded['backgroundColor'] ?? '#764a0f';
                $icon = $decoded['iconColor']       ?? '#e7d6bf';
                return [$this->ensureHash($bg), $this->ensureHash($icon)];
            }
        }

        // Fallback
        return ['#764a0f', '#e7d6bf'];
    }

    /**
     * Ensure a colour string has a leading #.
     */
    private function ensureHash(string $color): string
    {
        if ($color !== '' && $color[0] !== '#') {
            return '#' . $color;
        }
        return $color;
    }

    /**
     * Get grid class based on columns
     * 
     * @param string $columns Number of columns
     * @return string Grid class
     */
    private function getGridClass(string $columns): string
    {
        $gridClasses = [
            '2' => 'o-grid--half',
            '3' => 'o-grid--third',
            '4' => 'o-grid--quarter',
        ];

        return $gridClasses[$columns] ?? 'o-grid--half';
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return 'link-cards.blade.php';
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
