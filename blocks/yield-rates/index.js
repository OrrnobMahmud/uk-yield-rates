/**
 * UK Yield Rates - Gutenberg Block
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

const { name } = metadata;

registerBlockType(name, {
    ...metadata,
    edit: Edit,
    save: Save,
});
