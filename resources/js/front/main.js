import './lightbox-gallery.js';
import typingPlaceholder from './typing-placeholder.js';
import './old-toggle-box.js';
import './sticky-columns.js';
import './scroll-to-top.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('typingPlaceholder', typingPlaceholder);

    Alpine.data('themeSwitcher', () => ({
        dark: document.documentElement.classList.contains('dark'),

        toggle() {
            this.dark = !this.dark;

            localStorage.setItem('theme', this.dark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', this.dark);
        }
    }));
});
