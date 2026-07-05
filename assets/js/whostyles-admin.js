document.addEventListener('DOMContentLoaded', () => {
    const hashField = document.getElementById('whostyles_hash_field');
    const previewContainer = document.getElementById('whostyles_preview_container');
    const previewLight = document.getElementById('whostyles_preview_light');
    const previewDark = document.getElementById('whostyles_preview_dark');

    if (!hashField || !previewContainer || !previewLight || !previewDark) {
        return;
    }

    function updatePreview() {
        const hash = hashField.value.trim();
        if (!hash) {
            previewContainer.style.display = 'none';
            return;
        }

        // Validate format: {ws1:48chars}
        const regex = /^\{ws1:[A-Za-z0-9\-.]{48}\}$/;
        if (!regex.test(hash)) {
            previewContainer.style.display = 'none';
            return;
        }

        if (typeof window.WhostyleCore === 'undefined') {
            return;
        }

        const decoded = window.WhostyleCore.decode(hash);
        if (!decoded) {
            previewContainer.style.display = 'none';
            return;
        }

        // Show container.
        previewContainer.style.display = 'block';

        // Apply attributes to the light and dark preview containers.
        const lightAttrs = window.WhostyleCore.generateAttributes(hash);
        
        // Parse attributes and apply styling classes & inline styles.
        applyWhostyleToElement(previewLight, lightAttrs, 'light');
        applyWhostyleToElement(previewDark, lightAttrs, 'dark');
    }

    function applyWhostyleToElement(element, attrsString, mode) {
        // Remove existing whostyle classes.
        const classesToRemove = Array.from(element.classList).filter(c => 
            c.startsWith('ws-') || c === 'whostyle-container' || c === 'whostyle-rendered'
        );
        classesToRemove.forEach(c => element.classList.remove(c));

        // Reset inline styles.
        element.style.cssText = '';

        // Extract classes and style contents from the generated attributes string.
        const classMatch = attrsString.match(/class="([^"]+)"/);
        const styleMatch = attrsString.match(/style="([^"]+)"/);

        if (classMatch) {
            const classes = classMatch[1].split(' ');
            classes.forEach(c => element.classList.add(c));
        }

        if (styleMatch) {
            element.style.cssText = styleMatch[1];
        }

        // Enforce the specific mode (light or dark) styling preview variables locally.
        if (mode === 'light') {
            element.style.setProperty('background-color', 'var(--ws-light-bg)');
            element.style.setProperty('color', 'var(--ws-light-text)');
            element.style.setProperty('border-color', 'var(--ws-light-border)');
        } else {
            element.style.setProperty('background-color', 'var(--ws-dark-bg)');
            element.style.setProperty('color', 'var(--ws-dark-text)');
            element.style.setProperty('border-color', 'var(--ws-dark-border)');
        }
    }

    // Bind event listeners.
    hashField.addEventListener('input', updatePreview);

    // Run preview once on startup.
    updatePreview();
});
