window.commentForm = function (config) {
    return {
        loading: false,
        successMessage: '',
        generalError: '',
        errors: {},

        newRating: config.initialRating ?? 0,
        hoverRating: 0,

        form: {
            parent_id: config.initialParentId || '',
            author_name: config.initialAuthorName || '',
            author_email: config.initialAuthorEmail || '',
            content: config.initialContent || '',
            rating_percent: (config.initialRating ?? 0) ? (config.initialRating * 20) : '',
        },

        setRating(value) {
            if (this.loading) {
                return;
            }

            this.newRating = value;
            this.form.rating_percent = value ? value * 20 : '';
            this.clearFieldError('rating_percent');
        },

        clearFieldError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
                this.errors = {...this.errors};
            }
        },

        resetMessages() {
            this.successMessage = '';
            this.generalError = '';
        },

        resetErrors() {
            this.errors = {};
        },

        resetForm() {
            this.form.parent_id = '';
            this.form.content = '';
            this.form.rating_percent = '';
            this.newRating = 0;
            this.hoverRating = 0;

            if (config.isGuest) {
                this.form.author_name = '';
                this.form.author_email = '';
            }
        },

        focusFirstErrorField() {
            this.$nextTick(() => {
                const fieldOrder = [
                    'author_name',
                    'author_email',
                    'content',
                ];

                for (const fieldName of fieldOrder) {
                    if (!this.errors[fieldName]) {
                        continue;
                    }

                    const field = this.$root.querySelector(`[name="${fieldName}"]`);

                    if (field) {
                        field.focus();
                        break;
                    }
                }
            });
        },

        async submit() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.resetMessages();
            this.resetErrors();
            this.form.rating_percent = this.newRating ? this.newRating * 20 : '';

            try {
                const response = await fetch(config.endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify(this.form),
                });

                let data = null;

                try {
                    data = await response.json();
                } catch (_) {
                    data = null;
                }

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = Object.fromEntries(
                            Object.entries(data?.errors || {}).map(([key, value]) => [key, value[0]])
                        );

                        this.focusFirstErrorField();
                    } else {
                        this.generalError = data?.message || 'ارسال دیدگاه با خطا مواجه شد.';
                    }

                    return;
                }

                this.successMessage = data?.message || 'دیدگاه شما با موفقیت ثبت شد.';
                this.resetForm();

                if (data?.comment) {
                    window.dispatchEvent(new CustomEvent('comment:created', {
                        detail: {
                            comment: data.comment,
                        },
                    }));
                }

                this.$nextTick(() => {
                    this.$root.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                });

                window.setTimeout(() => {
                    this.successMessage = '';
                }, 5000);
            } catch (error) {
                this.generalError = 'ارتباط با سرور برقرار نشد. دوباره تلاش کنید.';
            } finally {
                this.loading = false;
            }
        },
    };
};
