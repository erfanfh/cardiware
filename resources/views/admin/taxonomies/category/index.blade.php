@extends('admin.layout')

@section('title', 'مدیریت دسته‌بندی‌ها')

@section('content')
    @livewire('admin.taxonomy.categories-table', [
    'editingId' => request('edit'),
    'search' => request('search')
    ])
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========== Slug Change Alert ==========
            const form = document.getElementById('term-form');
            const slugInput = document.getElementById('slug');
            const confirmInput = document.getElementById('slug-change-confirmed');

            if (form && slugInput) {
                const originalSlug = '{{ $editingTerm->slug ?? '' }}';

                form.addEventListener('submit', function (e) {
                    const currentSlug = slugInput.value.trim();

                    if (originalSlug && currentSlug !== originalSlug) {
                        e.preventDefault();
                        window.confirmSlugChange(originalSlug, currentSlug, form, confirmInput);
                    }
                });
            }

            // ========== Bulk Delete Alert ==========
            const bulkForm = document.querySelector('form[action="{{ route("admin.categories.bulk") }}"]');

            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    const actionSelect = this.querySelector('select[name="action"]');
                    const selectedAction = actionSelect.value;

                    const checkedCheckboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                    const checkedCount = checkedCheckboxes.length;

                    if (selectedAction === 'delete' && checkedCount > 0) {
                        e.preventDefault();
                        window.confirmBulkDelete(checkedCount, checkedCheckboxes, bulkForm);
                    } else if (selectedAction === 'delete' && checkedCount === 0) {
                        e.preventDefault();
                        window.showNoItemsSelected();
                    }
                });
            }
        });
    </script>
@endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // ========== Slug Change Alert ==========
                    const form = document.getElementById('term-form');
                    const slugInput = document.getElementById('slug');
                    const confirmInput = document.getElementById('slug-change-confirmed');

                    if (form && slugInput) {
                        const originalSlug = '{{ $editingTerm->slug ?? '' }}';

                        form.addEventListener('submit', function (e) {
                            const currentSlug = slugInput.value.trim();

                            if (originalSlug && currentSlug !== originalSlug) {
                                e.preventDefault();
                                window.confirmSlugChange(originalSlug, currentSlug, form, confirmInput);
                            }
                        });
                    }

                    // ========== Bulk Delete Alert ==========
                    const bulkForm = document.querySelector('form[action="{{ route("admin.categories.bulk") }}"]');

                    if (bulkForm) {
                        bulkForm.addEventListener('submit', function (e) {
                            const actionSelect = this.querySelector('select[name="action"]');
                            const selectedAction = actionSelect.value;

                            const checkedCheckboxes = document.querySelectorAll('input[name="ids[]"]:checked');
                            const checkedCount = checkedCheckboxes.length;

                            if (selectedAction === 'delete' && checkedCount > 0) {
                                e.preventDefault();
                                window.confirmBulkDelete(checkedCount, checkedCheckboxes, bulkForm);
                            } else if (selectedAction === 'delete' && checkedCount === 0) {
                                e.preventDefault();
                                window.showNoItemsSelected();
                            }
                        });
                    }
                });
            </script>
    @endpush
