import Swiper from 'swiper';

import {A11y, Keyboard, Navigation, Pagination,} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

/**
 * Product team members Alpine component.
 *
 * @param {Array} members
 * @param {string|null} modalId
 *
 * @returns {Object}
 */
window.productTeamMembersSlider = function (
    members = [],
    modalId = null
) {
    return {
        members: Array.isArray(members)
            ? members
            : [],

        modalId,

        swiper: null,
        selectedMember: null,
        triggerElement: null,
        closeTimer: null,

        isOpen: false,
        previousBodyOverflow: '',

        /**
         * Alpine initialization lifecycle.
         */
        init() {
            this.$nextTick(() => {
                this.initializeSwiper();
            });
        },

        /**
         * Initialize the Swiper slider.
         */
        initializeSwiper() {
            if (!this.$refs.swiper || this.swiper) {
                return;
            }

            const hasNavigation = Boolean(
                this.$refs.previousButton
                && this.$refs.nextButton
            );

            const hasPagination = Boolean(
                this.$refs.pagination
            );

            this.swiper = new Swiper(
                this.$refs.swiper,
                {
                    modules: [
                        A11y,
                        Keyboard,
                        Navigation,
                        Pagination,
                    ],

                    direction: 'horizontal',

                    slidesPerView: 1.08,
                    spaceBetween: 12,

                    speed: 500,

                    grabCursor: true,
                    watchOverflow: true,

                    observer: true,
                    observeParents: true,
                    resizeObserver: true,

                    navigation: hasNavigation
                        ? {
                            prevEl: this.$refs.previousButton,
                            nextEl: this.$refs.nextButton,
                            disabledClass: 'swiper-button-disabled',
                        }
                        : undefined,

                    pagination: hasPagination
                        ? {
                            el: this.$refs.pagination,
                            clickable: true,
                        }
                        : undefined,

                    keyboard: {
                        enabled: true,
                        onlyInViewport: true,
                    },

                    a11y: {
                        enabled: true,
                        prevSlideMessage: 'Show previous member',
                        nextSlideMessage: 'Show next member',
                        firstSlideMessage: 'This is the first member',
                        lastSlideMessage: 'This is the last member',
                        paginationBulletMessage: 'Go to member number {{index}}',
                    },

                    breakpoints: {
                        420: {
                            slidesPerView: 1.2,
                            spaceBetween: 12,
                        },

                        480: {
                            slidesPerView: 1.45,
                            spaceBetween: 14,
                        },

                        640: {
                            slidesPerView: 2,
                            spaceBetween: 16,
                        },

                        768: {
                            slidesPerView: 2.3,
                            spaceBetween: 16,
                        },

                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 18,
                        },

                        1280: {
                            slidesPerView: 4,
                            spaceBetween: 20,
                        },
                    },
                }
            );
        },

        /**
         * Open selected member modal.
         *
         * @param {number} index
         * @param {Event|null} event
         */
        openMember(index, event = null) {
            const member = this.members[index];

            if (!member) {
                return;
            }

            if (this.closeTimer) {
                window.clearTimeout(this.closeTimer);
                this.closeTimer = null;
            }

            this.selectedMember = member;

            this.triggerElement =
                event?.currentTarget
                ?? document.activeElement;

            this.previousBodyOverflow =
                document.body.style.overflow;

            this.isOpen = true;

            document.body.style.overflow = 'hidden';

            this.$nextTick(() => {
                window.requestAnimationFrame(() => {
                    this.focusCloseButton();
                });
            });
        },

        /**
         * Close member modal.
         */
        closeModal() {
            if (!this.isOpen) {
                return;
            }

            this.isOpen = false;

            document.body.style.overflow =
                this.previousBodyOverflow;

            const triggerElement = this.triggerElement;

            this.closeTimer = window.setTimeout(() => {
                this.selectedMember = null;
                this.triggerElement = null;
                this.closeTimer = null;

                triggerElement?.focus?.();
            }, 220);
        },

        /**
         * Focus modal close button.
         */
        focusCloseButton() {
            if (!this.modalId) {
                return;
            }

            const modalElement = document.getElementById(
                this.modalId
            );

            const closeButton = modalElement?.querySelector(
                '[data-team-member-close]'
            );

            closeButton?.focus();
        },

        /**
         * Does selected member have any contact information?
         */
        get hasContactInformation() {
            if (!this.selectedMember) {
                return false;
            }

            return Boolean(
                this.selectedMember.email
                || this.selectedMember.mobile
                || this.selectedMember.website_url
            );
        },

        /**
         * Member email link.
         */
        get emailLink() {
            const email = this.selectedMember?.email;

            if (!email) {
                return '#';
            }

            return `mailto:${email}`;
        },

        /**
         * Member mobile link.
         */
        get mobileLink() {
            const mobile = this.selectedMember?.mobile;

            if (!mobile) {
                return '#';
            }

            const normalizedMobile = String(mobile)
                .replace(/[^\d+]/g, '');

            return `tel:${normalizedMobile}`;
        },

        /**
         * Alpine component destruction lifecycle.
         */
        destroy() {
            if (this.closeTimer) {
                window.clearTimeout(this.closeTimer);
                this.closeTimer = null;
            }

            if (this.isOpen) {
                document.body.style.overflow =
                    this.previousBodyOverflow;
            }

            if (this.swiper) {
                this.swiper.destroy(true, true);
                this.swiper = null;
            }

            this.selectedMember = null;
            this.triggerElement = null;
            this.isOpen = false;
        },
    };
};
