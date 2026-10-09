@extends('admin.layout')

@section('title', 'ویرایش پروژه')

@section('content')
    <div data-edit-lock-status class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-7" role="status" aria-live="polite">
        ویرایش این پروژه در اختیار شماست. تا زمانی که این صفحه باز است، مدیر دیگری نمی‌تواند آن را ویرایش کند.
    </div>
    <x-admin.form action="{{ route('admin.projects.update', $project) }}" method="put"
                  data-edit-lock
                  data-renew-url="{{ route('admin.projects.edit-lock.renew', $project) }}"
                  data-release-url="{{ route('admin.projects.edit-lock.release', $project) }}">
        <input type="hidden" name="edit_lock_token" value="{{ $editLockToken }}">
        @include('admin.projects.projects.partials.form-fields', [
            'project' => $project,
            'roadmapItems' => $project->roadmapItems
                ->sortBy('sort_order')
                ->values()
                ->map(fn($i) => [
                    'title' => $i->title,
                    'status' => $i->status,
                    'progress_percent' => $i->progress_percent,
                    'excerpt' => $i->excerpt,
                    'sort_order' => $i->sort_order,
                ])->all(),
            'changelogs' => $project->changelogs
                ->sortBy('sort_order')
                ->values()
                ->map(fn($c) => [
                    'title' => $c->title,
                    'body' => $c->body,
                    'published_at' => optional($c->published_at)?->format('Y-m-d\TH:i'),
                    'sort_order' => $c->sort_order,
                ])->all(),
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
