<x-admin.form method="get" action="{{ route('admin.polls.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی نظرسنجی‌ها">
            <x-slot:actions>
                <x-admin.form.button tag="a" href="{{ route('admin.polls.create') }}" variant="solid">
                    + افزودن نظرسنجی جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input name="id" :value="request('id')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان، سؤال یا اسلاگ" span="col-span-12 md:col-span-6">
                    <x-admin.form.input name="search" :value="request('search')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت انتشار">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="شرکت‌کنندگان مجاز">
                    <x-admin.form.select
                        name="audience"
                        :options="['' => 'همه مخاطبان'] + $audiences"
                        :value="request('audience')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نمایش نتیجه">
                    <x-admin.form.select
                        name="results_visibility"
                        :options="['' => 'همه حالت‌ها'] + $resultsVisibilities"
                        :value="request('results_visibility')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت زمانی">
                    <x-admin.form.select
                        name="timing"
                        :options="['' => 'همه زمان‌بندی‌ها'] + $timings"
                        :value="request('timing')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="زمان شروع" span="col-span-12 md:col-span-6 xl:col-span-4">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="starts_at_from"
                            :value="request('starts_at_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="starts_at_to"
                            :value="request('starts_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="زمان پایان" span="col-span-12 md:col-span-6 xl:col-span-4">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="ends_at_from"
                            :value="request('ends_at_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="ends_at_to"
                            :value="request('ends_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ ایجاد" span="col-span-12 md:col-span-6 xl:col-span-4">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="created_at_from"
                            :value="request('created_at_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="created_at_to"
                            :value="request('created_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">
                    جستجو
                </x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.polls.index') }}" variant="secondary">
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
