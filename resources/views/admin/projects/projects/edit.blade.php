@extends('admin.layout')

@section('title', 'ویرایش پروژه')

@section('content')
    <form method="POST" action="{{ route('admin.projects.update', $project) }}">
        @csrf
        @method('PUT')

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
        ])
    </form>
@endsection
