document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('darkModeToggle');
    const isDark = document.cookie.includes('theme=dark');

    // Apply saved theme
    if (isDark) {
        document.body.classList.add('dark-mode');
        if (toggle) toggle.checked = true;
    }

    if (toggle) {
        toggle.addEventListener('change', () => {
            const enable = toggle.checked;
            document.body.classList.toggle('dark-mode', enable);
            document.cookie = `theme=${enable ? 'dark' : 'light'};path=/;max-age=31536000`;
        });
    }
});
