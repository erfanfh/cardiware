import './lightbox-gallery.js';
import typingPlaceholder from './typing-placeholder.js';
import './old-toggle-box.js';
import './sticky-columns.js';
import './scroll-to-top.js';
import './product-detail/image-gallery-slider.js';
import './product-detail/team-members.js';
import productBuyBox from './product-detail/product-buy-box.js';
import './product-list/hero-slider.js';
import productTabs from './product-detail/tabs.js';

Alpine.data('productTabs', productTabs);

window.productBuyBox = productBuyBox;

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
