<x-admin.form.repeater
    :items="$items"
    name="roadmap"
    title="آیتم‌های نقشه راه"
    addText="افزودن آیتم"
    emptyText="هنوز آیتمی اضافه نشده."
    :defaultItem="[
        'title' => '',
        'status' => 'in_progress',
        'progress_percent' => 0,
        'excerpt' => '',
    ]"
>
    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 lg:col-span-6">
            <x-admin.field label="عنوان" required>
                <x-admin.form.input
                    x-model="it.title"
                    x-bind:name="field(i, 'title')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 sm:col-span-6 lg:col-span-3">
            <x-admin.field label="وضعیت">
                <x-admin.select x-model="it.status" x-bind:name="field(i, 'status')">
                    <option value="planned">برنامه‌ریزی</option>
                    <option value="in_progress">در حال انجام</option>
                    <option value="done">انجام شده</option>
                </x-admin.select>
            </x-admin.field>
        </div>

        <div class="col-span-12 sm:col-span-6 lg:col-span-3">
            <x-admin.field label="درصد پیشرفت">
                <x-admin.form.input type="number" min="0" max="100"
                                    x-model.number="it.progress_percent"
                                    x-bind:name="field(i, 'progress_percent')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12">
            <x-admin.field label="excerpt">
                <x-admin.form.textarea rows="2"
                                       x-model="it.excerpt"
                                       x-bind:name="field(i, 'excerpt')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
