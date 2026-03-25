@extends('admin.layout')

@section('title', 'لیست پروژه‌ها')

@section('content')
    @include('admin.projects.projects.partials.filters-panel')

    @php
        $projectIds = $projects->pluck('id')->values()->all();
    @endphp

    <div
        x-data="adminBulkSelection()"
        class="space-y-4"
    >
        <x-admin.list.bulk-bar
            :action="route('admin.projects.bulk-action')"
            :options="$bulkActionOptions"
            button-text="اعمال عملیات گروهی"
            idle-text="برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند پروژه را انتخاب کن."
            selected-text="پروژه انتخاب شده"
        />

        @include('admin.projects.projects.partials.list-table')

        @if(method_exists($projects, 'links'))
            <x-admin.list.pagination-wrap>
                <div class="text-xs text-slate-500">
                    @if(method_exists($projects, 'total'))
                        نمایش
                        <span class="font-bold text-slate-700">{{ $projects->firstItem() }}</span>
                        تا
                        <span class="font-bold text-slate-700">{{ $projects->lastItem() }}</span>
                        از
                        <span class="font-bold text-slate-700">{{ $projects->total() }}</span>
                        پروژه
                    @endif
                </div>

                <div
                    class="[&_nav]:flex [&_nav]:items-center [&_nav]:gap-1 [&_span]:rounded-lg [&_span]:border [&_span]:border-slate-200 [&_span]:bg-white [&_span]:px-3 [&_span]:py-1.5 [&_span]:text-sm [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:bg-white [&_a]:px-3 [&_a]:py-1.5 [&_a]:text-sm [&_a]:text-slate-700 [&_a]:transition [&_a:hover]:bg-slate-50">
                    {{ $projects->links() }}
                </div>
            </x-admin.list.pagination-wrap>
        @endif
    </div>

    <x-admin.modal.confirm
        name="deleteProject"
        title="حذف پروژه"
        description="با حذف این پروژه، اطلاعات وابسته ممکن است تحت تأثیر قرار بگیرند. این عملیات را فقط در صورت اطمینان انجام بده."
        confirm-text="بله، حذف شود"
        cancel-text="انصراف"
    />

    <form
        x-data="{ deleteId: null }"
        x-on:confirm-delete-project.window="
        $dispatch('open-confirm-modal', {
            name: 'deleteProject',
            payload: { id: $event.detail.id }
        })
    "
        x-on:confirm-modal-accepted.window="
        if ($event.detail.name !== 'deleteProject') return;
        deleteId = $event.detail.payload.id;
        $refs.projectDeleteForm.action = '{{ url('/admin/projects') }}/' + deleteId;
        $refs.projectDeleteForm.submit();
    "
        x-ref="projectDeleteFormWrapper"
        class="hidden"
    >
        <form x-ref="projectDeleteForm" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </form>
@endsection

@push('scripts')
    <script>
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
                            ...data.project,
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
    </script>

    <script>
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
    </script>
@endpush
