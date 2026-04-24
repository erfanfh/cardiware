document.addEventListener('alpine:init', () => {
    Alpine.data('adminCommentActions', ({bulkActionUrl = '', csrfToken = ''}) => ({
        bulkActionUrl,
        csrfToken,
        processingIds: [],
        hiddenIds: [],

        isProcessing(id) {
            return this.processingIds.includes(Number(id));
        },

        isHidden(id) {
            return this.hiddenIds.includes(Number(id));
        },

        async confirmAndRun(id, action, message) {
            if (!window.confirm(message)) {
                return;
            }

            await this.runAction(id, action);
        },

        async runAction(id, action) {
            id = Number(id);

            if (this.isProcessing(id)) {
                return;
            }

            this.processingIds.push(id);

            try {
                const response = await fetch(this.bulkActionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: this.buildFormData(action, [id]),
                });

                const data = await this.parseJsonResponse(response);

                if (!response.ok || !data?.ok) {
                    throw new Error(data?.message || 'عملیات با خطا مواجه شد.');
                }

                this.applyRowResult(id, action);
                this.notifySuccess(data.message || 'عملیات با موفقیت انجام شد.');
            } catch (error) {
                console.error(error);
                this.notifyError(error.message || 'خطایی رخ داد.');
            } finally {
                this.processingIds = this.processingIds.filter((item) => item !== id);
            }
        },

        applyRowResult(id, action) {
            const row = document.querySelector(`[data-comment-row="${id}"]`);

            if (action === 'delete') {
                this.hiddenIds.push(id);
                return;
            }

            if (!row) {
                return;
            }

            row.classList.remove(
                '!bg-amber-50',
                '!border-b-amber-200/70',
                '!bg-rose-50',
                '!border-b-rose-200/70',
                '!border-b-slate-200'
            );

            if (action === 'approve') {
                row.classList.add('!border-b-slate-200');
            }

            if (action === 'reject') {
                row.classList.add('!bg-rose-50', '!border-b-rose-200/70');
            }
        },

        buildFormData(action, ids) {
            const formData = new FormData();

            formData.append('bulk_action', action);

            ids.forEach((id) => {
                formData.append('ids[]', String(id));
            });

            return formData;
        },

        async parseJsonResponse(response) {
            const rawText = await response.text();

            try {
                return rawText ? JSON.parse(rawText) : null;
            } catch (error) {
                console.error('Invalid JSON response:', rawText);
                throw new Error('پاسخ سرور معتبر نیست.');
            }
        },

        notifySuccess(message) {
            if (window.toast?.success) {
                window.toast.success(message);
                return;
            }

            console.log(message);
        },

        notifyError(message) {
            if (window.toast?.error) {
                window.toast.error(message);
                return;
            }

            alert(message);
        },
    }));
});
