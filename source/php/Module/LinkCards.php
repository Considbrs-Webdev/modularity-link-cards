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
     * View data for the module.
     *
     * ACF returns false or an empty string for a repeater with no rows, and
     * may return the columns select as an int. Both are normalised before use.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $data = $this->getFields();

        $cards = $data['cards'] ?? null;
        $data['cards'] = $this->prepareCards(is_array($cards) ? $cards : []);
        $data['columns'] = $this->normalizeColumns($data['columns'] ?? null);
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

        $prepared = [];

        foreach ($cards as $card) {
            if (!is_array($card)) {
                continue;
            }

            $colorThemeRaw = $card['color_theme'] ?? '';
            [$bgColor, $iconColor] = $this->resolveColors($colorThemeRaw);

            $link = $card['link'] ?? [];
            if (!is_array($link)) {
                $link = [];
            }

            $icon = $card['icon'] ?? '';
            if (!is_string($icon)) {
                $icon = '';
            }

            $prepared[] = [
                'title'               => is_string($card['title'] ?? null) ? $card['title'] : '',
                'description'         => is_string($card['description'] ?? null) ? $card['description'] : '',
                'link'                => $link,
                'hasLink'             => !empty($link['url']),
                'icon'                => $icon,
                'iconBackgroundColor' => $bgColor,
                'iconColor'           => $iconColor,
            ];
        }

        return $prepared;
    }

    /**
     * Coerce the columns select to a grid key.
     *
     * ACF may return the choice as an int, or false when the field is empty.
     *
     * @param mixed $columns
     * @return string
     */
    private function normalizeColumns(mixed $columns): string
    {
        $columns = is_scalar($columns) ? (string) $columns : '';

        return isset($this->gridClasses()[$columns]) ? $columns : '2';
    }

    /**
     * Grid modifier classes keyed by the columns field value.
     *
     * @return array<string, string>
     */
    private function gridClasses(): array
    {
        return [
            '2' => 'o-grid--half',
            '3' => 'o-grid--third',
            '4' => 'o-grid--quarter',
        ];
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
            return [
                $this->colorOrDefault($raw['backgroundColor'] ?? null, '#764a0f'),
                $this->colorOrDefault($raw['iconColor'] ?? null, '#e7d6bf'),
            ];
        }

        // JSON string
        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return [
                    $this->colorOrDefault($decoded['backgroundColor'] ?? null, '#764a0f'),
                    $this->colorOrDefault($decoded['iconColor'] ?? null, '#e7d6bf'),
                ];
            }
        }

        // Fallback
        return ['#764a0f', '#e7d6bf'];
    }

    /**
     * Return a hex colour, falling back when the stored value is empty or not a string.
     */
    private function colorOrDefault(mixed $color, string $default): string
    {
        if (!is_string($color) || $color === '') {
            return $default;
        }

        if ($color[0] !== '#') {
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
        return $this->gridClasses()[$columns] ?? 'o-grid--half';
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
