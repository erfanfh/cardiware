@extends('admin.layout')
@section('title', 'ویرایش ویدیو')
@section('content')
    <div data-edit-lock-status class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-7" role="status" aria-live="polite">
        ویرایش این ویدیو در اختیار شماست. تا زمانی که این صفحه باز است، مدیر دیگری نمی‌تواند آن را ویرایش کند.
    </div>
    <x-admin.form action="{{ route('admin.videos.update', $video) }}" method="put"
                  data-edit-lock
                  data-renew-url="{{ route('admin.videos.edit-lock.renew', $video) }}"
                  data-release-url="{{ route('admin.videos.edit-lock.release', $video) }}">
        <input type="hidden" name="edit_lock_token" value="{{ $editLockToken }}">
        @include('admin.video.videos.partials.form-fields', ['video' => $video])
        <x-admin.form.save-button label="ذخیره تغییرات"/>
    </x-admin.form>
@endsection
