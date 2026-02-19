@extends('admin.layout')

@section('title', 'داشبورد')

@section('content')
    <div class="flex min-h-[100vh]">
        <main class="flex-1 md:py-[20px] md:px-[26px] py-[16px] px-[14px]">
            <div
                class="flex items-center justify-between gap-4 mb-[18px] flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[var(--text-main)] mb-[0.4rem]">داشبورد ادمین</h1>
                    <div class="m-0 text-[0.9rem] text-[var(--text-muted)]">
                        به سامانه مودینگ وی خوش آمدید!
                    </div>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center justify-center gap-[6px] px-[14px] py-[7px] rounded-full text-[0.86rem] font-medium border border-transparent cursor-pointer no-underline transition-transform duration-75 ease-out bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">داشبورد</a>
                </div>
            </div>
        </main>
    </div>
@endsection
