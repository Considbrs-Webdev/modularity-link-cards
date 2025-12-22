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
            $iconBgColor = $card['iconBackgroundColor'] 
                ?? $card['icon_background_color'] 
                ?? '#7B5B3C';
            
            return [
                'title' => $card['title'] ?? '',
                'description' => $card['description'] ?? '',
                'link' => $card['link'] ?? [],
                'icon' => $card['icon'] ?? '',
                'iconBackgroundColor' => $iconBgColor,
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
