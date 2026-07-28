/**
 * UK Yield Rates - Block Editor Component
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, ToggleControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { buildShortcode } from './shortcode-builder';
import './editor.css';

// Sanitize HTML to prevent XSS attacks
const sanitizeHtml = (html) => {
    if (!html) return '';

    // Create a temporary element to parse HTML
    const doc = new DOMParser().parseFromString(html, 'text/html');

    // Remove all script tags and event handlers
    const scripts = doc.querySelectorAll('script, iframe, object, embed');
    scripts.forEach(el => el.remove());

    // Remove event handlers from all elements
    const allElements = doc.querySelectorAll('*');
    allElements.forEach(el => {
        const attrs = el.attributes;
        for (let i = attrs.length - 1; i >= 0; i--) {
            const attrName = attrs[i].name.toLowerCase();
            if (attrName.startsWith('on') || attrName === 'javascript:') {
                el.removeAttribute(attrs[i].name);
            }
        }
    });

    return doc.body.innerHTML;
};

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
                // Sanitize HTML to prevent XSS
                setPreview(sanitizeHtml(data.data.html));
            }
            setLoading(false);
        })
        .catch(() => {
            setLoading(false);
        });
    }, [maturity, format, showChange, decimalPlaces, theme]);

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
