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
        $data = [];

        // Append field config
        $fields = \Modularity\Helper\FormatObject::camelCase($this->getFields());
        $data = array_merge($data, (array) $fields);

        // Process cards data
        $data['cards'] = $this->prepareCards($data['cards'] ?? []);
        $data['columns'] = $data['columns'] ?? '2';
        $data['gridClass'] = $this->getGridClass($data['columns']);

        return $data;
    }

    /**
     * Color theme mappings: background color => icon color
     */
    private const COLOR_THEMES = [
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
            // Handle both camelCase and snake_case field names
            $colorTheme = $card['colorTheme'] 
                ?? $card['color_theme'] 
                ?? '764a0f';
            
            $bgColor = '#' . $colorTheme;
            $iconColor = '#' . (self::COLOR_THEMES[$colorTheme] ?? 'e7d6bf');
            
            return [
                'title' => $card['title'] ?? '',
                'description' => $card['description'] ?? '',
                'link' => $card['link'] ?? [],
                'icon' => $card['icon'] ?? '',
                'iconBackgroundColor' => $bgColor,
                'iconColor' => $iconColor,
            ];
        }, $cards);
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
