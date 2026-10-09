@extends('admin.layout')

@section('title', 'ویرایش نوشته')

@section('content')
    <div data-edit-lock-status class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-7" role="status" aria-live="polite">
        ویرایش این نوشته در اختیار شماست. تا زمانی که این صفحه باز است، مدیر دیگری نمی‌تواند آن را ویرایش کند.
    </div>
    <x-admin.form action="{{ route('admin.posts.update', $post) }}" method="put"
                  data-edit-lock
                  data-renew-url="{{ route('admin.posts.edit-lock.renew', $post) }}"
                  data-release-url="{{ route('admin.posts.edit-lock.release', $post) }}">
        <input type="hidden" name="edit_lock_token" value="{{ $editLockToken }}">
        @include('admin.content.posts.partials.form-fields', [
            'post' => $post,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
