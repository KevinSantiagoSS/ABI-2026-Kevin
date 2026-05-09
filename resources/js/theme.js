const STORAGE_KEY = 'tablerTheme';
const VALID_THEMES = new Set(['light', 'dark']);
const root = document.documentElement;

function normalizeTheme(theme) {
    return VALID_THEMES.has(theme) ? theme : 'light';
}

function safeGetStoredTheme() {
    try {
        return normalizeTheme(localStorage.getItem(STORAGE_KEY));
    } catch (error) {
        return 'light';
    }
}

function syncBodyTheme(theme) {
    if (!document.body) {
        return;
    }

    document.body.setAttribute('data-bs-theme', theme);
    document.body.classList.toggle('theme-dark', theme === 'dark');
    document.body.style.colorScheme = theme;
}

function applyTheme(theme, { persist = true } = {}) {
    const normalizedTheme = normalizeTheme(theme);

    root.classList.add('theme-changing');
    root.setAttribute('data-bs-theme', normalizedTheme);
    root.classList.toggle('theme-dark', normalizedTheme === 'dark');
    root.style.colorScheme = normalizedTheme;

    syncBodyTheme(normalizedTheme);

    if (persist) {
        try {
            localStorage.setItem(STORAGE_KEY, normalizedTheme);
        } catch (error) {
            // Ignore storage errors and continue with the applied theme.
        }
    }

    window.__abiTheme = {
        storageKey: STORAGE_KEY,
        value: normalizedTheme,
    };

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            root.classList.remove('theme-changing');
        });
    });

    document.dispatchEvent(new CustomEvent('theme:changed', {
        detail: { theme: normalizedTheme },
    }));

    return normalizedTheme;
}

function currentTheme() {
    return normalizeTheme(root.getAttribute('data-bs-theme'));
}

function handleThemeClick(event) {
    const trigger = event.target.closest('[data-theme-value]');

    if (!trigger) {
        return;
    }

    event.preventDefault();
    applyTheme(trigger.dataset.themeValue);
}

document.addEventListener('click', handleThemeClick);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(safeGetStoredTheme(), { persist: false });
    }, { once: true });
} else {
    applyTheme(safeGetStoredTheme(), { persist: false });
}

window.ABITheme = {
    get: currentTheme,
    set: (theme) => applyTheme(theme),
    toggle: () => applyTheme(currentTheme() === 'dark' ? 'light' : 'dark'),
};
