@php
    use App\Domain\Project\Enums\RoadmapItemStatusEnum;$statuses = [];
    foreach(RoadmapItemStatusEnum::cases() as $item){
        $statuses[$item->value] = $item->label();
    }
@endphp

<x-admin.form.repeater
    :items="$items"
    name="roadmap_items"
    title="آیتم‌های نقشه راه"
    :defaultItem="[
        'id' => null,
        'title' => '',
        'status' => 'in_progress',
        'progress_percent' => 0,
        'excerpt' => '',
    ]"
>
    <input type="hidden" x-bind:name="field(i, 'id')" x-model="it.id">

    <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 lg:col-span-6">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    x-model="it.title"
                    x-bind:name="field(i, 'title')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 sm:col-span-6 lg:col-span-3">
            <x-admin.field label="وضعیت">
                <x-admin.form.select
                    x-model="it.status"
                    x-bind:name="field(i, 'status')"
                    :options="$statuses"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 sm:col-span-6 lg:col-span-3">
            <x-admin.field label="درصد پیشرفت (0 تا 100)">
                <x-admin.form.input type="number" min="0" max="100"
                                    x-model.number="it.progress_percent"
                                    x-bind:name="field(i, 'progress_percent')"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12">
            <x-admin.field label="توضیحات خلاصه">
                <x-admin.form.textarea rows="2"
                                       x-model="it.excerpt"
                                       x-bind:name="field(i, 'excerpt')"
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>
