(function () {
    const el = wp.element.createElement;
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost;
    const { PanelBody, TextControl } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { useEffect, useState } = wp.element;

    const WhostylesSidebar = () => {
        // Retrieve post metadata from Gutenberg editor store.
        const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta') || {});
        const hash = meta._whostyle_hash || '';

        const { editPost } = useDispatch('core/editor');

        const [previewAttrs, setPreviewAttrs] = useState(null);

        // Update live preview parameters when hash changes.
        useEffect(() => {
            if (!hash) {
                setPreviewAttrs(null);
                return;
            }

            const regex = /^\{ws1:[A-Za-z0-9\-.]{48}\}$/;
            if (!regex.test(hash) || typeof window.WhostyleCore === 'undefined') {
                setPreviewAttrs(null);
                return;
            }

            const decoded = window.WhostyleCore.decode(hash);
            if (!decoded) {
                setPreviewAttrs(null);
                return;
            }

            const attrs = window.WhostyleCore.generateAttributes(hash);
            setPreviewAttrs(attrs);
        }, [hash]);

        const setHash = (newHash) => {
            editPost({ meta: { _whostyle_hash: newHash } });
        };

        // Parse class and style properties from the raw attributes string.
        let classes = '';
        let styles = {};
        if (previewAttrs) {
            const classMatch = previewAttrs.match(/class="([^"]+)"/);
            const styleMatch = previewAttrs.match(/style="([^"]+)"/);
            if (classMatch) {
                classes = classMatch[1];
            }
            if (styleMatch) {
                // Convert CSS style inline string to React styles object.
                const styleStr = styleMatch[1];
                styleStr.split(';').forEach(rule => {
                    const parts = rule.split(':');
                    if (parts.length === 2) {
                        styles[parts[0].trim()] = parts[1].trim();
                    }
                });
            }
        }

        const renderPreviewBox = (mode) => {
            if (!previewAttrs) return null;

            const isLight = mode === 'light';
            const localStyles = {
                ...styles,
                padding: '12px',
                border: '1px solid',
                borderRadius: styles['--ws-bradius'] || '4px',
                borderColor: isLight ? 'var(--ws-light-border)' : 'var(--ws-dark-border)',
                backgroundColor: isLight ? 'var(--ws-light-bg)' : 'var(--ws-dark-bg)',
                color: isLight ? 'var(--ws-light-text)' : 'var(--ws-dark-text)',
                marginTop: '10px'
            };

            return el(
                'div',
                { style: { marginBottom: '15px' } },
                el(
                    'span',
                    { style: { fontSize: '10px', fontWeight: 'bold', color: isLight ? '#666' : '#ccc' } },
                    isLight ? 'LIGHT MODE PREVIEW' : 'DARK MODE PREVIEW'
                ),
                el(
                    'div',
                    { className: classes, style: localStyles },
                    el('h4', { style: { margin: '0 0 5px 0' } }, 'Preview Header'),
                    el('p', { style: { margin: 0, fontSize: '13px' } }, 'Syndicated Whostyle preview content.')
                )
            );
        };

        return el(
            PluginSidebar,
            {
                name: 'whostyles-sidebar',
                title: 'Whostyles Settings',
                icon: 'art'
            },
            el(
                PanelBody,
                { title: 'Syndication Hash', initialOpen: true },
                el(
                    TextControl,
                    {
                        label: 'Whostyle V1 Hash',
                        value: hash,
                        onChange: setHash,
                        placeholder: '{ws1:...}'
                    }
                ),
                previewAttrs && el(
                    'div',
                    { style: { marginTop: '20px', borderTop: '1px solid #eee', paddingTop: '15px' } },
                    el('div', { style: { fontWeight: 'bold', marginBottom: '10px' } }, 'Live Preview'),
                    renderPreviewBox('light'),
                    renderPreviewBox('dark')
                )
            )
        );
    };

    registerPlugin('whostyles-gutenberg', {
        render: WhostylesSidebar
    });
})();
