<div class="space-y-6">
    <div
        x-data="{
            seoTitle: @js(old('product_list_seo_title', get_setting('seo', 'product_list_seo_title'))),
            seoDescription: @js(old('product_list_seo_description', get_setting('seo', 'product_list_seo_description'))),

            insertAtCursor(field, value) {
                const el = this.$refs[field];
                if (!el) return;

                const start = el.selectionStart;
                const end = el.selectionEnd;

                const text = el.value;
                el.value = text.substring(0, start) + value + text.substring(end);

                el.dispatchEvent(new Event('input'));

                this.$nextTick(() => {
                    el.focus();
                    el.setSelectionRange(start + value.length, start + value.length);
                });
            }
        }"
        class="space-y-6"
    >
        <x-admin.metabox title="تنظیمات" :collapsible="false">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
                <x-admin.field
                    label="الگوی عنوان سئو"
                >
                    <x-admin.form.input
                        x-ref="title"
                        name="product_list_seo_title"
                        x-model="seoTitle"
                    />

                    <div class="flex flex-wrap gap-2">
                        @foreach($seoVariables as $variable)
                            <button
                                type="button"
                                @click="insertAtCursor('title', '{{ $variable['token'] }}')"
                                class="cursor-pointer px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white hover:border-primary-300 hover:text-primary-600 transition"
                            >
                                {{ $variable['label'] }}
                            </button>
                        @endforeach
                    </div>
                </x-admin.field>

                <x-admin.field
                    label="الگوی توضیح متا"
                >
                    <x-admin.form.textarea
                        x-ref="description"
                        name="product_list_seo_description"
                        rows="4"
                        x-model="seoDescription"
                    />

                    <div class="flex flex-wrap gap-2">
                        @foreach($seoVariables as $variable)
                            <button
                                type="button"
                                @click="insertAtCursor('description', '{{ $variable['token'] }}')"
                                class="cursor-pointer px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white hover:border-primary-300 hover:text-primary-600 transition"
                            >
                                {{ $variable['label'] }}
                            </button>
                        @endforeach
                    </div>
                </x-admin.field>
            </div>
        </x-admin.metabox>
    </div>
</div>
