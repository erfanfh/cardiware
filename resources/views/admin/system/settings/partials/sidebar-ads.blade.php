<div class="space-y-6">
    @for($i = 1; $i <= 2; $i++)
        <x-admin.metabox title="تبلیغات سایدبار {{ $i }}" :collapsible="false">
            <div class="space-y-4">
                <x-admin.field label="نمایش کادر" class="switch-field">
                    <x-admin.form.switch
                        name="ads_{{ $i }}_display_background"
                        :checked="old('ads_'.$i.'_display_background', get_setting('sidebarWidgets', 'ads_'.$i.'_display_background'))"
                    />
                </x-admin.field>

                <x-admin.field label="محتوای تبلیغ">
                    <x-admin.form.editor
                        name="ads_{{ $i }}_body"
                        :value="old('ads_'.$i.'_body', get_setting('sidebarWidgets', 'ads_'.$i.'_body'))"
                        preset="full"
                        height="420"
                        :toolbar-sticky="false"
                        placeholder="محتوای تبلیغ را وارد کنید..."
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>
    @endfor
</div>
