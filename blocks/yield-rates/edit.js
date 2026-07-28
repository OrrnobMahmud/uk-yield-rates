/**
 * UK Yield Rates - Block Editor Component
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, ToggleControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
    const {
        maturity,
        format,
        showChange,
        decimalPlaces,
        theme,
    } = attributes;

    const blockProps = useBlockProps();
    const [preview, setPreview] = useState(null);
    const [loading, setLoading] = useState(true);

    // Fetch preview data
    useEffect(() => {
        setLoading(true);

        // Build shortcode for preview
        const shortcode = buildShortcode(attributes);

        // Make AJAX request to render preview
        fetch(ukYieldBlockData.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=uk_yield_render_preview&shortcode=${encodeURIComponent(shortcode)}&nonce=${ukYieldBlockData.nonce}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setPreview(data.data.html);
            }
            setLoading(false);
        })
        .catch(() => {
            setLoading(false);
        });
    }, [maturity, format, showChange, decimalPlaces, theme]);

    // Build shortcode string from attributes
    const buildShortcode = (attrs) => {
        let shortcode = '[uk_yield_rates';

        if (attrs.maturity !== 'all') {
            shortcode += ` maturity="${attrs.maturity}"`;
        }

        if (attrs.format !== 'inline') {
            shortcode += ` format="${attrs.format}"`;
        }

        if (!attrs.showChange) {
            shortcode += ` show_change="no"`;
        }

        if (attrs.decimalPlaces !== '2') {
            shortcode += ` decimal="${attrs.decimalPlaces}"`;
        }

        if (attrs.theme !== 'light') {
            shortcode += ` theme="${attrs.theme}"`;
        }

        shortcode += ']';
        return shortcode;
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'uk-yield-rates')}>
                    <PanelRow>
                        <SelectControl
                            label={__('Maturity', 'uk-yield-rates')}
                            value={maturity}
                            options={[
                                { label: __('All Maturities', 'uk-yield-rates'), value: 'all' },
                                { label: __('2-Year', 'uk-yield-rates'), value: '2' },
                                { label: __('5-Year', 'uk-yield-rates'), value: '5' },
                                { label: __('10-Year', 'uk-yield-rates'), value: '10' },
                                { label: __('20-Year', 'uk-yield-rates'), value: '20' },
                                { label: __('30-Year', 'uk-yield-rates'), value: '30' },
                            ]}
                            onChange={(value) => setAttributes({ maturity: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label={__('Format', 'uk-yield-rates')}
                            value={format}
                            options={[
                                { label: __('Inline', 'uk-yield-rates'), value: 'inline' },
                                { label: __('Sidebar', 'uk-yield-rates'), value: 'sidebar' },
                                { label: __('Table', 'uk-yield-rates'), value: 'table' },
                                { label: __('Compact', 'uk-yield-rates'), value: 'compact' },
                            ]}
                            onChange={(value) => setAttributes({ format: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Show Change Indicator', 'uk-yield-rates')}
                            checked={showChange}
                            onChange={(value) => setAttributes({ showChange: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label={__('Decimal Places', 'uk-yield-rates')}
                            value={decimalPlaces}
                            options={[
                                { label: '2', value: '2' },
                                { label: '3', value: '3' },
                                { label: '4', value: '4' },
                            ]}
                            onChange={(value) => setAttributes({ decimalPlaces: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label={__('Theme', 'uk-yield-rates')}
                            value={theme}
                            options={[
                                { label: __('Light', 'uk-yield-rates'), value: 'light' },
                                { label: __('Dark', 'uk-yield-rates'), value: 'dark' },
                            ]}
                            onChange={(value) => setAttributes({ theme: value })}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>

            <div className="uk-yield-block-preview">
                {loading ? (
                    <div className="uk-yield-block-loading">
                        <Spinner />
                        <p>{__('Loading yield rates...', 'uk-yield-rates')}</p>
                    </div>
                ) : preview ? (
                    <div
                        className="uk-yield-block-content"
                        dangerouslySetInnerHTML={{ __html: preview }}
                    />
                ) : (
                    <div className="uk-yield-block-placeholder">
                        <p>{__('UK Yield Rates Preview', 'uk-yield-rates')}</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Edit;
