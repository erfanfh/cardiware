@php
    $isEdit = $level->exists;
    $colors = ['brand' => 'نارنجی برند', 'slate' => 'خاکستری', 'emerald' => 'سبز', 'cyan' => 'فیروزه‌ای', 'amber' => 'کهربایی', 'violet' => 'بنفش', 'rose' => 'سرخابی'];
@endphp

@extends('admin.layout')

@section('title', $isEdit ? 'ویرایش سطح' : 'ایجاد سطح جدید')

@section('content')
    <x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form action="{{ $isEdit ? route('admin.loyalty-levels.update', $level) : route('admin.loyalty-levels.store') }}"
                  :method="$isEdit ? 'put' : 'post'">
        @if($isEdit)
            @method('PUT')
        @endif

        <x-admin.metabox title="مشخصات سطح">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field label="عنوان سطح" required :error="$errors->first('title')">
                    <x-admin.form.input type="text" name="title" :value="old('title', $level->title)"/>
                </x-admin.field>

                <x-admin.field label="نشان (ایموجی)" hint="مثال: 🌱 ⭐ 🏆 👑 💎">
                    <x-admin.form.input type="text" name="badge" :value="old('badge', $level->badge)"/>
                </x-admin.field>

                <x-admin.field label="حداقل XP" hint="کل امتیاز کسب‌شده لازم برای رسیدن به این سطح." required
                               :error="$errors->first('min_xp')">
                    <x-admin.form.input type="number" min="0" step="1" name="min_xp"
                                        :value="old('min_xp', $level->min_xp)"/>
                </x-admin.field>

                <x-admin.field label="پاداش رسیدن (امتیاز)" hint="یک‌بار هنگام رسیدن به سطح پرداخت می‌شود؛ XP نمی‌دهد."
                               required :error="$errors->first('reward_points')">
                    <x-admin.form.input type="number" min="0" step="1" name="reward_points"
                                        :value="old('reward_points', $level->reward_points)"/>
                </x-admin.field>

                <x-admin.field label="ضریب امتیاز خرید"
                               hint="امتیاز خرید کاربر در این عدد ضرب می‌شود. مثال: 1.15 یعنی ۱۵٪ امتیاز بیشتر. پیشنهاد: حداکثر 1.5"
                               required :error="$errors->first('purchase_multiplier')">
                    <x-admin.form.input type="number" min="1" max="5" step="0.05" name="purchase_multiplier"
                                        :value="old('purchase_multiplier', $level->purchase_multiplier)"/>
                </x-admin.field>

                <x-admin.field label="رنگ" required :error="$errors->first('color_key')">
                    <x-admin.form.select name="color_key" :value="old('color_key', $level->color_key)"
                                         :options="$colors"/>
                </x-admin.field>

                <x-admin.field label="ترتیب" required :error="$errors->first('sort_order')">
                    <x-admin.form.input type="number" min="0" step="1" name="sort_order"
                                        :value="old('sort_order', $level->sort_order)"/>
                </x-admin.field>

                <div class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">فعال بودن سطح</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                سطح غیرفعال از نردبان سطوح باشگاه حذف می‌شود؛ کاربرانِ در آن محدوده به سطح فعال قبلی برمی‌گردند.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="is_active"
                            :checked="old('is_active', $level->is_active ?? true)"
                        />
                    </label>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.form.save-button :label="$isEdit ? 'ذخیره تغییرات' : 'ایجاد سطح'"/>
    </x-admin.form>
    </x-admin.page>
@endsection
