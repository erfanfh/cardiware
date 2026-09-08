<div class="space-y-6">
    @for($i = 1;$i <= 5;$i++)
        <x-admin.metabox title="باکس تبلیغ {{ $i }}" :collapsible="false">
            <div class="grid grid-cols-1 gap-4 space-y-4">
                <x-admin.field label="نمایش بک گراند" class="switch-field">
                    <x-admin.form.switch
                        name="ads_1_display_background"
                        :checked="old('ads_'.$i.'_display_background', get_setting('home', 'ads_'.$i.'_display_background'))"
                    />
                </x-admin.field>

                <x-admin.field>
                    <x-admin.form.editor
                        name="ads_{{ $i }}_body"
                        :value="old('ads_'.$i.'_body', get_setting('home', 'ads_'.$i.'_body'))"
                        preset="full"
                        height="500"
                        :toolbar-sticky="false"
                        placeholder="متن نوشته ..."
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>
    @endfor
</div>
