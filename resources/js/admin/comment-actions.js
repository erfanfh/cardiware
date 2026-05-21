document.addEventListener('alpine:init', () => {
    Alpine.data('adminCommentActions', (
        {
            bulkActionUrl = '',
            csrfToken = '',
            initialStatuses = {},
        }
    ) => ({
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
            id = Number(id);

            if (action === 'delete') {
                this.hiddenIds.push(id);
                return;
            }

            if (action === 'approve') {
                this.applyCommentStatus(id, 'approved');
                return;
            }

            if (action === 'reject') {
                this.applyCommentStatus(id, 'rejected');
                return;
            }

            if (action === 'pending') {
                this.applyCommentStatus(id, 'pending');
            }
        },

        applyCommentStatus(id, status) {
            id = Number(id);

            const row = document.querySelector(`[data-comment-row="${id}"]`);

            this.setRowStatus(id, status);

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

            if (status === 'approved') {
                row.classList.add('!border-b-slate-200');
                return;
            }

            if (status === 'rejected') {
                row.classList.add('!bg-rose-50', '!border-b-rose-200/70');
                return;
            }

            if (status === 'pending') {
                row.classList.add('!bg-amber-50', '!border-b-amber-200/70');
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

        rowStatuses: Object.fromEntries(
            Object.entries(initialStatuses).map(([id, status]) => [Number(id), status || 'unknown'])
        ),

        rowStatus(id) {
            return this.rowStatuses[Number(id)] || 'unknown';
        },

        setRowStatus(id, status) {
            this.rowStatuses[Number(id)] = status || 'unknown';
        },

        replyBadgeClass(id) {
            const status = this.rowStatus(id);

            if (status === 'pending') {
                return 'bg-amber-100 text-amber-700 border-amber-200';
            }

            if (status === 'rejected') {
                return 'bg-rose-100 text-rose-700 border-rose-200';
            }

            return 'bg-slate-100 text-slate-700 border-slate-200';
        },

        replyModal: {
            open: false,
            processing: false,
            id: null,
            author: '',
            parentContent: '',
            content: '',
            error: null,
            replyUrl: '',
        },

        openReplyModal(payload) {
            this.replyModal.open = true;
            this.replyModal.processing = false;
            this.replyModal.id = Number(payload.id);
            this.replyModal.author = payload.author || 'کاربر';
            this.replyModal.parentContent = payload.content || '';
            this.replyModal.content = '';
            this.replyModal.error = null;
            this.replyModal.replyUrl = payload.replyUrl || '';

            this.$nextTick(() => {
                const textarea = document.querySelector('[x-model="replyModal.content"]');
                textarea?.focus();
            });
        },

        closeReplyModal(force = false) {
            if (this.replyModal.processing && !force) {
                return;
            }

            this.replyModal.open = false;
            this.replyModal.id = null;
            this.replyModal.author = '';
            this.replyModal.parentContent = '';
            this.replyModal.content = '';
            this.replyModal.error = null;
            this.replyModal.replyUrl = '';
        },

        async submitReply() {
            const content = String(this.replyModal.content || '').trim();

            if (!content) {
                this.replyModal.error = 'لطفاً متن پاسخ را وارد کنید.';
                return;
            }

            this.replyModal.processing = true;
            this.replyModal.error = null;

            try {
                const formData = new FormData();
                formData.append('content', content);

                const response = await fetch(this.replyModal.replyUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: formData,
                });

                const data = await this.parseJsonResponse(response);

                if (!response.ok || !data?.ok) {
                    throw new Error(
                        data?.message ||
                        data?.errors?.content?.[0] ||
                        data?.errors?.comment?.[0] ||
                        'ثبت پاسخ با خطا مواجه شد.'
                    );
                }

                this.notifySuccess(data.message || 'پاسخ با موفقیت ثبت شد.');

                const parentCommentId = this.replyModal.id;

                const parentStatus =
                    data.parent_comment?.status ||
                    data.parentComment?.status ||
                    data.comment?.parent_status ||
                    data.status;

                if (parentCommentId && parentStatus) {
                    this.applyCommentStatus(parentCommentId, parentStatus);
                }

                this.closeReplyModal(true);
            } catch (error) {
                console.error(error);
                this.replyModal.error = error.message || 'خطایی در ثبت پاسخ رخ داد.';
                this.notifyError(this.replyModal.error);
            } finally {
                this.replyModal.processing = false;
            }
        },
    }));
});
