import Swiper from 'swiper';
import {Navigation, Pagination} from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

Swiper.use([Navigation, Pagination]);

const HTTP_PROTOCOLS = new Set(['http:', 'https:']);

const parseHttpUrl = (value) => {
    if (typeof value !== 'string' || value.trim() === '') {
        return null;
    }

    try {
        const url = new URL(value.trim());

        return HTTP_PROTOCOLS.has(url.protocol) ? url : null;
    } catch (_) {
        return null;
    }
};

const resolveAparatHash = (url) => {
    const hostname = url.hostname.toLowerCase();

    if (hostname !== 'aparat.com' && hostname !== 'www.aparat.com') {
        return null;
    }

    const embedMatch = url.pathname.match(/\/video\/video\/embed\/videohash\/([^/]+)/i);
    const pageMatch = url.pathname.match(/^\/v\/([^/]+)/i);
    const hash = embedMatch?.[1] ?? pageMatch?.[1] ?? null;

    if (!hash) {
        return '';
    }

    try {
        return decodeURIComponent(hash).trim();
    } catch (_) {
        return hash.trim();
    }
};

const resolvePlayer = (sourceUrl) => {
    const parsedUrl = parseHttpUrl(sourceUrl);

    if (!parsedUrl) {
        return {
            type: 'unsupported',
            src: null,
            source: null,
        };
    }

    const aparatHash = resolveAparatHash(parsedUrl);

    if (aparatHash === '') {
        return {
            type: 'unsupported',
            src: null,
            source: parsedUrl.toString(),
        };
    }

    if (aparatHash) {
        return {
            type: 'aparat',
            src: `https://www.aparat.com/video/video/embed/videohash/${encodeURIComponent(aparatHash)}/vt/frame`,
            source: parsedUrl.toString(),
        };
    }

    return {
        type: 'direct',
        src: parsedUrl.toString(),
        source: parsedUrl.toString(),
    };
};

window.productVideoGallery = function () {
    return {
        modalId: null,
        swiper: null,
        selectedVideo: null,
        playerError: false,
        previouslyFocused: null,
        previousBodyOverflow: '',

        init(modalId) {
            this.modalId = modalId;
            this.$nextTick(() => this.initSwiper());
        },

        initSwiper() {
            if (!this.$refs.slider || this.swiper) {
                return;
            }

            const options = {
                loop: false,
                speed: 450,
                spaceBetween: 14,
                slidesPerView: 1.08,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                breakpoints: {
                    640: {
                        slidesPerView: 1.45,
                        spaceBetween: 16,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 16,
                    },
                    1280: {
                        slidesPerView: 2.15,
                        spaceBetween: 18,
                    },
                },
            };

            if (this.$refs.next && this.$refs.prev) {
                options.navigation = {
                    nextEl: this.$refs.next,
                    prevEl: this.$refs.prev,
                };
            }

            if (this.$refs.pagination) {
                options.pagination = {
                    el: this.$refs.pagination,
                    clickable: true,
                };
            }

            this.swiper = new Swiper(this.$refs.slider, options);
        },

        openFromElement(element) {
            if (!element) {
                return;
            }

            const sourceUrl = element.dataset.videoUrl ?? '';
            const player = resolvePlayer(sourceUrl);

            this.playerError = false;
            this.previouslyFocused = document.activeElement;
            this.selectedVideo = {
                title: element.dataset.videoTitle ?? '',
                coverUrl: element.dataset.videoCover ?? '',
                sourceUrl: player.source,
                playerType: player.type,
                playerUrl: player.src,
            };

            this.lockPageScroll();

            this.$nextTick(() => {
                const modal = this.getModalElement();
                const closeButton = modal?.querySelector('[data-product-video-modal-close]');
                const directPlayer = modal?.querySelector('[data-product-video-direct-player]');

                closeButton?.focus({preventScroll: true});

                if (directPlayer instanceof HTMLMediaElement) {
                    directPlayer.play().catch(() => {
                        // Autoplay can be blocked by the browser. Controls remain available.
                    });
                }
            });
        },

        closeModal() {
            const modal = this.getModalElement();
            const directPlayer = modal?.querySelector('[data-product-video-direct-player]');

            if (directPlayer instanceof HTMLMediaElement) {
                directPlayer.pause();
                directPlayer.removeAttribute('src');
                directPlayer.load();
            }

            this.selectedVideo = null;
            this.playerError = false;
            this.unlockPageScroll();

            this.$nextTick(() => {
                if (this.previouslyFocused instanceof HTMLElement) {
                    this.previouslyFocused.focus({preventScroll: true});
                }

                this.previouslyFocused = null;
            });
        },

        handleDirectVideoError() {
            this.playerError = true;
        },

        lockPageScroll() {
            this.previousBodyOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        },

        unlockPageScroll() {
            document.body.style.overflow = this.previousBodyOverflow;
            this.previousBodyOverflow = '';
        },

        getModalElement() {
            return document.getElementById(this.modalId);
        },
    };
};
