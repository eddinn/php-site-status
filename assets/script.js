// Get current theme from cookie
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

// Set cookie for theme (expires in 30 days)
function setCookie(name, value) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires.toUTCString()}; path=/`;
}

// Apply theme on page load
document.addEventListener('DOMContentLoaded', function () {
    const savedTheme = getCookie('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }

    const toggle = document.getElementById('darkModeToggle');
    if (toggle) {
        toggle.checked = document.body.classList.contains('dark-mode');

        toggle.addEventListener('change', function () {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                setCookie('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                setCookie('theme', 'light');
            }
        });
    }
});
