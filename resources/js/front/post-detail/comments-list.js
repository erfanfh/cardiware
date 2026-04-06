window.commentsWidget = function (config) {
    return {
        isOpen: false,
        hasLoaded: false,
        loading: false,
        errorMessage: '',

        sort: 'new',
        replyTo: null,

        comments: [],
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        },
        stats: {
            total_comments: config.initialTotalComments ?? 0,
            average_rating: null,
        },

        init() {
            window.addEventListener('comment:created', (event) => {
                const comment = event.detail?.comment;

                if (!comment) {
                    return;
                }

                this.stats.total_comments = (this.stats.total_comments || 0) + 1;

                if (this.hasLoaded && this.isOpen && this.sort === 'new' && this.meta.current_page === 1) {
                    this.comments.unshift(comment);

                    if (this.comments.length > this.meta.per_page) {
                        this.comments.pop();
                    }
                }
            });
        },

        displayTotalCount() {
            return this.stats.total_comments || this.meta.total || 0;
        },

        openButtonLabel() {
            const total = this.displayTotalCount();

            if (total > 0) {
                return `مشاهده ${total} دیدگاه`;
            }

            return 'مشاهده دیدگاه‌ها';
        },

        avatarText(name) {
            const value = (name || '').trim();

            return value ? value.charAt(0) : 'ک';
        },

        async openComments() {
            if (this.loading) {
                return;
            }

            this.errorMessage = '';

            if (this.hasLoaded) {
                this.isOpen = true;
                return;
            }

            const success = await this.fetchComments(1);

            if (success) {
                this.isOpen = true;
            }

            if (this.displayTotalCount() === 0) {
                setTimeout(() => {
                    this.scrollToForm();
                }, 300);
            }
        },

        async fetchComments(page = 1) {
            this.loading = true;
            this.errorMessage = '';

            try {
                const url = new URL(config.listEndpoint, window.location.origin);
                url.searchParams.set('sort', this.sort);
                url.searchParams.set('page', page);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let data = null;

                try {
                    data = await response.json();
                } catch (_) {
                    data = null;
                }

                if (!response.ok) {
                    this.errorMessage = data?.message || 'دریافت دیدگاه‌ها با خطا مواجه شد.';
                    return false;
                }

                this.comments = Array.isArray(data?.data) ? data.data : [];
                this.meta = {
                    current_page: data?.meta?.current_page ?? 1,
                    last_page: data?.meta?.last_page ?? 1,
                    per_page: data?.meta?.per_page ?? 10,
                    total: data?.meta?.total ?? 0,
                };
                this.stats = {
                    total_comments: data?.stats?.total_comments ?? data?.meta?.total ?? 0,
                    average_rating: data?.stats?.average_rating ?? null,
                };

                this.hasLoaded = true;
                this.replyTo = null;

                return true;
            } catch (error) {
                this.errorMessage = 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.';
                return false;
            } finally {
                this.loading = false;
            }
        },

        async changeSort(value) {
            if (this.loading) {
                return;
            }

            this.sort = value;
            this.replyTo = null;

            const success = await this.fetchComments(1);

            if (!success) {
                return;
            }

            this.$nextTick(() => {
                this.$root.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });
        },

        async goToPage(page) {
            if (this.loading) {
                return;
            }

            if (page < 1 || page > this.meta.last_page) {
                return;
            }

            this.replyTo = null;

            const success = await this.fetchComments(page);

            if (!success) {
                return;
            }

            this.$nextTick(() => {
                this.$root.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });
        },

        showPagination() {
            return (this.meta.last_page || 1) > 1;
        },

        paginationItems() {
            const current = this.meta.current_page || 1;
            const last = this.meta.last_page || 1;

            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);

            const pages = [];

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        },

        toggleLike(comment) {
            comment.liked = !comment.liked;
            comment.likes += comment.liked ? 1 : -1;
        },

        startReply(comment) {
            this.replyTo = comment.id;

            this.$nextTick(() => {
                const textarea = document.querySelector('[x-show="replyTo === ' + comment.id + '"] textarea');
                if (textarea) {
                    textarea.scrollIntoView({behavior: 'smooth', block: 'center'});
                    textarea.focus();
                }
            });
        },

        scrollToForm() {
            const el = document.getElementById('send-comment-form');
            if (!el) return;

            const yOffset = -140;
            const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;

            window.scrollTo({
                top: y,
                behavior: 'smooth'
            });
        },
    };
};
