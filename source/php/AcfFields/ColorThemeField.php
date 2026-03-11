<?php

declare(strict_types=1);

namespace ModularityLinkCards\AcfFields;

use ModularityLinkCards\Helper\CacheBust;

/**
 * Custom ACF field: Color Theme Picker
 *
 * Renders a UI that lets the editor either choose a predefined theme
 * (background + icon colour baked in) or pick both colours individually.
 *
 * Stored value (JSON-encoded string):
 * {
 *   "mode":           "theme" | "custom",
 *   "theme":          "<theme-key>",         // only when mode=theme
 *   "colorGroup":     "<group-name>" | "purely-custom" | null, // only when mode=custom + groups exist
 *   "backgroundColor": "#rrggbb",
 *   "iconColor":       "#rrggbb"
 * }
 *
 * Filters
 * -------
 * Modularity/Module/LinkCards/ColorThemes
 *   Receives and must return array<string, array{label:string, backgroundColor:string, iconColor:string}>
 *
 * Modularity/Module/LinkCards/BackgroundColors
 *   Receives and must return array — flat OR grouped:
 *     - Flat:    array<string, string>          (label => hex)
 *     - Grouped: array<string, array<string, string>> (groupLabel => [label => hex])
 *
 * Modularity/Module/LinkCards/IconColors
 *   Same shape as BackgroundColors above.
 */
class ColorThemeField extends \acf_field
{
    public $name     = 'link_cards_color_theme';
    public $label    = 'Color Theme';
    public $category = 'jquery';
    public $defaults = [
        'default_value' => '',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // -------------------------------------------------------------------------
    // Data definitions
    // -------------------------------------------------------------------------

    /**
     * Default predefined themes.
     *
     * @return array<string, array{label:string, backgroundColor:string, iconColor:string}>
     */
    protected function getThemes(): array
    {
        $themes = [
            'brown' => [
                'label'           => __('Brown', 'modularity-link-cards'),
                'backgroundColor' => '#764a0f',
                'iconColor'       => '#e7d6bf',
            ],
            'dark-green' => [
                'label'           => __('Dark Green', 'modularity-link-cards'),
                'backgroundColor' => '#233b1f',
                'iconColor'       => '#a0b990',
            ],
            'light-green' => [
                'label'           => __('Light Green', 'modularity-link-cards'),
                'backgroundColor' => '#a0b990',
                'iconColor'       => '#2b512b',
            ],
            'gold' => [
                'label'           => __('Gold', 'modularity-link-cards'),
                'backgroundColor' => '#ba8a48',
                'iconColor'       => '#5f3a0b',
            ],
        ];

        return (array) apply_filters('Modularity/Module/LinkCards/ColorThemes', $themes);
    }

    /**
     * Background colour options (flat or grouped).
     *
     * @return array
     */
    protected function getBackgroundColors(): array
    {
        $colors = [];
        return (array) apply_filters('Modularity/Module/LinkCards/BackgroundColors', $colors);
    }

    /**
     * Icon colour options (flat or grouped).
     *
     * @return array
     */
    protected function getIconColors(): array
    {
        $colors = [];
        return (array) apply_filters('Modularity/Module/LinkCards/IconColors', $colors);
    }

    // -------------------------------------------------------------------------
    // ACF hooks
    // -------------------------------------------------------------------------

    public function input_admin_enqueue_scripts(): void
    {
        $pluginUrl = MODULARITYLINKCARDS_URL . '/assets/dist/';

        $jsFile  = CacheBust::name('js/color-theme-field.js');
        $cssFile = CacheBust::name('css/color-theme-field.css');

        if ($jsFile) {
            wp_enqueue_script(
                'link-cards-color-theme-field',
                $pluginUrl . $jsFile,
                ['acf-input', 'jquery'],
                null,
                true
            );

            wp_localize_script('link-cards-color-theme-field', 'linkCardsColorTheme', [
                'themes'           => $this->getThemes(),
                'backgroundColors' => $this->getBackgroundColors(),
                'iconColors'       => $this->getIconColors(),
                'i18n'             => [
                    'selectTheme'       => __('Select Theme', 'modularity-link-cards'),
                    'custom'            => __('Custom', 'modularity-link-cards'),
                    'backgroundColor'   => __('Background Color', 'modularity-link-cards'),
                    'iconColor'         => __('Icon Color', 'modularity-link-cards'),
                    'preview'           => __('Preview', 'modularity-link-cards'),
                    'noColors'          => __('No colors available', 'modularity-link-cards'),
                ],
            ]);
        }

        if ($cssFile) {
            wp_enqueue_style(
                'link-cards-color-theme-field',
                $pluginUrl . $cssFile,
                ['acf-input'],
                null
            );
        }
    }

    public function render_field($field): void
    {
        $rawValue   = $field['value'] ?? '';
        $parsed     = $this->parseValue($rawValue, $field);
        $themes     = $this->getThemes();
        $bgColors   = $this->getBackgroundColors();
        $iconColors = $this->getIconColors();

        // Compute available colour groups (union of bg + icon group keys)
        $isBgGrouped   = $this->isGroupedColors($bgColors);
        $isIconGrouped = $this->isGroupedColors($iconColors);
        $allGroups = array_values(array_unique(array_merge(
            $isBgGrouped   ? array_keys($bgColors)   : [],
            $isIconGrouped ? array_keys($iconColors) : []
        )));
        $hasGroups = !empty($allGroups);

        // JSON data attributes for JS bootstrap
        $dataThemes = esc_attr(wp_json_encode($themes));
        $dataBg     = esc_attr(wp_json_encode($bgColors));
        $dataIcon   = esc_attr(wp_json_encode($iconColors));
        $dataValue  = esc_attr(wp_json_encode($parsed));
?>
<div class="lc-color-theme-field"
     data-themes="<?php echo $dataThemes; ?>"
     data-background-colors="<?php echo $dataBg; ?>"
     data-icon-colors="<?php echo $dataIcon; ?>"
     data-value="<?php echo $dataValue; ?>">

    <input type="hidden"
           name="<?php echo esc_attr($field['name']); ?>"
           class="lc-color-theme-field__value"
           value="<?php echo esc_attr(wp_json_encode($parsed)); ?>" />

    <!-- ── Theme selector row ─────────────────────────────────── -->
    <div class="lc-color-theme-field__row lc-color-theme-field__row--themes">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Color Theme', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-theme-field__theme-options">
            <?php foreach ($themes as $key => $theme): ?>
            <button type="button"
                    class="lc-color-theme-field__theme-swatch <?php echo ($parsed['mode'] === 'theme' && $parsed['theme'] === $key) ? 'is-selected' : ''; ?>"
                    data-theme-key="<?php echo esc_attr($key); ?>"
                    data-bg="<?php echo esc_attr($theme['backgroundColor']); ?>"
                    data-icon="<?php echo esc_attr($theme['iconColor']); ?>"
                    title="<?php echo esc_attr($theme['label']); ?>">
                <span class="lc-color-theme-field__swatch-preview"
                      style="background-color:<?php echo esc_attr($theme['backgroundColor']); ?>;">
                    <i class="fa fa-star" style="color:<?php echo esc_attr($theme['iconColor']); ?>;"></i>
                </span>
                <span class="lc-color-theme-field__swatch-label"><?php echo esc_html($theme['label']); ?></span>
            </button>
            <?php endforeach; ?>

            <!-- Custom option -->
            <button type="button"
                    class="lc-color-theme-field__theme-swatch lc-color-theme-field__theme-swatch--custom <?php echo ($parsed['mode'] === 'custom') ? 'is-selected' : ''; ?>"
                    data-theme-key="custom"
                    title="<?php esc_attr_e('Custom', 'modularity-link-cards'); ?>">
                <span class="lc-color-theme-field__swatch-preview lc-color-theme-field__swatch-preview--custom"
                      style="background-color:<?php echo esc_attr($parsed['mode'] === 'custom' ? $parsed['backgroundColor'] : '#cccccc'); ?>;">
                    <i class="fa fa-star" style="color:<?php echo esc_attr($parsed['mode'] === 'custom' ? $parsed['iconColor'] : '#666666'); ?>;"></i>
                </span>
                <span class="lc-color-theme-field__swatch-label">
                    <?php esc_html_e('Custom', 'modularity-link-cards'); ?>
                </span>
            </button>
        </div>
    </div>

    <!-- ── Custom colour pickers (shown only in custom mode) ──── -->
    <div class="lc-color-theme-field__custom-panel <?php echo ($parsed['mode'] === 'custom') ? 'is-visible' : ''; ?>">

        <?php if ($hasGroups): ?>

        <!-- Group selector -->
        <div class="lc-color-theme-field__row lc-color-theme-field__row--group-selector">
            <label class="lc-color-theme-field__label">
                <?php esc_html_e('Color Group', 'modularity-link-cards'); ?>
            </label>
            <div class="lc-color-theme-field__group-options">
                <?php foreach ($allGroups as $groupName): ?>
                <button type="button"
                        class="lc-color-theme-field__group-btn <?php echo ($parsed['colorGroup'] === $groupName) ? 'is-selected' : ''; ?>"
                        data-group="<?php echo esc_attr($groupName); ?>">
                    <?php echo esc_html($groupName); ?>
                </button>
                <?php endforeach; ?>
                <button type="button"
                        class="lc-color-theme-field__group-btn lc-color-theme-field__group-btn--purely-custom <?php echo ($parsed['colorGroup'] === 'purely-custom') ? 'is-selected' : ''; ?>"
                        data-group="purely-custom">
                    <?php esc_html_e('Custom Colors', 'modularity-link-cards'); ?>
                </button>
            </div>
        </div>

        <!-- Per-group swatch panels (all rendered; JS toggles is-visible) -->
        <?php foreach ($allGroups as $groupName): ?>
        <?php $this->renderGroupPanel(
            $groupName,
            $parsed['backgroundColor'],
            $parsed['iconColor'],
            $bgColors,
            $iconColors,
            $parsed['colorGroup'] ?? ''
        ); ?>
        <?php endforeach; ?>

        <!-- Free-input panel (shown when "Custom Colors" group is selected) -->
        <?php $this->renderPurelyCustomPanel(
            $parsed['backgroundColor'],
            $parsed['iconColor'],
            $parsed['colorGroup'] ?? ''
        ); ?>

        <?php else: ?>

        <!-- No groups available: show free-form colour pickers directly -->
        <div class="lc-color-theme-field__row">
            <label class="lc-color-theme-field__label">
                <?php esc_html_e('Background Color', 'modularity-link-cards'); ?>
            </label>
            <?php $this->renderColorPicker('backgroundColor', $parsed['backgroundColor'], $bgColors); ?>
        </div>
        <div class="lc-color-theme-field__row">
            <label class="lc-color-theme-field__label">
                <?php esc_html_e('Icon Color', 'modularity-link-cards'); ?>
            </label>
            <?php $this->renderColorPicker('iconColor', $parsed['iconColor'], $iconColors); ?>
        </div>

        <?php endif; ?>

    </div>

    <!-- ── Live preview ────────────────────────────────────────── -->
    <div class="lc-color-theme-field__row lc-color-theme-field__row--preview">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Preview', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-theme-field__preview"
             style="background-color:<?php echo esc_attr($parsed['backgroundColor']); ?>;">
            <i class="fa fa-star lc-color-theme-field__preview-icon"
               style="color:<?php echo esc_attr($parsed['iconColor']); ?>;"></i>
        </div>
    </div>

</div>
<?php
    }

    // -------------------------------------------------------------------------
    // Colour picker sub-widget
    // -------------------------------------------------------------------------

    /**
     * Render a compact colour picker: swatches from the filtered list
     * (grouped or flat) plus a free-text hex fallback.
     *
     * @param string $fieldKey     JS data-key for this picker
     * @param string $currentHex  Currently selected hex value
     * @param array  $colorList   Flat or grouped colour array
     */
    protected function renderColorPicker(string $fieldKey, string $currentHex, array $colorList): void
    {
        $isGrouped = $this->isGroupedColors($colorList);
?>
<div class="lc-color-picker" data-picker-key="<?php echo esc_attr($fieldKey); ?>">

    <?php if (!empty($colorList)): ?>
    <div class="lc-color-picker__swatches">
        <?php if ($isGrouped): ?>
            <?php foreach ($colorList as $groupName => $groupColors): ?>
            <div class="lc-color-picker__group">
                <span class="lc-color-picker__group-label"><?php echo esc_html($groupName); ?></span>
                <div class="lc-color-picker__group-swatches">
                    <?php foreach ($groupColors as $colorLabel => $hex): ?>
                    <button type="button"
                            class="lc-color-picker__swatch <?php echo (strtolower($currentHex) === strtolower($hex)) ? 'is-selected' : ''; ?>"
                            data-hex="<?php echo esc_attr($hex); ?>"
                            title="<?php echo esc_attr($colorLabel . ' (' . $hex . ')'); ?>"
                            style="background-color:<?php echo esc_attr($hex); ?>;"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($colorList as $colorLabel => $hex): ?>
            <button type="button"
                    class="lc-color-picker__swatch <?php echo (strtolower($currentHex) === strtolower($hex)) ? 'is-selected' : ''; ?>"
                    data-hex="<?php echo esc_attr($hex); ?>"
                    title="<?php echo esc_attr($colorLabel . ' (' . $hex . ')'); ?>"
                    style="background-color:<?php echo esc_attr($hex); ?>;"></button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="lc-color-picker__hex-wrap">
        <input type="text"
               class="lc-color-picker__hex-input"
               value="<?php echo esc_attr($currentHex); ?>"
               placeholder="#rrggbb"
               maxlength="7"
               pattern="^#[0-9A-Fa-f]{6}$" />
    </div>
</div>
<?php
    }

    // -------------------------------------------------------------------------
    // Value handling
    // -------------------------------------------------------------------------

    /**
     * Parse a stored field value into a guaranteed-shape array.
     */
    protected function parseValue(mixed $raw, array $field): array
    {
        $defaults = [
            'mode'            => 'theme',
            'theme'           => 'brown',
            'colorGroup'      => null,
            'backgroundColor' => '#764a0f',
            'iconColor'       => '#e7d6bf',
        ];

        if (empty($raw)) {
            // Honour the ACF default_value if set
            $raw = $field['default_value'] ?? '';
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_merge($defaults, $decoded);
            }
        }

        return $defaults;
    }

    /**
     * Sanitise before saving.
     */
    public function update_value($value, $post_id, $field): string
    {
        if (is_array($value)) {
            $value = wp_json_encode($value);
        }

        if (!is_string($value)) {
            return '';
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return '';
        }

        $mode = in_array($decoded['mode'] ?? '', ['theme', 'custom'], true) ? $decoded['mode'] : 'theme';
        $sanitised = ['mode' => $mode];

        if ($mode === 'theme') {
            $sanitised['theme']           = sanitize_key($decoded['theme'] ?? '');
            $sanitised['backgroundColor'] = sanitize_hex_color($decoded['backgroundColor'] ?? '') ?? '';
            $sanitised['iconColor']       = sanitize_hex_color($decoded['iconColor'] ?? '') ?? '';
        } else {
            $rawGroup = $decoded['colorGroup'] ?? null;
            $sanitised['colorGroup'] = ($rawGroup === 'purely-custom' || $rawGroup === null)
                ? $rawGroup
                : sanitize_text_field($rawGroup);
            $sanitised['backgroundColor'] = sanitize_hex_color($decoded['backgroundColor'] ?? '') ?? '#cccccc';
            $sanitised['iconColor']       = sanitize_hex_color($decoded['iconColor'] ?? '') ?? '#333333';
        }

        return wp_json_encode($sanitised);
    }

    /**
     * Return value to templates — decoded array or raw hex string for
     * backward-compatibility.
     */
    public function format_value($value, $post_id, $field): array
    {
        return $this->parseValue($value, $field);
    }

    // -------------------------------------------------------------------------
    // Colour picker sub-widgets for group-enforced mode
    // -------------------------------------------------------------------------

    /**
     * Render swatch panels for a single colour group.
     * Both bg and icon swatches are shown inside this panel; JS hides/shows
     * the panel based on the selected group button.
     *
     * @param string $groupName       Group key / label
     * @param string $currentBg      Currently stored background hex
     * @param string $currentIcon    Currently stored icon hex
     * @param array  $bgColors       Full grouped bg colour array
     * @param array  $iconColors     Full grouped icon colour array
     * @param string $selectedGroup  Currently selected group in stored value
     */
    protected function renderGroupPanel(
        string $groupName,
        string $currentBg,
        string $currentIcon,
        array $bgColors,
        array $iconColors,
        string $selectedGroup
    ): void {
        $isVisible = ($selectedGroup === $groupName);
        $groupBg   = $bgColors[$groupName]   ?? [];
        $groupIcon = $iconColors[$groupName] ?? [];
?>
<div class="lc-color-theme-field__group-panel <?php echo $isVisible ? 'is-visible' : ''; ?>"
     data-group="<?php echo esc_attr($groupName); ?>">

    <?php if (!empty($groupBg)): ?>
    <div class="lc-color-theme-field__row">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Background Color', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-picker" data-picker-key="backgroundColor">
            <div class="lc-color-picker__swatches">
                <?php foreach ($groupBg as $colorLabel => $hex): ?>
                <button type="button"
                        class="lc-color-picker__swatch <?php echo (strtolower($currentBg) === strtolower($hex)) ? 'is-selected' : ''; ?>"
                        data-hex="<?php echo esc_attr($hex); ?>"
                        title="<?php echo esc_attr($colorLabel . ' (' . $hex . ')'); ?>"
                        style="background-color:<?php echo esc_attr($hex); ?>;"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($groupIcon)): ?>
    <div class="lc-color-theme-field__row">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Icon Color', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-picker" data-picker-key="iconColor">
            <div class="lc-color-picker__swatches">
                <?php foreach ($groupIcon as $colorLabel => $hex): ?>
                <button type="button"
                        class="lc-color-picker__swatch <?php echo (strtolower($currentIcon) === strtolower($hex)) ? 'is-selected' : ''; ?>"
                        data-hex="<?php echo esc_attr($hex); ?>"
                        title="<?php echo esc_attr($colorLabel . ' (' . $hex . ')'); ?>"
                        style="background-color:<?php echo esc_attr($hex); ?>;"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php
    }

    /**
     * Render free-input colour pickers (native <input type="color"> + hex text)
     * used when the editor selects "Custom Colors".
     *
     * @param string $currentBg     Currently stored background hex
     * @param string $currentIcon   Currently stored icon hex
     * @param string $selectedGroup Currently selected group in stored value
     */
    protected function renderPurelyCustomPanel(
        string $currentBg,
        string $currentIcon,
        string $selectedGroup
    ): void {
        $isVisible = ($selectedGroup === 'purely-custom');
?>
<div class="lc-color-theme-field__purely-custom-panel <?php echo $isVisible ? 'is-visible' : ''; ?>">

    <!-- Background -->
    <div class="lc-color-theme-field__row">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Background Color', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-picker" data-picker-key="backgroundColor">
            <div class="lc-color-picker__hex-wrap">
                <input type="color"
                       class="lc-color-picker__native-input"
                       value="<?php echo esc_attr($currentBg ?: '#cccccc'); ?>" />
                <input type="text"
                       class="lc-color-picker__hex-input"
                       value="<?php echo esc_attr($currentBg); ?>"
                       placeholder="#rrggbb"
                       maxlength="7"
                       pattern="^#[0-9A-Fa-f]{6}$" />
            </div>
        </div>
    </div>

    <!-- Icon -->
    <div class="lc-color-theme-field__row">
        <label class="lc-color-theme-field__label">
            <?php esc_html_e('Icon Color', 'modularity-link-cards'); ?>
        </label>
        <div class="lc-color-picker" data-picker-key="iconColor">
            <div class="lc-color-picker__hex-wrap">
                <input type="color"
                       class="lc-color-picker__native-input"
                       value="<?php echo esc_attr($currentIcon ?: '#333333'); ?>" />
                <input type="text"
                       class="lc-color-picker__hex-input"
                       value="<?php echo esc_attr($currentIcon); ?>"
                       placeholder="#rrggbb"
                       maxlength="7"
                       pattern="^#[0-9A-Fa-f]{6}$" />
            </div>
        </div>
    </div>

</div>
<?php
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Detect whether $colorList is grouped (array of arrays) or flat.
     */
    protected function isGroupedColors(array $colorList): bool
    {
        foreach ($colorList as $v) {
            return is_array($v);
        }
        return false;
    }
}
