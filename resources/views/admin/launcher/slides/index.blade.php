@extends('admin.layout')

@section('title', 'مدیریت اسلایدر لانچر')

@section('content')
    @php($formSlide = $editingSlide)
    @php($targetType = old('target_type', $formSlide?->target_type ?? 'product'))

    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h1 class="text-base font-black text-slate-900">اسلایدر صفحه اصلی MW Launcher</h1>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    اسلایدها به ترتیب عددی نمایش داده می‌شوند و هر هفت ثانیه تغییر می‌کنند. تصویر پیشنهادی ۱۵۶۰×۲۴۰ پیکسل است.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.launcher-slides.store') }}" class="p-5"
                  x-data="{ targetType: @js($targetType) }">
                @csrf
                <input type="hidden" name="id" value="{{ old('id', $formSlide?->id) }}">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-8">
                        <x-admin.field label="عنوان اسلاید">
                            <x-admin.form.input name="title" required maxlength="255"
                                                value="{{ old('title', $formSlide?->title) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="متن دکمه">
                            <x-admin.form.input name="button_text" required maxlength="80"
                                                value="{{ old('button_text', $formSlide?->button_text ?? 'مشاهده') }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="توضیح کوتاه" hint="یک خط کوتاه؛ حداکثر ۵۰۰ کاراکتر">
                            <x-admin.form.textarea name="description" rows="2"
                                                   :value="old('description', $formSlide?->description)" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-12">
                        <x-admin.field label="تصویر اسلاید">
                            <x-admin.form.media-picker
                                mode="field"
                                name="image"
                                layout="vertical"
                                preview-height="h-48"
                                :value="[
                                    'id' => old('image.id', $formSlide?->image_media_id),
                                    'path' => old('image.path', $formSlide?->image?->url),
                                ]"
                            />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-4">
                        <x-admin.field label="نوع مقصد">
                            <select name="target_type" x-model="targetType"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm">
                                <option value="product">صفحه محصول در لانچر</option>
                                <option value="url">لینک سایت</option>
                                <option value="none">بدون لینک و دکمه</option>
                            </select>
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-8" x-show="targetType === 'product'" x-cloak>
                        <x-admin.field label="محصول مقصد">
                            <select name="target_sale_item_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm">
                                <option value="">انتخاب محصول…</option>
                                @foreach($saleItems as $saleItem)
                                    <option value="{{ $saleItem->id }}"
                                            @selected((string) old('target_sale_item_id', $formSlide?->target_sale_item_id) === (string) $saleItem->id)>
                                        {{ $saleItem->launcher_title ?: ($saleItem->title ?: $saleItem->product?->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-8" x-show="targetType === 'url'" x-cloak>
                        <x-admin.field label="لینک HTTPS سایت">
                            <x-admin.form.input name="target_url" type="url" dir="ltr"
                                                placeholder="https://example.com/product"
                                                value="{{ old('target_url', $formSlide?->target_url) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="ترتیب نمایش">
                            <x-admin.form.input name="sort_order" type="number" min="0" max="100000"
                                                value="{{ old('sort_order', $formSlide?->sort_order ?? 0) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="شروع نمایش" hint="اختیاری">
                            <x-admin.form.input name="starts_at" type="datetime-local" dir="ltr"
                                                value="{{ old('starts_at', $formSlide?->starts_at?->format('Y-m-d\\TH:i')) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <x-admin.field label="پایان نمایش" hint="اختیاری">
                            <x-admin.form.input name="ends_at" type="datetime-local" dir="ltr"
                                                value="{{ old('ends_at', $formSlide?->ends_at?->format('Y-m-d\\TH:i')) }}" />
                        </x-admin.field>
                    </div>
                    <div class="md:col-span-3">
                        <label class="flex h-full cursor-pointer items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <span class="text-sm font-bold text-emerald-800">نمایش در لانچر</span>
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="h-5 w-5"
                                   @checked(old('is_enabled', $formSlide?->is_enabled ?? false))>
                        </label>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <x-admin.form.button type="submit" variant="dark" size="lg">
                        {{ $formSlide ? 'ذخیره تغییرات اسلاید' : 'ثبت اسلاید جدید' }}
                    </x-admin.form.button>
                    @if($formSlide)
                        <a href="{{ route('admin.launcher-slides.index') }}"
                           class="inline-flex items-center rounded-xl border border-slate-300 px-5 text-sm font-bold text-slate-700">
                            انصراف
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 text-sm font-black text-slate-900">اسلایدهای ثبت‌شده</div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th class="px-4 py-3">تصویر</th><th class="px-4 py-3">عنوان</th>
                        <th class="px-4 py-3">مقصد</th><th class="px-4 py-3">ترتیب</th>
                        <th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($slides as $slide)
                        <tr>
                            <td class="px-4 py-3">
                                <img src="{{ $slide->image?->url }}" alt="" class="h-14 w-28 rounded-lg object-cover">
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-black text-slate-900">{{ $slide->title }}</div>
                                <div class="mt-1 max-w-sm truncate text-xs text-slate-500">{{ $slide->description }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($slide->target_type === 'product')
                                    محصول #{{ $slide->target_sale_item_id }}
                                @elseif($slide->target_type === 'url')
                                    <span dir="ltr" class="block max-w-xs truncate">{{ $slide->target_url }}</span>
                                @else
                                    بدون لینک
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $slide->sort_order }}</td>
                            <td class="px-4 py-3 {{ $slide->is_enabled ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $slide->is_enabled ? 'فعال' : 'غیرفعال' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.launcher-slides.index', ['edit' => $slide->id]) }}"
                                       class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.launcher-slides.destroy', $slide) }}"
                                          onsubmit="return confirm('این اسلاید حذف شود؟')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">هنوز اسلایدی ثبت نشده است.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
