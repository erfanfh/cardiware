import './lightbox-gallery.js';
import '../localize-display-digits.js';
import './popups.js';
import typingPlaceholder from './typing-placeholder.js';
import './old-toggle-box.js';
import './sticky-columns.js';
import './scroll-to-top.js';
import './footer-news.js';
import './career-page.js';
import './product-detail/image-gallery-slider.js';
import './product-detail/team-members.js';
import './product-detail/video-gallery.js';
import productBuyBox from './product-detail/product-buy-box.js';
import './product-list/hero-slider.js';
import productTabs from './product-detail/tabs.js';
import storyViewer from './story-viewer.js';

Alpine.data('productTabs', productTabs);
Alpine.data('storyViewer', storyViewer);

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

import initTimeSpentTracker from './loyalty/time-spent-tracker.js';
import initLoyaltyEarnedToast from './loyalty/earned-toast.js';
import initRecentPurchases from './recent-purchases.js';

initLoyaltyEarnedToast();
initTimeSpentTracker();
initRecentPurchases();
