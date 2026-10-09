@extends('admin.layout')

@section('title', 'نوشته در حال ویرایش است')

@section('content')
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 space-y-4" role="alert">
        <h1 class="font-bold text-slate-800">{{ $post->title }}</h1>
        <p class="text-sm leading-7 text-amber-900">{{ $message }}</p>
        <p class="text-sm leading-7 text-slate-600">اگر صفحه ویرایش بسته شده یا ارتباط آن قطع شده باشد، نوشته حداکثر پس از یک دقیقه آزاد می‌شود.</p>
        <div class="flex flex-wrap gap-4 text-sm font-bold">
            <a href="{{ route('admin.posts.edit', $post) }}" class="text-brand-600">تلاش دوباره</a>
            <a href="{{ route('admin.posts.index') }}" class="text-slate-600">بازگشت به فهرست نوشته‌ها</a>
        </div>
    </div>
@endsection
