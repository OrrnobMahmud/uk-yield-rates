/**
 * UK Yield Rates - Block Editor Component
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, ToggleControl, Spinner, Button, TextControl } from '@wordpress/components';
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

// Sample yield data for preview when live data unavailable
const sampleYields = {
    '2': { yield: 4.25, change: 0.02 },
    '5': { yield: 4.15, change: -0.01 },
    '10': { yield: 4.05, change: 0.00 },
    '20': { yield: 4.35, change: 0.03 },
    '30': { yield: 4.45, change: -0.02 }
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
    const [previewMode, setPreviewMode] = useState('live'); // 'live', 'sample', 'shortcode'
    const [customShortcode, setCustomShortcode] = useState('');

    // Generate sample preview HTML
    const generateSamplePreview = () => {
        const decimal = parseInt(decimalPlaces) || 2;
        let html = '';

        if (format === 'inline' || format === 'compact') {
            html = '<span class="uk-yield-inline" style="display: inline; white-space: nowrap;">';

            const maturitiesToShow = maturity === 'all'
                ? Object.keys(sampleYields)
                : maturity.split(',');

            maturitiesToShow.forEach((mat, index) => {
                if (sampleYields[mat]) {
                    if (index > 0) html += ', ';
                    const yieldVal = sampleYields[mat].yield.toFixed(decimal);
                    const change = sampleYields[mat].change;
                    const changeSymbol = change > 0 ? '↑' : (change < 0 ? '↓' : '→');
                    const changeClass = change > 0 ? 'positive' : (change < 0 ? 'negative' : 'neutral');

                    html += `<span class="uk-yield-item">`;
                    html += `<span class="uk-yield-label">${mat}-Year: </span>`;
                    html += `<span class="uk-yield-value">${yieldVal}%</span>`;
                    if (showChange) {
                        html += ` <span class="uk-yield-change ${changeClass}">${changeSymbol}${Math.abs(change).toFixed(decimal)}</span>`;
                    }
                    html += `</span>`;
                }
            });

            html += '</span>';
        } else if (format === 'table') {
            html = '<div class="uk-yield-table-wrapper"><table class="uk-yield-table"><thead><tr>';
            html += '<th>Maturity</th><th>Yield</th>';
            if (showChange) html += '<th>Change</th><th>Status</th>';
            html += '</tr></thead><tbody>';

            Object.keys(sampleYields).forEach(mat => {
                const yieldVal = sampleYields[mat].yield.toFixed(decimal);
                const change = sampleYields[mat].change;
                const changeClass = change > 0 ? 'positive' : (change < 0 ? 'negative' : 'neutral');
                const status = change > 0 ? 'Rising' : (change < 0 ? 'Falling' : 'Stable');

                html += `<tr>`;
                html += `<td>${mat}-Year</td>`;
                html += `<td class="uk-yield-td-yield">${yieldVal}%</td>`;
                if (showChange) {
                    html += `<td class="uk-yield-td-change ${changeClass}">${change > 0 ? '+' : ''}${change.toFixed(decimal)}</td>`;
                    html += `<td class="uk-yield-td-status ${changeClass}">${status}</td>`;
                }
                html += `</tr>`;
            });

            html += '</tbody></table></div>';
        } else if (format === 'sidebar') {
            html = '<div class="uk-yield-sidebar">';
            html += '<div class="uk-yield-sidebar-header"><h3 class="uk-yield-sidebar-title">UK Gilt Yields</h3></div>';
            html += '<div class="uk-yield-sidebar-content">';

            Object.keys(sampleYields).forEach(mat => {
                const yieldVal = sampleYields[mat].yield.toFixed(decimal);
                const change = sampleYields[mat].change;
                const changeClass = change > 0 ? 'positive' : (change < 0 ? 'negative' : 'neutral');
                const changeSymbol = change > 0 ? '↑' : (change < 0 ? '↓' : '→');

                html += `<div class="uk-yield-sidebar-row">`;
                html += `<span class="uk-yield-sidebar-maturity">${mat}-Year</span>`;
                html += `<span class="uk-yield-sidebar-yield">${yieldVal}%</span>`;
                if (showChange) {
                    html += `<span class="uk-yield-sidebar-change ${changeClass}">${changeSymbol}${Math.abs(change).toFixed(decimal)}</span>`;
                }
                html += `</div>`;
            });

            html += '</div>';
            html += '<div class="uk-yield-sidebar-footer">Updated: Sample Data</div>';
            html += '</div>';
        }

        return html;
    };

    // Fetch preview data
    useEffect(() => {
        setLoading(true);

        if (previewMode === 'sample') {
            // Use sample data
            setPreview(sanitizeHtml(generateSamplePreview()));
            setLoading(false);
            return;
        }

        if (previewMode === 'shortcode' && customShortcode) {
            // Use custom shortcode
            fetch(ukYieldBlockData.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=uk_yield_render_preview&shortcode=${encodeURIComponent(customShortcode)}&nonce=${ukYieldBlockData.nonce}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setPreview(sanitizeHtml(data.data.html));
                }
                setLoading(false);
            })
            .catch(() => {
                setLoading(false);
            });
            return;
        }

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
    }, [maturity, format, showChange, decimalPlaces, theme, previewMode, customShortcode]);

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

                <PanelBody title={__('Preview Options', 'uk-yield-rates')} initialOpen={false}>
                    <PanelRow>
                        <SelectControl
                            label={__('Preview Mode', 'uk-yield-rates')}
                            value={previewMode}
                            options={[
                                { label: __('Live Data', 'uk-yield-rates'), value: 'live' },
                                { label: __('Sample Data', 'uk-yield-rates'), value: 'sample' },
                                { label: __('Custom Shortcode', 'uk-yield-rates'), value: 'shortcode' },
                            ]}
                            onChange={(value) => setPreviewMode(value)}
                            help={__('Choose how to preview the yield display', 'uk-yield-rates')}
                        />
                    </PanelRow>

                    {previewMode === 'shortcode' && (
                        <PanelRow>
                            <TextControl
                                label={__('Custom Shortcode', 'uk-yield-rates')}
                                value={customShortcode}
                                onChange={(value) => setCustomShortcode(value)}
                                placeholder='[uk_yield_rates format="table"]'
                                help={__('Enter a shortcode to preview (e.g., [uk_yield_rates maturity="10"])', 'uk-yield-rates')}
                            />
                        </PanelRow>
                    )}

                    <PanelRow>
                        <div className="uk-yield-preview-theme-info">
                            <p>
                                <strong>{__('Current Theme:', 'uk-yield-rates')}</strong>{' '}
                                {theme === 'dark' ? __('Dark Mode', 'uk-yield-rates') : __('Light Mode', 'uk-yield-rates')}
                            </p>
                            <p className="description">
                                {__('Preview shows how yields will appear on the frontend.', 'uk-yield-rates')}
                            </p>
                        </div>
                    </PanelRow>

                    <PanelRow>
                        <div className="uk-yield-preview-shortcode">
                            <strong>{__('Generated Shortcode:', 'uk-yield-rates')}</strong>
                            <code className="uk-yield-shortcode-display">
                                {buildShortcode(attributes)}
                            </code>
                            <Button
                                isSmall
                                onClick={() => {
                                    navigator.clipboard.writeText(buildShortcode(attributes));
                                    // Could add a snackbar notification here
                                }}
                            >
                                {__('Copy', 'uk-yield-rates')}
                            </Button>
                        </div>
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
                        className={`uk-yield-block-content uk-yield-theme-${theme}`}
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
