@extends('admin.layout')

@section('title', 'تنظیمات صفحه VIP')

@section('content')
    <x-admin.form action="{{ route('admin.vip-plans.page-settings.update') }}" method="put">
        <x-admin.page>
            <x-admin.form.validation-errors class="mb-6"/>

            <div class="mb-5 flex justify-end gap-3">
                <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.index') }}" variant="secondary">بازگشت به پلن‌ها</x-admin.form.button>
                <x-admin.form.button tag="a" href="{{ route('vip.index') }}" target="_blank" variant="secondary">مشاهده صفحه VIP</x-admin.form.button>
            </div>

            <x-admin.metabox title="متن‌های Hero صفحه VIP">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-admin.field required label="متن نشان بالای عنوان" :error="$errors->first('hero_badge')">
                        <x-admin.form.input name="hero_badge" :value="old('hero_badge', $hero['hero_badge'])" required/>
                    </x-admin.field>
                    <x-admin.field required label="بخش اول عنوان" :error="$errors->first('hero_title')">
                        <x-admin.form.input name="hero_title" :value="old('hero_title', $hero['hero_title'])" required/>
                    </x-admin.field>
                    <x-admin.field required label="بخش رنگی عنوان" :error="$errors->first('hero_highlight')">
                        <x-admin.form.input name="hero_highlight" :value="old('hero_highlight', $hero['hero_highlight'])" required/>
                    </x-admin.field>
                    <x-admin.field required label="متن دکمه" :error="$errors->first('hero_button')">
                        <x-admin.form.input name="hero_button" :value="old('hero_button', $hero['hero_button'])" required/>
                    </x-admin.field>
                    <div class="md:col-span-2">
                        <x-admin.field required label="توضیح زیر عنوان" :error="$errors->first('hero_description')">
                            <x-admin.form.textarea name="hero_description" rows="4" :value="old('hero_description', $hero['hero_description'])" required/>
                        </x-admin.field>
                    </div>
                    @for($number = 1; $number <= 3; $number++)
                        @php($key = 'hero_benefit_'.$number)
                        <x-admin.field required label="مزیت {{ $number }}" :error="$errors->first($key)">
                            <x-admin.form.input :name="$key" :value="old($key, $hero[$key])" required/>
                        </x-admin.field>
                    @endfor
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="پلن ویژه در لیست">
                <x-admin.field label="پلن ویژه" hint="انتخاب این گزینه اختیاری است. تا زمانی که پلنی انتخاب نکنید، هیچ پلنی برجسته نمی‌شود." :error="$errors->first('featured_plan_id')">
                    <x-admin.form.select name="featured_plan_id" :options="['' => 'بدون پلن ویژه'] + $plans->pluck('title', 'id')->all()" :value="old('featured_plan_id', $featuredPlanId)"/>
                </x-admin.field>
            </x-admin.metabox>

            <x-admin.form.save-button label="ذخیره تنظیمات صفحه VIP"/>
        </x-admin.page>
    </x-admin.form>
@endsection
