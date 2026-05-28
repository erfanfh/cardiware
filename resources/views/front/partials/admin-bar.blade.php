@can('admin.display')
    @php
        $user = auth()->user();

        $quickActions = [];

        if(\Illuminate\Support\Facades\Gate::allows('post.create')){
            $quickActions[] = [
                'title' => 'نوشته جدید',
                'href' => route('admin.posts.create'),
            ];
        }

        if(\Illuminate\Support\Facades\Gate::allows('project.create')){
            $quickActions[] = [
                'title' => 'پروژه جدید',
                'href' => route('admin.projects.create'),
            ];
        }

        if(\Illuminate\Support\Facades\Gate::allows('page.create')){
            $quickActions[] = [
                'title' => 'برگه جدید',
                'href' => route('admin.pages.create'),
            ];
        }

        if(\Illuminate\Support\Facades\Gate::allows('product.create')){
            $quickActions[] = [
                'title' => 'محصول جدید',
                'href' => route('admin.products.create'),
            ];
        }

        $contextAction = null;

        if (request()->routeIs('contents.detail') and \Illuminate\Support\Facades\Gate::allows('post.edit') and isset($post)) {
            $contextAction = [
                'title' => 'ویرایش نوشته',
                'href' => route('admin.posts.edit', $post),
            ];
        } elseif (request()->routeIs('projects.detail') and \Illuminate\Support\Facades\Gate::allows('project.edit') and isset($project)) {
            $contextAction = [
                'title' => 'ویرایش پروژه',
                'href' => route('admin.projects.edit', $project),
            ];
        } elseif (request()->routeIs('contents.detail') and \Illuminate\Support\Facades\Gate::allows('page.edit') and isset($page)) {
            $contextAction = [
                'title' => 'ویرایش برگه',
                'href' => route('admin.pages.edit', $page),
            ];
        } elseif (request()->routeIs('products.detail') and \Illuminate\Support\Facades\Gate::allows('product.edit') and isset($product)) {
            $contextAction = [
                'title' => 'ویرایش محصول',
                'href' => route('admin.products.edit', $product),
            ];
        }
    @endphp

    <div
        x-data="{ quickOpen: false, mobileOpen: false }"
        class="fixed inset-x-0 top-0 z-[1000]"
    >
        <div class="border-b border-white/10 bg-slate-950/85 backdrop-blur-xl text-white">
            <div class="mx-auto flex h-10 max-w-7xl items-center justify-between gap-3 px-3 sm:px-4 lg:px-6">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg px-2 py-1 transition hover:bg-white/10"
                    >
                        <span
                            class="flex h-6 w-6 [&_svg]:w-3.5 [&_svg]:h-3.5 items-center justify-center rounded-lg bg-brand-500 text-white">
                            @include('icons.home-12')
                        </span>

                        <div class="hidden sm:flex sm:items-center sm:gap-2 sm:leading-none">
                            <span class="text-xs font-bold text-white">
                                {{ get_setting('general', 'title') }}
                            </span>
                        </div>
                    </a>

                    <div class="hidden h-4 w-px bg-white/10 md:block"></div>

                    <div class="hidden items-center gap-1 md:flex">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-lg px-2.5 leading-7 [&_svg]:w-3.5 [&_svg]:h-3.5 text-xs font-medium text-white/80 transition hover:bg-white/10 hover:text-white"
                        >
                            @include('icons.dashboard-square-01')
                            <span>پنل مدیریت</span>
                        </a>
                    </div>
                </div>

                <div class="hidden items-center gap-2 lg:flex">
                    <div class="relative flex items-center gap-2">
                        @if($contextAction)
                            <a
                                href="{{ $contextAction['href'] }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-2.5 leading-7 text-xs font-medium text-white transition hover:bg-white/15"
                            >
                                @include('icons.edit-03')
                                <span>{{ $contextAction['title'] }}</span>
                            </a>
                        @endif

                        @if(count($quickActions) > 0)
                            <button
                                type="button"
                                @click="quickOpen = !quickOpen"
                                @click.outside="quickOpen = false"
                                class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-2.5 leading-7 text-xs cursor-pointer font-medium text-white transition hover:bg-white/15"
                            >
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none" stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                </svg>
                                <span>ایجاد سریع</span>
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none" stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>

                            <div
                                x-show="quickOpen"
                                x-transition.origin.top.right
                                class="absolute left-0 top-[calc(100%+8px)] w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 text-slate-800 shadow-lg dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:shadow-black/30"
                                style="display: none;"
                            >
                                <div class="mb-1 px-2 py-1 text-xs font-bold text-slate-400 dark:text-slate-500">
                                    میانبرها
                                </div>

                                @foreach($quickActions as $action)
                                    <a
                                        href="{{ $action['href'] }}"
                                        class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-slate-100 dark:hover:bg-slate-800"
                                    >
                                        <span>{{ $action['title'] }}</span>
                                        <svg viewBox="0 0 24 24"
                                             class="h-4 w-4 fill-none text-slate-400 dark:text-slate-500"
                                             stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="h-4 w-px bg-white/10"></div>

                    <div class="flex items-center gap-1.5">
                        <div class="hidden items-center gap-1 md:flex">
                            <a
                                href="{{ route('account') }}"
                                class="inline-flex items-center gap-2 rounded-lg px-2.5 leading-7 [&_svg]:w-3.5 [&_svg]:h-3.5 text-xs font-medium text-white/80 transition hover:bg-white/10 hover:text-white"
                            >
                                @include('icons.user')
                                <span>{{ $user->display_name }}</span>
                            </a>
                        </div>

                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg px-2.5 leading-7 text-xs cursor-pointer font-medium text-white/80 transition hover:bg-red-500/15 hover:text-red-300"
                            >
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none" stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5M15 12H3"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7"/>
                                </svg>
                                <span>خروج</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex items-center gap-2 lg:hidden">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-white"
                        aria-label="پنل مدیریت"
                    >
                        <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 13h6V5H4v8Zm10 6h6V5h-6v14ZM4 19h6v-2H4v2Zm0-6h6v-2H4v2Zm10 6h6v-2h-6v2Z"/>
                        </svg>
                    </a>

                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/10 hover:text-white"
                        aria-label="باز کردن منو"
                    >
                        <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div
                x-show="mobileOpen"
                x-transition
                class="border-t border-white/10 px-3 py-3 lg:hidden"
                style="display: none;"
            >
                <div class="grid gap-2">
                    @if($contextAction)
                        <a
                            href="{{ $contextAction['href'] }}"
                            class="flex items-center justify-between rounded-xl bg-brand-500/15 px-3 py-3 text-sm font-medium text-white transition hover:bg-brand-500/20"
                        >
                            <span>{{ $contextAction['title'] }}</span>
                            @include('icons.edit-03')
                        </a>
                    @endif

                    <a
                        href="{{ route('admin.projects.create') }}"
                        class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10"
                    >
                        <span>پروژه جدید</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.posts.create') }}"
                        class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10"
                    >
                        <span>نوشته جدید</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.pages.create') }}"
                        class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10"
                    >
                        <span>برگه جدید</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                    </a>

                    <a
                        href="{{ route('admin.products.create') }}"
                        class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10"
                    >
                        <span>محصول جدید</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                    </a>

                    <a
                        href="{{ route('account') }}"
                        class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-3 text-sm font-medium text-white/85 transition hover:bg-white/10"
                    >
                        <span>حساب کاربری</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 7a7 7 0 0 1 14 0"/>
                        </svg>
                    </a>

                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="flex w-full items-center justify-between rounded-xl bg-red-500/10 px-3 py-3 text-sm font-medium text-red-300 transition hover:bg-red-500/15"
                        >
                            <span>خروج</span>
                            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5M15 12H3"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10 4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            padding-top: 2.5rem;
        }

        #header {
            top: 2.5rem;
        }
    </style>
@endif
