document.addEventListener('alpine:init', () => {
    Alpine.data('adminListQuickEdit', (initialRows = {}, initialOpenId = null) => ({
        openId: initialOpenId,
        rows: initialRows,
        savingRowIds: [],
        errors: {},

        isOpen(id) {
            return Number(this.openId) === Number(id);
        },

        open(id) {
            this.openId = Number(id);
        },

        close() {
            this.openId = null;
        },

        toggle(id) {
            id = Number(id);
            this.openId = this.openId === id ? null : id;
        },

        onEscape(event) {
            if (event.key === 'Escape') {
                this.close();
            }
        },

        isSaving(id) {
            return this.savingRowIds.includes(Number(id));
        },

        startSaving(id) {
            id = Number(id);

            if (!this.isSaving(id)) {
                this.savingRowIds.push(id);
            }
        },

        stopSaving(id) {
            id = Number(id);
            this.savingRowIds = this.savingRowIds.filter(item => Number(item) !== id);
        },

        clearErrors(id) {
            this.errors[id] = {};
        },

        setErrors(id, errors) {
            this.errors[id] = errors || {};
        },

        fieldError(id, field) {
            return this.errors?.[id]?.[field]?.[0] ?? null;
        },

        hasErrors(id) {
            return Object.keys(this.errors?.[id] || {}).length > 0;
        },

        async submitQuickEdit(form, id) {
            id = Number(id);
            this.startSaving(id);
            this.clearErrors(id);

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.setErrors(id, data.errors || {});
                        return;
                    }

                    throw new Error(data.message || 'خطایی در ذخیره‌سازی رخ داد.');
                }

                this.rows[id] = {
                    ...this.rows[id],
                    ...data.updatedItem,
                };

                this.clearErrors(id);
                this.close();

                window.dispatchEvent(new CustomEvent('admin-toast', {
                    detail: {
                        type: 'success',
                        message: data.message || 'اطلاعات پروژه با موفقیت ذخیره شد.',
                    }
                }));
            } catch (error) {
                this.setErrors(id, {
                    general: [error.message || 'خطایی در ذخیره‌سازی رخ داد.'],
                });
            } finally {
                this.stopSaving(id);
            }
        },
    }));
});

document.addEventListener('alpine:init', () => {
    Alpine.data('adminBulkSelection', () => ({
        selected: [],
        bulkAction: '',

        toggleSelection(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item !== id);
                return;
            }

            this.selected.push(id);
        },

        isSelected(id) {
            return this.selected.includes(id);
        },

        toggleAll(ids) {
            if (this.selected.length === ids.length) {
                this.selected = [];
                return;
            }

            this.selected = [...ids];
        },

        clearSelection() {
            this.selected = [];
        },

        get selectedCount() {
            return this.selected.length;
        },

        isAllSelected(ids) {
            return ids.length > 0 && this.selected.length === ids.length;
        },

        submitBulkAction(formEl) {
            if (this.selectedCount === 0 || !this.bulkAction || !formEl) {
                return;
            }

            formEl.submit();
        },
    }));
});
