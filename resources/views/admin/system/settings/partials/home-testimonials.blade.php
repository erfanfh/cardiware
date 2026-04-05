<div class="space-y-6">
    <x-admin.metabox title="تجربه کاربران" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان باکس">
                <x-admin.form.input
                    name="testimonials_title"
                    :value="old('testimonials_title', get_setting('home', 'testimonials_title'))"
                />
            </x-admin.field>

            <x-admin.field label="میانگین رضایت کاربران (0 تا 5)">
                <x-admin.form.input
                    type="number"
                    min="0"
                    max="5"
                    step="any"
                    name="testimonials_users_avg_scores"
                    :value="old('testimonials_users_avg_scores', get_setting('home', 'testimonials_users_avg_scores'))"
                />
            </x-admin.field>
        </div>

        <x-admin.field>
            <x-admin.form.repeater
                :items="get_setting('home', 'testimonials_items')"
                name="testimonials_items"
                title="آیتم ها"
                addText="افزودن آیتم"
                :defaultItem="[
                    'title' => '',
                    'subtitle' => '',
                    'score' => '',
                    'text' => '',
                    'tags' => '',
                    'special_experience' => null,
                    'image_id' => null,
                    'image_path' => null,
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-admin.field label="تصویر">
                            <x-admin.form.media-picker
                                mode="repeater"
                                name="image"
                            />
                        </x-admin.field>
                    </div>

                    <div class="flex flex-col gap-4">
                        <x-admin.field label="عنوان">
                            <x-admin.form.input x-model="it.title"
                                                x-bind:name="field(i, 'title')"/>
                        </x-admin.field>

                        <x-admin.field label="زیرعنوان">
                            <x-admin.form.input x-model="it.subtitle"
                                                x-bind:name="field(i, 'subtitle')"/>
                        </x-admin.field>

                        <x-admin.field label="امتیاز (0 تا 5)">
                            <x-admin.form.input x-model="it.score"
                                                x-bind:name="field(i, 'score')"/>
                        </x-admin.field>
                    </div>

                    <x-admin.field label="متن">
                        <x-admin.form.textarea rows="2" x-model="it.text"
                                               x-bind:name="field(i, 'text')"/>
                    </x-admin.field>

                    <x-admin.field label="برچسب ها" hint="با کاما از هم جدا کنید.">
                        <x-admin.form.textarea rows="2" x-model="it.tags"
                                               x-bind:name="field(i, 'tags')"/>
                    </x-admin.field>

                    <x-admin.field label="تجربه ویژه" class="switch-field">
                        <x-admin.form.switch
                            x-model="it.special_experience"
                            x-bind:name="field(i, 'special_experience')"
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
