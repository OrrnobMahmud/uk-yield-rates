/**
 * UK Yield Rates - Shared Shortcode Builder
 * Used by both edit.js and save.js to avoid code duplication
 */

export const buildShortcode = (attributes) => {
    const {
        maturity,
        format,
        showChange,
        decimalPlaces,
        theme,
    } = attributes;

    let shortcode = '[uk_yield_rates';

    if (maturity !== 'all') {
        shortcode += ` maturity="${maturity}"`;
    }

    if (format !== 'inline') {
        shortcode += ` format="${format}"`;
    }

    if (!showChange) {
        shortcode += ` show_change="no"`;
    }

    if (decimalPlaces !== '2') {
        shortcode += ` decimal="${decimalPlaces}"`;
    }

    if (theme !== 'light') {
        shortcode += ` theme="${theme}"`;
    }

    shortcode += ']';
    return shortcode;
};

export default buildShortcode;
