@extends('admin.layout')

@section('title', 'ویرایش پروژه')

@section('content')
    <x-admin.form action="{{ route('admin.projects.update', $project) }}" method="put">
        @include('admin.projects.projects.form', [
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
            'ads' => $project->ads
                ->sortBy('sort_order')
                ->values()
                ->map(fn($a) => [
                    'title' => $a->title,
                    'link' => $a->link,
                    'sort_order' => $a->sort_order,
                ])->all(),
        ])

        <x-admin.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
