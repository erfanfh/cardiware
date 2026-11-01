@php
    $isEdit = $achievement->exists;
    $metrics = collect(\App\Domain\Loyalty\Enums\AchievementMetricEnum::cases())
        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
        ->all();
@endphp

@extends('admin.layout')

@section('title', $isEdit ? 'ویرایش نشان' : 'ایجاد نشان جدید')

@section('content')
    <x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form action="{{ $isEdit ? route('admin.loyalty-achievements.update', $achievement) : route('admin.loyalty-achievements.store') }}"
                  :method="$isEdit ? 'put' : 'post'">
        @if($isEdit)
            @method('PUT')
        @endif

        <x-admin.metabox title="مشخصات نشان">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field label="کد (یکتا)" hint="مثال: first_purchase" required :error="$errors->first('code')">
                    <x-admin.form.input type="text" name="code" :value="old('code', $achievement->code)"/>
                </x-admin.field>

                <x-admin.field label="عنوان" required :error="$errors->first('title')">
                    <x-admin.form.input type="text" name="title" :value="old('title', $achievement->title)"/>
                </x-admin.field>

                <x-admin.field label="توضیح">
                    <x-admin.form.input type="text" name="description" :value="old('description', $achievement->description)"/>
                </x-admin.field>

                <x-admin.field label="نشان (ایموجی)">
                    <x-admin.form.input type="text" name="badge" :value="old('badge', $achievement->badge)"/>
                </x-admin.field>

                <x-admin.field label="معیار" required :error="$errors->first('metric')">
                    <x-admin.form.select name="metric" :value="old('metric', $achievement->metric?->value)" :options="$metrics"/>
                </x-admin.field>

                <x-admin.field label="آستانه" hint="مقدار معیار برای باز شدن نشان؛ برای مبلغ‌ها به تومان." required
                               :error="$errors->first('threshold')">
                    <x-admin.form.input type="number" min="1" step="1" name="threshold"
                                        :value="old('threshold', $achievement->threshold)"/>
                </x-admin.field>

                <x-admin.field label="پاداش (امتیاز)" hint="صفر = نشان افتخاری بدون پاداش. پاداش نشان XP نمی‌دهد."
                               required :error="$errors->first('reward_points')">
                    <x-admin.form.input type="number" min="0" step="1" name="reward_points"
                                        :value="old('reward_points', $achievement->reward_points)"/>
                </x-admin.field>

                <x-admin.field label="ترتیب" required :error="$errors->first('sort_order')">
                    <x-admin.form.input type="number" min="0" step="1" name="sort_order"
                                        :value="old('sort_order', $achievement->sort_order)"/>
                </x-admin.field>

                <div class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">فعال بودن نشان</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                نشان غیرفعال در باشگاه کاربران نمایش داده نمی‌شود و به‌صورت خودکار اهدا نمی‌گردد.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="is_active"
                            :checked="old('is_active', $achievement->is_active ?? true)"
                        />
                    </label>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.form.save-button :label="$isEdit ? 'ذخیره تغییرات' : 'ایجاد نشان'"/>
    </x-admin.form>
    </x-admin.page>
@endsection
