<div class="flex flex-col gap-4">
    @forelse(\App\Domain\Catalog\Models\ProductInformationField::all() as $informationField)
        <x-admin.field
            class="col-span-12"
            label="{{ $informationField->title }}"
        >
            <x-admin.form.input
                name="information_fields[{{ $informationField->id }}]"
                :value="old('information_fields.' . $informationField->id, $product ? $product->informationEntries->where('product_information_field_id', $informationField->id)->first()?->value : '')"
            />
        </x-admin.field>
    @empty
        <div class="text-center text-xs">
            هیج فیلدی یافت نشد!
        </div>
    @endforelse
</div>

<a href="{{ route('admin.products.information.index') }}" target="_blank"
   class="block font-bold text-brand-600 text-xs mt-4">
    برای مدیریت فیلد های اطلاعات، کلیک کنید.
</a>
