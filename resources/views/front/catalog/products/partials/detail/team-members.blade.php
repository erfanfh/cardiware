@php
    $modalId = 'team-member-modal-' . $product->getKey();
    $modalTitleId = 'team-member-modal-title-' . $product->getKey();

    $teamMembers = $product->teamMembers
        ->filter(fn ($productTeamMember) => $productTeamMember->teamMember !== null)
        ->map(function ($productTeamMember): array {
            $member = $productTeamMember->teamMember;

            $firstNameInitial = filled($member->first_name)
                ? mb_substr(trim($member->first_name), 0, 1)
                : '';

            $lastNameInitial = filled($member->last_name)
                ? mb_substr(trim($member->last_name), 0, 1)
                : '';

            $websiteUrl = filled($member->website_url)
                ? trim($member->website_url)
                : null;

            if (
                filled($websiteUrl)
                && !\Illuminate\Support\Str::startsWith(
                    $websiteUrl,
                    ['http://', 'https://']
                )
            ) {
                $websiteUrl = 'https://' . ltrim($websiteUrl, '/');
            }

            return [
                'id' => $member->getKey(),
                'full_name' => $member->full_name,
                'job_title' => $member->job_title,
                'bio' => $member->bio,

                /*
                 * If the image URL is received in MediaFile of accessor another way,
                 * change only this line.
                 */
                'picture_url' => $member->picture?->url,

                'email' => $member->email,
                'mobile' => $member->mobile,
                'website_url' => $websiteUrl,

                'initials' => mb_strtoupper(
                    $firstNameInitial . $lastNameInitial
                ),
            ];
        })
        ->values();
@endphp

@if($teamMembers->isNotEmpty())
    <section
        id="team-members"
        x-data="productTeamMembersSlider(
            @js($teamMembers->all()),
            @js($modalId)
        )"
    >
        <x-front.cart
            class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90"
        >
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <x-front.cart-title
                    title="Contributors"
                    subtitle="Meet the creators and contributors of this product"
                    iconName="user-multiple"
                />

                @if($teamMembers->count() > 1)
                    <div class="flex shrink-0 items-center gap-2">
                        <button
                            x-ref="previousButton"
                            type="button"
                            class="cursor-pointer flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white dark:focus:ring-slate-600 [&.swiper-button-disabled]:pointer-events-none [&.swiper-button-disabled]:opacity-30"
                            aria-label="Previous member"
                        >
                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 18 6-6-6-6"
                                />
                            </svg>
                        </button>

                        <button
                            x-ref="nextButton"
                            type="button"
                            class="cursor-pointer flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition duration-200 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-300 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-700 dark:hover:text-white dark:focus:ring-slate-600 [&.swiper-button-disabled]:pointer-events-none [&.swiper-button-disabled]:opacity-30"
                            aria-label="Next member"
                        >
                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m15 18-6-6 6-6"
                                />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            <div
                x-ref="swiper"
                class="swiper"
                dir="ltr"
            >
                <div class="swiper-wrapper">
                    @foreach($teamMembers as $member)
                        <div class="swiper-slide !h-auto">
                            <button
                                type="button"
                                @click="openMember({{ $loop->index }}, $event)"
                                class="cursor-pointer group flex h-full w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70 text-start transition duration-300 hover:border-slate-300 hover:bg-white focus:outline-none  dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-slate-700 dark:hover:bg-slate-800/80"
                                aria-label="View information {{ $member['full_name'] }}"
                            >
                                <div
                                    class="relative flex w-full items-center justify-center overflow-hidden bg-gradient-to-b from-slate-100 to-slate-50 px-5 pb-5 pt-8 dark:from-slate-800 dark:to-slate-900">
                                    <div
                                        class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-slate-200/60 to-transparent dark:from-slate-700/30"></div>

                                    <div class="relative">
                                        <div
                                            class="absolute -inset-3 rounded-full bg-white/80 opacity-0 blur-lg transition duration-300 group-hover:opacity-100 dark:bg-slate-500/20"></div>

                                        @if($member['picture_url'])
                                            <img
                                                src="{{ $member['picture_url'] }}"
                                                alt="{{ $member['full_name'] }}"
                                                loading="lazy"
                                                class="relative size-28 rounded-full border-4 border-white object-cover shadow-lg ring-1 ring-slate-200 transition duration-300 group-hover:scale-[1.03] dark:border-slate-800 dark:ring-slate-700 sm:size-32"
                                            >
                                        @else
                                            <div
                                                class="relative flex size-28 items-center justify-center rounded-full border-4 border-white bg-slate-200 text-3xl font-black text-slate-600 shadow-lg ring-1 ring-slate-300 dark:border-slate-800 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-600 sm:size-32">
                                                {{ $member['initials'] ?: '?' }}
                                            </div>
                                        @endif

                                        <span
                                            class="absolute bottom-1 left-1 flex size-8 items-center justify-center rounded-full border-4 border-white bg-slate-900 text-white shadow-sm dark:border-slate-800 dark:bg-white dark:text-slate-900">
                                                <svg
                                                    class="size-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m15 18-6-6 6-6"
                                                    />
                                                </svg>
                                            </span>
                                    </div>
                                </div>

                                <div class="flex w-full flex-1 flex-col px-5 pb-5 pt-4">
                                    <h3 class="text-base font-black text-slate-950 transition group-hover:text-slate-700 dark:text-white dark:group-hover:text-slate-200">
                                        {{ $member['full_name'] }}
                                    </h3>

                                    <p class="mt-1 min-h-6 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        {{ $member['job_title'] ?: 'Contributor' }}
                                    </p>

                                    <div
                                        class="mt-3 flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-700/70">
                                            <span class="text-[0.8rem] font-bold text-slate-700 dark:text-slate-200">
                                                View profile
                                            </span>

                                        <svg
                                            class="size-4 text-slate-400 transition duration-200 group-hover:-translate-x-1 group-hover:text-slate-700 dark:group-hover:text-white"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m15 18-6-6 6-6"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($teamMembers->count() > 1)
                <div
                    x-ref="pagination"
                    class="!hidden mt-5 flex min-h-5 items-center justify-center [&_.swiper-pagination-bullet]:mx-1 [&_.swiper-pagination-bullet]:h-2 [&_.swiper-pagination-bullet]:w-2 [&_.swiper-pagination-bullet]:cursor-pointer [&_.swiper-pagination-bullet]:rounded-full [&_.swiper-pagination-bullet]:bg-slate-300 [&_.swiper-pagination-bullet]:opacity-100 [&_.swiper-pagination-bullet]:transition-all [&_.swiper-pagination-bullet]:duration-200 [&_.swiper-pagination-bullet-active]:w-6 [&_.swiper-pagination-bullet-active]:bg-slate-900 dark:[&_.swiper-pagination-bullet]:bg-slate-700 dark:[&_.swiper-pagination-bullet-active]:bg-white"
                ></div>
            @endif
        </x-front.cart>

        <template x-teleport="body">
            <div
                id="{{ $modalId }}"
                x-show="isOpen"
                style="display: none;"
                @keydown.escape.window="closeModal"
                class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $modalTitleId }}"
            >
                {{-- Backdrop --}}
                <div
                    x-show="isOpen"
                    x-transition:enter="transition-opacity duration-200 ease-out"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-200 ease-in"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                    @click="closeModal"
                    aria-hidden="true"
                ></div>

                {{-- Modal --}}
                <div
                    x-show="isOpen"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="translate-y-full opacity-0 sm:translate-y-5 sm:scale-95"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                    x-transition:leave="transition duration-200 ease-in"
                    x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                    x-transition:leave-end="translate-y-full opacity-0 sm:translate-y-5 sm:scale-95"
                    class="relative z-10 max-h-[92dvh] w-full overflow-y-auto rounded-t-3xl border border-slate-200 bg-white text-start shadow-2xl dark:border-slate-700 dark:bg-slate-900 sm:max-w-2xl sm:rounded-3xl"
                    @click.stop
                >
                    <button
                        type="button"
                        data-team-member-close
                        @click="closeModal"
                        class="cursor-pointer absolute left-4 top-4 z-20 flex size-10 items-center justify-center rounded-full border border-white/60 bg-white/80 text-slate-700 shadow-sm backdrop-blur-md transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-white/80 dark:border-slate-600/60 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white"
                        aria-label="Close window"
                    >
                        <svg
                            class="size-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    {{-- Modal header --}}
                    <div
                        class="relative overflow-hidden rounded-t-3xl bg-gradient-to-br from-slate-100 via-white to-slate-200 px-6 pb-20 pt-12 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800 sm:px-10">
                        <div
                            class="absolute -right-16 -top-20 size-52 rounded-full bg-white/60 blur-3xl dark:bg-slate-600/20"></div>

                        <div
                            class="absolute -bottom-24 -left-12 size-52 rounded-full bg-slate-300/50 blur-3xl dark:bg-black/20"></div>

                        <div class="relative flex flex-col items-center text-center">
                            <template x-if="selectedMember && selectedMember.picture_url">
                                <img
                                    :src="selectedMember.picture_url"
                                    :alt="selectedMember.full_name"
                                    class="size-32 rounded-full border-4 border-white object-cover shadow-xl ring-1 ring-slate-200 dark:border-slate-800 dark:ring-slate-700 sm:size-36"
                                >
                            </template>

                            <template x-if="selectedMember && !selectedMember.picture_url">
                                <div
                                    class="flex size-32 items-center justify-center rounded-full border-4 border-white bg-slate-200 text-4xl font-black text-slate-600 shadow-xl ring-1 ring-slate-300 dark:border-slate-800 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-600 sm:size-36">
                                    <span x-text="selectedMember.initials || '?'"></span>
                                </div>
                            </template>

                            <h2
                                id="{{ $modalTitleId }}"
                                class="mt-5 text-xl font-black text-slate-950 dark:text-white sm:text-2xl"
                                x-text="selectedMember?.full_name || ''"
                            ></h2>

                            <p
                                class="mt-2 text-sm font-bold text-slate-500 dark:text-slate-400"
                                x-text="selectedMember?.job_title || 'Contributor'"
                            ></p>
                        </div>
                    </div>

                    {{-- Modal body --}}
                    <div class="-mt-10 px-4 pb-6 sm:px-8 sm:pb-8">
                        <div
                            class="relative rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/5 dark:border-slate-700 dark:bg-slate-800 dark:shadow-black/20 sm:p-6">
                            <template x-if="selectedMember && selectedMember.bio">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                            <svg
                                                class="size-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h7.5M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                                                />
                                            </svg>
                                        </span>

                                        <h3 class="text-sm font-black text-slate-950 dark:text-white">
                                            About member
                                        </h3>
                                    </div>

                                    <p
                                        class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-600 dark:text-slate-300"
                                        x-text="selectedMember.bio"
                                    ></p>
                                </div>
                            </template>

                            <template x-if="hasContactInformation">
                                <div
                                    :class="selectedMember && selectedMember.bio
                                        ? 'mt-6 border-t border-slate-200 pt-6 dark:border-slate-700'
                                        : ''"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                            <svg
                                                class="size-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-7.5 3.75h7.5M21 12c0 4.97-4.03 9-9 9a9.96 9.96 0 0 1-4.255-.95L3 21l.95-4.745A9.96 9.96 0 0 1 3 12c0-4.97 4.03-9 9-9s9 4.03 9 9Z"
                                                />
                                            </svg>
                                        </span>

                                        <h3 class="text-sm font-black text-slate-950 dark:text-white">
                                            Contact channels
                                        </h3>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <template x-if="selectedMember && selectedMember.email">
                                            <a
                                                :href="emailLink"
                                                class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:hover:border-slate-600 dark:hover:bg-slate-900"
                                            >
                                                <span
                                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm dark:bg-slate-800 dark:text-slate-300">
                                                    <svg
                                                        class="size-5"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.69 5.52a2 2 0 0 1-2.12 0L2.25 6.75"
                                                        />
                                                    </svg>
                                                </span>

                                                <span class="min-w-0">
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                        Email
                                                    </span>

                                                    <span
                                                        class="mt-1 block truncate text-sm font-bold text-slate-800 dark:text-slate-100"
                                                        dir="ltr"
                                                        x-text="selectedMember.email"
                                                    ></span>
                                                </span>
                                            </a>
                                        </template>

                                        <template x-if="selectedMember && selectedMember.mobile">
                                            <a
                                                :href="mobileLink"
                                                class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:hover:border-slate-600 dark:hover:bg-slate-900"
                                            >
                                                <span
                                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm dark:bg-slate-800 dark:text-slate-300">
                                                    <svg
                                                        class="size-5"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.36-.27.526-.731.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                                                        />
                                                    </svg>
                                                </span>

                                                <span class="min-w-0">
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                        Phone number
                                                    </span>

                                                    <span
                                                        class="mt-1 block truncate text-sm font-bold text-slate-800 dark:text-slate-100"
                                                        dir="ltr"
                                                        x-text="selectedMember.mobile"
                                                    ></span>
                                                </span>
                                            </a>
                                        </template>

                                        <template x-if="selectedMember && selectedMember.website_url">
                                            <a
                                                :href="selectedMember.website_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/60 dark:hover:border-slate-600 dark:hover:bg-slate-900 sm:col-span-2"
                                            >
                                                <span
                                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm dark:bg-slate-800 dark:text-slate-300">
                                                    <svg
                                                        class="size-5"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 21a9 9 0 1 0 0-18m0 18c2.486 0 4.5-4.03 4.5-9S14.486 3 12 3m0 18c-2.486 0-4.5-4.03-4.5-9S9.514 3 12 3M3.6 9h16.8M3.6 15h16.8"
                                                        />
                                                    </svg>
                                                </span>

                                                <span class="min-w-0">
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                        Website
                                                    </span>

                                                    <span
                                                        class="mt-1 block truncate text-sm font-bold text-slate-800 dark:text-slate-100"
                                                        dir="ltr"
                                                        x-text="selectedMember.website_url"
                                                    ></span>
                                                </span>

                                                <svg
                                                    class="mr-auto size-4 shrink-0 text-slate-400 transition group-hover:-translate-x-0.5 group-hover:-translate-y-0.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M6 8.25v9.75h9.75"
                                                    />
                                                </svg>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="selectedMember && !selectedMember.bio && !hasContactInformation">
                                <div class="py-6 text-center">
                                    <span
                                        class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-300">
                                        <svg
                                            class="size-6"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M11.25 11.25 12 10.5m0 0 .75.75M12 10.5v4.125m9-2.625a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                            />
                                        </svg>
                                    </span>

                                    <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                        No additional information has been provided for this member.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </section>
@endif
