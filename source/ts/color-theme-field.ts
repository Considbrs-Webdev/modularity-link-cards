/**
 * ACF custom field: Color Theme Picker
 *
 * Handles:
 *  - Predefined theme swatch selection
 *  - "Custom" mode: group selector → per-group bg+icon swatches
 *  - "Custom Colors" escape hatch: native colour inputs
 *  - No-groups fallback: free hex pickers rendered directly
 *  - Live preview square update
 *  - Serialises the chosen state back into the hidden input
 */

/* global jQuery, acf */
interface ThemeOption {
  label: string;
  backgroundColor: string;
  iconColor: string;
}

interface FieldValue {
  mode: "theme" | "custom";
  theme?: string;
  colorGroup?: string | null;
  backgroundColor: string;
  iconColor: string;
}

(function ($) {
  // ── Bootstrap every field instance on the page ──────────────────────────

  function initField($wrap: any): void {
    // Guard against double-initialisation (ACF fires "ready append" each time
    // content is re-rendered; without this, duplicate event listeners are
    // attached and later closures re-read the stale original data-value,
    // overwriting the hidden input with an out-of-date state on every event).
    if ($wrap.data("lc-initialized")) return;
    $wrap.data("lc-initialized", true);

    // jQuery auto-parses valid JSON in data-* attributes into objects/arrays,
    // so guard against receiving an already-parsed value before calling JSON.parse.
    function parseData<T>(raw: unknown, fallback: T): T {
      if (raw === null || raw === undefined) return fallback;
      if (typeof raw === "object") return raw as T;
      if (typeof raw === "string" && raw !== "") {
        try {
          return JSON.parse(raw) as T;
        } catch {
          /* ignore */
        }
      }
      return fallback;
    }

    const themesData: Record<string, ThemeOption> = parseData(
      $wrap.data("themes"),
      {} as Record<string, ThemeOption>,
    );

    // Parse current value (set by PHP)
    let current: FieldValue = parseData($wrap.data("value"), null) ?? {
      mode: "theme",
      theme: "brown",
      colorGroup: null,
      backgroundColor: "#764a0f",
      iconColor: "#e7d6bf",
    };

    // Preserve a separate purely-custom colour pair for the panel so
    // switching to themes/groups doesn't clobber the editor's custom picks.
    const currAny = current as any;
    if (currAny._pureBackgroundColor === undefined) {
      currAny._pureBackgroundColor =
        current.colorGroup === "purely-custom"
          ? current.backgroundColor
          : current.backgroundColor;
    }
    if (currAny._pureIconColor === undefined) {
      currAny._pureIconColor =
        current.colorGroup === "purely-custom"
          ? current.iconColor
          : current.iconColor;
    }

    // ── Internal helpers ────────────────────────────────────────────────

    function persist(): void {
      const $input = $wrap.find(".lc-color-theme-field__value");
      $input.val(JSON.stringify(current));
      // Notify ACF (and the Gutenberg block layer, which serialises form
      // values into block attributes on input/change events) that the hidden
      // input's value has changed. Without this, setting .val() directly
      // leaves the block-attribute cache stale and saves will write the
      // pre-edit value for every repeater row.
      $input.trigger("change");
    }

    function applyPreview(): void {
      const $preview = $wrap.find(".lc-color-theme-field__preview");
      const $icon = $wrap.find(".lc-color-theme-field__preview-icon");
      const $customSwatch = $wrap.find(
        ".lc-color-theme-field__theme-swatch--custom .lc-color-theme-field__swatch-preview",
      );
      // Determine icon classes from adjacent ACF `icon` field, falling back
      // to the default star if none set.
      function getSelectedIconClasses(): string {
        // Try row-level then fields container selectors to locate the icon value
        const $row = $wrap.closest(".acf-row, .acf-fields");
        let iconVal: string | undefined;
        if ($row.length) {
          iconVal = $row
            .find('[data-name="icon"] .acf-fontawesome-icon-value')
            .val() as string | undefined;
        }
        if (!iconVal) {
          // Attempt broader lookup in case of different markup
          iconVal = $wrap
            .closest(".acf-field")
            .siblings()
            .find('[data-name="icon"] .acf-fontawesome-icon-value')
            .val() as string | undefined;
        }
        if (!iconVal) return "fa fa-star";
        // Ensure the base `fa` class exists for older FA versions
        const classes = iconVal.split(/\s+/).filter(Boolean);
        if (
          !classes.includes("fa") &&
          !classes.some((c) => c.startsWith("fa-"))
        ) {
          classes.unshift("fa");
        }
        return classes.join(" ");
      }

      $preview.css("background-color", current.backgroundColor);
      $icon.css("color", current.iconColor);

      const iconClasses = getSelectedIconClasses();
      $icon.attr("class", iconClasses + " lc-color-theme-field__preview-icon");

      // Sync icon class on every theme swatch (predefined + custom button)
      // so they always display the currently selected icon.
      $wrap
        .find(
          ".lc-color-theme-field__theme-swatch .lc-color-theme-field__swatch-preview i",
        )
        .attr("class", iconClasses);

      if (current.mode === "custom") {
        $customSwatch
          .css("background-color", current.backgroundColor)
          .find("i")
          .css("color", current.iconColor);
      }
    }

    // Update preview when the fontawesome icon field changes.
    //
    // Primary path: the fontawesome field fires the event on its own element,
    // which bubbles up the DOM. We listen on our .acf-row so we only react to
    // changes within the same repeater row — no fragile row-matching needed.
    const $myRow = $wrap.closest(".acf-row");
    if ($myRow.length) {
      $myRow.on("acf:fontawesome_icon:change", function () {
        applyPreview();
      });
    }

    function updateColorValue(
      pickerKey: "backgroundColor" | "iconColor",
      hex: string,
      isPure: boolean = false,
    ): void {
      const currAny = current as any;
      if (isPure) {
        currAny._pureBackgroundColor =
          pickerKey === "backgroundColor" ? hex : currAny._pureBackgroundColor;
        currAny._pureIconColor =
          pickerKey === "iconColor" ? hex : currAny._pureIconColor;
        current[pickerKey] = hex;
      } else {
        current[pickerKey] = hex;
      }

      applyPreview();
      persist();
    }

    /**
     * Within a group panel, try to highlight the swatch that matches the
     * currently stored colour. If none matches, select the first swatch
     * and update the stored colour value.
     */
    function autoSelectInPanel(
      $panel: any,
      pickerKey: "backgroundColor" | "iconColor",
    ): void {
      const $picker = $panel.find(
        `.lc-color-picker[data-picker-key="${pickerKey}"]`,
      );
      if (!$picker.length) return;

      const currentHex = (current[pickerKey] ?? "").toLowerCase();
      $picker.find(".lc-color-picker__swatch").removeClass("is-selected");

      let matched = false;
      $picker.find(".lc-color-picker__swatch").each(function (
        this: HTMLElement,
      ) {
        const swatchHex = ($(this).data("hex") as string).toLowerCase();
        if (swatchHex === currentHex) {
          $(this).addClass("is-selected");
          matched = true;
          return false; // break
        }
      });

      if (!matched) {
        const $first = $picker.find(".lc-color-picker__swatch").first();
        if ($first.length) {
          $first.addClass("is-selected");
          current[pickerKey] = $first.data("hex") as string;
        }
      }
    }

    /**
     * Activate a colour group (or "purely-custom") inside the custom panel.
     * Hides all other panels, shows the selected one, and auto-selects swatches.
     */
    function selectGroup(
      groupName: string,
      autoSelectColors: boolean = true,
    ): void {
      current.colorGroup = groupName;

      // Update button highlights — use .filter() to handle international chars
      $wrap.find(".lc-color-theme-field__group-btn").removeClass("is-selected");
      $wrap
        .find(".lc-color-theme-field__group-btn")
        .filter(function (this: HTMLElement) {
          return $(this).data("group") === groupName;
        })
        .addClass("is-selected");

      if (groupName === "purely-custom") {
        $wrap
          .find(".lc-color-theme-field__group-panel")
          .removeClass("is-visible");
        $wrap
          .find(".lc-color-theme-field__purely-custom-panel")
          .addClass("is-visible");

        // Ensure the native colour inputs and hex inputs inside the
        // purely-custom panel reflect the stored purely-custom colours
        // (kept separate so theme/group picks don't clobber them).
        const currAny = current as any;
        const bgHex = currAny._pureBackgroundColor || "#cccccc";
        const iconHex = currAny._pureIconColor || "#333333";
        const $purePanel = $wrap.find(
          ".lc-color-theme-field__purely-custom-panel",
        );
        $purePanel
          .find(
            '.lc-color-picker[data-picker-key="backgroundColor"] .lc-color-picker__native-input',
          )
          .val(bgHex as any);
        $purePanel
          .find(
            '.lc-color-picker[data-picker-key="backgroundColor"] .lc-color-picker__hex-input',
          )
          .val(bgHex as any);
        $purePanel
          .find(
            '.lc-color-picker[data-picker-key="iconColor"] .lc-color-picker__native-input',
          )
          .val(iconHex as any);
        $purePanel
          .find(
            '.lc-color-picker[data-picker-key="iconColor"] .lc-color-picker__hex-input',
          )
          .val(iconHex as any);

        // If the purely-custom panel is activated, make the preview and
        // stored `backgroundColor`/`iconColor` reflect the purely-custom
        // pair so saving uses these values.
        current.backgroundColor = bgHex;
        current.iconColor = iconHex;
      } else {
        $wrap
          .find(".lc-color-theme-field__purely-custom-panel")
          .removeClass("is-visible");
        $wrap
          .find(".lc-color-theme-field__group-panel")
          .removeClass("is-visible");

        const $panel = $wrap
          .find(".lc-color-theme-field__group-panel")
          .filter(function (this: HTMLElement) {
            return $(this).data("group") === groupName;
          });

        $panel.addClass("is-visible");
        if (autoSelectColors) {
          autoSelectInPanel($panel, "backgroundColor");
          autoSelectInPanel($panel, "iconColor");
        }
      }

      applyPreview();
      persist();
    }

    function selectTheme(key: string): void {
      if (key === "custom") {
        current.mode = "custom";
        $wrap
          .find(".lc-color-theme-field__custom-panel")
          .addClass("is-visible");

        // If groups exist and none is stored yet, auto-activate the first group
        const $groupBtns = $wrap.find(".lc-color-theme-field__group-btn");
        if ($groupBtns.length && !current.colorGroup) {
          selectGroup($groupBtns.first().data("group") as string);
          // selectGroup already calls applyPreview + persist; update swatch and return
          $wrap
            .find(".lc-color-theme-field__theme-swatch")
            .removeClass("is-selected");
          $wrap
            .find(
              '.lc-color-theme-field__theme-swatch[data-theme-key="custom"]',
            )
            .addClass("is-selected");
          return;
        }
      } else if (themesData[key]) {
        current.mode = "theme";
        current.theme = key;
        current.backgroundColor = themesData[key].backgroundColor;
        current.iconColor = themesData[key].iconColor;
        $wrap
          .find(".lc-color-theme-field__custom-panel")
          .removeClass("is-visible");
      }

      $wrap
        .find(".lc-color-theme-field__theme-swatch")
        .removeClass("is-selected");
      $wrap
        .find(".lc-color-theme-field__theme-swatch")
        .filter(function (this: HTMLElement) {
          return $(this).data("theme-key") === key;
        })
        .addClass("is-selected");

      applyPreview();
      persist();
    }

    // ── Theme swatch buttons ────────────────────────────────────────────

    $wrap.on(
      "click",
      ".lc-color-theme-field__theme-swatch",
      function (this: HTMLElement) {
        selectTheme($(this).data("theme-key") as string);
      },
    );

    // ── Group selector buttons ──────────────────────────────────────────

    $wrap.on(
      "click",
      ".lc-color-theme-field__group-btn",
      function (this: HTMLElement) {
        selectGroup($(this).data("group") as string);
      },
    );

    // ── Colour pickers ──────────────────────────────────────────────────

    // Swatch click inside a colour picker
    $wrap.on("click", ".lc-color-picker__swatch", function (this: HTMLElement) {
      const $swatch = $(this);
      const hex = $swatch.data("hex") as string;
      const $picker = $swatch.closest(".lc-color-picker");
      const pickerKey = $picker.data("picker-key") as
        | "backgroundColor"
        | "iconColor";

      $picker.find(".lc-color-picker__swatch").removeClass("is-selected");
      $swatch.addClass("is-selected");

      $picker.find(".lc-color-picker__hex-input").val(hex);

      const isPure =
        $swatch.closest(".lc-color-theme-field__purely-custom-panel").length >
        0;
      updateColorValue(pickerKey, hex, isPure);
    });

    // Native <input type="color"> change (purely-custom panel)
    $wrap.on(
      "input",
      ".lc-color-picker__native-input",
      function (this: HTMLInputElement) {
        const $input = $(this);
        const hex = $input.val() as string;
        const $picker = $input.closest(".lc-color-picker");
        const pickerKey = $picker.data("picker-key") as
          | "backgroundColor"
          | "iconColor";

        $picker.find(".lc-color-picker__hex-input").val(hex);

        const isPure =
          $input.closest(".lc-color-theme-field__purely-custom-panel").length >
          0;
        updateColorValue(pickerKey, hex, isPure);
      },
    );

    // Hex text input in a colour picker
    $wrap.on(
      "input",
      ".lc-color-picker__hex-input",
      function (this: HTMLInputElement) {
        const $input = $(this);
        const raw = $input.val() as string;
        const hex = raw.startsWith("#") ? raw : "#" + raw;
        const $picker = $input.closest(".lc-color-picker");
        const pickerKey = $picker.data("picker-key") as
          | "backgroundColor"
          | "iconColor";

        // Only commit valid 6-digit hex values
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
          $picker.find(".lc-color-picker__native-input").val(hex);
          $picker.find(".lc-color-picker__swatch").removeClass("is-selected");
          $picker
            .find(
              `.lc-color-picker__swatch[data-hex="${hex.toUpperCase()}"], .lc-color-picker__swatch[data-hex="${hex.toLowerCase()}"]`,
            )
            .addClass("is-selected");
          const isPure =
            $picker.closest(".lc-color-theme-field__purely-custom-panel")
              .length > 0;
          updateColorValue(pickerKey, hex, isPure);
        }
      },
    );

    // ── Initialise ──────────────────────────────────────────────────────

    // In custom mode with groups present but nothing stored yet, auto-pick first group
    if (current.mode === "custom") {
      const $firstGroupBtn = $wrap
        .find(".lc-color-theme-field__group-btn")
        .first();
      if ($firstGroupBtn.length && !current.colorGroup) {
        // Pass false so stored bg/icon colors are NOT overwritten by
        // autoSelectInPanel when there is no matching swatch. The user's
        // previously saved colors (or PHP defaults) must survive page reload.
        selectGroup($firstGroupBtn.data("group") as string, false);
        return; // selectGroup handles persist + preview
      }
    }

    applyPreview();
  }

  // ── ACF lifecycle: run on every field render ─────────────────────────────

  if (typeof acf !== "undefined") {
    acf.add_action("ready append", function ($el: any) {
      $el.find(".lc-color-theme-field").each(function (this: HTMLElement) {
        initField($(this));
      });
    });
  } else {
    // Fallback: DOM ready
    $(function () {
      $(".lc-color-theme-field").each(function (this: HTMLElement) {
        initField($(this));
      });
    });
  }
})(jQuery);
