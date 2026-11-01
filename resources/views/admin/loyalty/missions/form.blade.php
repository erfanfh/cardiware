@php
    $isEdit = $mission->exists;
    $metrics = collect(\App\Domain\Loyalty\Enums\MissionMetricEnum::cases())
        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
        ->all();
@endphp

@extends('admin.layout')

@section('title', $isEdit ? 'ویرایش مأموریت' : 'ایجاد مأموریت جدید')

@section('content')
    <x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form action="{{ $isEdit ? route('admin.loyalty-missions.update', $mission) : route('admin.loyalty-missions.store') }}"
                  :method="$isEdit ? 'put' : 'post'">
        @if($isEdit)
            @method('PUT')
        @endif

        <x-admin.metabox title="مشخصات مأموریت">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field label="کد (یکتا)" hint="مثال: weekly_visits_3" required :error="$errors->first('code')">
                    <x-admin.form.input type="text" name="code" :value="old('code', $mission->code)"/>
                </x-admin.field>

                <x-admin.field label="عنوان" required :error="$errors->first('title')">
                    <x-admin.form.input type="text" name="title" :value="old('title', $mission->title)"/>
                </x-admin.field>

                <x-admin.field label="توضیح">
                    <x-admin.form.input type="text" name="description" :value="old('description', $mission->description)"/>
                </x-admin.field>

                <x-admin.field label="آیکن (ایموجی)">
                    <x-admin.form.input type="text" name="icon" :value="old('icon', $mission->icon)"/>
                </x-admin.field>

                <x-admin.field label="معیار پیشرفت" required :error="$errors->first('metric')">
                    <x-admin.form.select name="metric" :value="old('metric', $mission->metric?->value)" :options="$metrics"/>
                </x-admin.field>

                <x-admin.field label="هدف (تعداد)" required :error="$errors->first('target_count')">
                    <x-admin.form.input type="number" min="1" step="1" name="target_count"
                                        :value="old('target_count', $mission->target_count)"/>
                </x-admin.field>

                <x-admin.field label="جایزه (امتیاز)" required :error="$errors->first('reward_points')">
                    <x-admin.form.input type="number" min="0" step="1" name="reward_points"
                                        :value="old('reward_points', $mission->reward_points)"/>
                </x-admin.field>

                <x-admin.field label="ترتیب" required :error="$errors->first('sort_order')">
                    <x-admin.form.input type="number" min="0" step="1" name="sort_order"
                                        :value="old('sort_order', $mission->sort_order)"/>
                </x-admin.field>

                <div class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">فعال بودن مأموریت</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                مأموریت غیرفعال در باشگاه کاربران نمایش داده نمی‌شود و پیشرفت آن ثبت نمی‌گردد.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="is_active"
                            :checked="old('is_active', $mission->is_active ?? true)"
                        />
                    </label>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.form.save-button :label="$isEdit ? 'ذخیره تغییرات' : 'ایجاد مأموریت'"/>
    </x-admin.form>
    </x-admin.page>
@endsection
