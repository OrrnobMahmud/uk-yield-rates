/**
 * UK Yield Rates - Block Save Function
 */

import { useBlockProps } from '@wordpress/block-editor';
import { buildShortcode } from './shortcode-builder';

const Save = ({ attributes }) => {
    const blockProps = useBlockProps.save();
    const shortcode = buildShortcode(attributes);

    return (
        <div {...blockProps}>
            {shortcode}
        </div>
    );
};

export default Save;
