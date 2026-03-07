@php
    $isEdit = isset($project) && $project;
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار">
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">وضعیت</span>
                    <span class="font-extrabold text-slate-800">
                            {{ $isEdit ? ($project->status ?? '—') : 'پیش‌نویس' }}
                        </span>
                </div>

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12">
                        <x-admin.field label="status">
                            <x-admin.select name="status">
                                @php $st = old('status', $project->status ?? 'active'); @endphp
                                <option value="active" @selected($st==='active')>فعال (در حال توسعه)</option>
                                <option value="completed" @selected($st==='completed')>تکمیل شده</option>
                                <option value="paused" @selected($st==='paused')>متوقف / در انتظار</option>
                            </x-admin.select>
                        </x-admin.field>
                    </div>

                    <div class="col-span-12">
                        <x-admin.field label="released_at">
                            <x-admin.form.input type="datetime-local" name="released_at"
                                                value="{{ old('released_at', optional($project->released_at ?? null)?->format('Y-m-d\TH:i')) }}"/>
                        </x-admin.field>
                    </div>

                    <div class="col-span-12">
                        <x-admin.field label="آخرین بروزرسانی (last_updated_at)">
                            <x-admin.form.input type="datetime-local" name="last_updated_at"
                                                value="{{ old('last_updated_at', optional($project->last_updated_at ?? null)?->format('Y-m-d\TH:i')) }}"/>
                        </x-admin.field>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex items-center gap-2">
                    <x-admin.button type="submit" variant="solid" class="flex-1">
                        {{ $isEdit ? 'ذخیره' : 'انتشار' }}
                    </x-admin.button>
                    <x-admin.button type="button" variant="soft" class="flex-1">
                        ذخیره پیش‌نویس
                    </x-admin.button>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="پیشرفت پروژه">
            <div class="space-y-3">
                <x-admin.field label="progress_percent" hint="۰ تا ۱۰۰">
                    <div class="flex items-center gap-3">
                        <x-admin.form.input type="number" min="0" max="100" name="progress_percent"
                                            value="{{ old('progress_percent', $project->progress_percent ?? 0) }}"
                                            class="w-28"/>
                        <div class="flex-1">
                            @php $pp = (int) old('progress_percent', $project->progress_percent ?? 0); @endphp
                            <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full bg-orange-500" style="width: {{ max(0, min(100, $pp)) }}%"></div>
                            </div>
                            <div class="mt-1 text-[11px] text-slate-500">{{ $pp }}%</div>
                        </div>
                    </div>
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="اطلاعات نسخه و سازنده">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12">
                    <x-admin.field label="current_version" hint="مثلاً v0.9.3">
                        <x-admin.form.input name="current_version"
                                            value="{{ old('current_version', $project->current_version ?? '') }}"
                                            placeholder="v0.0.0"/>
                    </x-admin.field>
                </div>

                <div class="col-span-12">
                    <x-admin.field label="creator_name" hint="مثلاً ModdingWay Team">
                        <x-admin.form.input name="creator_name"
                                            value="{{ old('creator_name', $project->creator_name ?? '') }}"
                                            placeholder="نام تیم/سازنده"/>
                    </x-admin.field>
                </div>

                <div class="col-span-12">
                    <x-admin.field label="platform" hint="مثلاً PC (Windows)">
                        <x-admin.form.input name="platform" value="{{ old('platform', $project->platform ?? '') }}"
                                            placeholder="Platform"/>
                    </x-admin.field>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="عملیات">
            <div class="flex flex-wrap gap-2">
                <x-admin.button type="button" variant="ghost">مشاهده</x-admin.button>
                <x-admin.button type="button" variant="ghost">تکثیر</x-admin.button>
                <x-admin.button type="button" variant="danger">حذف</x-admin.button>
            </div>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$project->title ?? ''"
        :slugValue="$project->slug ?? ''"
        basePath="{{ route('home') }}/projects/"
        :checkUrl="route('admin.projects.check-slug')"
        :currentId="$project->id ?? null"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field required>
            <x-admin.form.textarea name="body" rows="10"
                                   placeholder="توضیحات کامل پروژه ...">{{ old('body', $project->body ?? '') }}</x-admin.form.textarea>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.field required
                       hint="این فیلد اختیاری است. درصورتی که تکمیل نشود، خلاصه توضیحات پروژه در نظر گرفته خواهد شد.">
            <x-admin.form.textarea name="excerpt" rows="4"
                                   placeholder="یک خلاصه کوتاه از پروژه ...">{{ old('excerpt', $project->excerpt ?? '') }}</x-admin.form.textarea>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="نقشه راه">
        <x-admin.project.roadmap-repeater
            name="roadmap"
            :items="old('roadmap', $roadmapItems ?? [])"
        />
    </x-admin.metabox>

    <x-admin.metabox title="آپدیت‌ها و تغییرات">
        <x-admin.project.changelog-repeater
            name="changelogs"
            :items="old('changelogs', $changelogs ?? [])"
        />
    </x-admin.metabox>
</x-admin.page>
