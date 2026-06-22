<div class="space-y-6">
    <x-admin.metabox title="تنظیمات گفتینو">
        <div class="grid grid-cols-1 gap-5">
            <x-admin.field
                label="شناسه گفتینو"
            >
                <x-admin.form.input
                    name="goftino_code"
                    class="direction-ltr"
                    value="{{ get_setting('general', 'goftino_code') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
