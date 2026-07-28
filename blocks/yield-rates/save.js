/**
 * UK Yield Rates - Block Save Function
 */

import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
    const {
        maturity,
        format,
        showChange,
        decimalPlaces,
        theme,
    } = attributes;

    const blockProps = useBlockProps.save();

    // Build shortcode string
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

    return (
        <div {...blockProps}>
            {shortcode}
        </div>
    );
};

export default Save;
