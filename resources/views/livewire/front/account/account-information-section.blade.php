<section class="space-y-4 xl:space-y-6">
    @if(session('account_information_success'))
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold leading-7 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('account_information_success') }}
        </div>
    @endif

    <div id="account-avatar" class="scroll-mt-44 rounded-2xl border border-brand-200 bg-gradient-to-l from-brand-50 to-white p-4 shadow-sm md:scroll-mt-40 sm:p-6 dark:border-brand-500/30 dark:from-brand-500/10 dark:to-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-white">Change profile picture</h2>
                <p class="mt-1 text-xs leading-6 text-slate-600 dark:text-slate-300">Choose your avatar from the collection below; members of VIP can also upload a custom photo.</p>
            </div>
            <img src="{{ auth()->user()->profileAvatarUrl() ?: asset('img/user-avatar.png') }}" alt="Current profile picture" width="64" height="64" class="size-16 rounded-2xl border-2 border-white object-cover shadow-sm ring-2 ring-brand-200 dark:border-slate-800 dark:ring-brand-500/30">
        </div>

        @if($avatarErrorMessage)
            <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ $avatarErrorMessage }}</div>
        @endif

        <form wire:submit.prevent="saveAvatar" class="mt-5">
            <fieldset>
                <legend class="sr-only">Select profile avatar</legend>
                <div class="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-12">
                    @foreach(\App\Domain\Identity\PresetAvatars::ids() as $avatarId)
                        <label class="group relative cursor-pointer" wire:key="preset-avatar-{{ $avatarId }}">
                            <input type="radio" name="preset_avatar" wire:model="preset_avatar" value="{{ $avatarId }}" class="peer sr-only" aria-label="Avatar {{ $avatarId }}">
                            <img src="{{ \App\Domain\Identity\PresetAvatars::url($avatarId) }}" alt="" loading="lazy" width="96" height="96"
                                 class="aspect-square w-full rounded-2xl border-2 border-slate-200 bg-white object-cover transition group-hover:border-brand-300 peer-checked:border-brand-500 peer-checked:ring-4 peer-checked:ring-brand-500/20 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500 dark:border-slate-600 dark:bg-slate-900">
                            <span class="pointer-events-none absolute bottom-1 left-1 hidden rounded-full bg-brand-500 px-1.5 py-0.5 text-[10px] font-black text-white peer-checked:block" aria-hidden="true">✓</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            @error('preset_avatar')
                <p role="alert" class="mt-3 text-xs font-bold text-red-600 dark:text-red-300">{{ $message }}</p>
            @enderror
            <button type="submit" wire:loading.attr="disabled" wire:target="saveAvatar"
                    class="mt-5 inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 sm:w-auto">
                <span wire:loading.remove wire:target="saveAvatar">Save avatar</span>
                <span wire:loading wire:target="saveAvatar">Saving...</span>
            </button>
        </form>

        {{-- ═══ Upload custom photo - exclusive to members of VIP ═══ --}}
        <div class="mt-5 border-t border-brand-100 pt-5 dark:border-brand-500/20">
            @if($this->vipActive)
                @php
                    $previewUrl = null;
                    if ($customAvatar) {
                        try { $previewUrl = $customAvatar->temporaryUrl(); } catch (Throwable) {}
                    }
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="grid size-7 place-items-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 [&_svg]:size-4">@include('icons.crown-03')</span>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Custom photo <span class="rounded-lg bg-amber-100 px-1.5 py-0.5 text-[10px] font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">VIP</span></h3>
                    </div>
                    @if($this->hasCustomAvatar)
                        <span class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 dark:border-emerald-500/25 dark:bg-emerald-500/10 dark:text-emerald-300">
                            Custom photo is active
                        </span>
                    @endif
                </div>

                @if($customAvatarErrorMessage)
                    <div role="alert" class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold leading-6 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ $customAvatarErrorMessage }}</div>
                @endif
                @error('customAvatar')
                    <div role="alert" class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold leading-6 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ $message }}</div>
                @enderror

                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <div class="relative shrink-0">
                        <img src="{{ $previewUrl ?: (auth()->user()->profileAvatarUrl() ?: asset('img/user-avatar.png')) }}"
                             alt="Profile photo preview" width="80" height="80"
                             class="size-20 rounded-2xl border-2 border-amber-200 bg-white object-cover shadow-sm dark:border-amber-500/30 dark:bg-slate-900">
                        <span class="pointer-events-none absolute -bottom-1.5 -right-1.5 grid size-6 place-items-center rounded-full bg-amber-400 text-[11px] font-black text-slate-950 shadow-sm" aria-hidden="true">★</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <label class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/60 px-4 text-xs font-extrabold text-amber-800 transition hover:border-amber-400 hover:bg-amber-50 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/15">
                            <span wire:loading.remove wire:target="customAvatar">Select image (JPG / PNG / WebP Up to 1 MB)</span>
                            <span wire:loading wire:target="customAvatar" class="animate-pulse">Receiving file...</span>
                            <input type="file" wire:model="customAvatar" class="sr-only"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>

                        @if($customAvatar)
                            <button type="button" wire:click="saveCustomAvatar" wire:loading.attr="disabled" wire:target="saveCustomAvatar"
                                    class="mt-3 inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-amber-500 px-5 text-sm font-extrabold text-slate-950 shadow-soft transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                                <span wire:loading.remove wire:target="saveCustomAvatar">Save custom photo</span>
                                <span wire:loading wire:target="saveCustomAvatar" class="animate-pulse">Saving...</span>
                            </button>
                        @else
                            <p class="mt-2 text-[11px] leading-5 font-bold text-slate-500 dark:text-slate-400">
                                Saving a custom photo replaces the previous preset avatar.
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-gradient-to-l from-amber-50 to-transparent p-4 dark:border-amber-500/25 dark:from-amber-500/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 [&_svg]:size-5">@include('icons.crown-03')</span>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white">Upload custom profile photo</div>
                            <p class="mt-1 text-xs leading-6 text-slate-600 dark:text-slate-400">Exclusive to members of VIP — Replace preset avatars with your own photo (up to 1 MB).</p>
                        </div>
                    </div>
                    <a href="{{ route('vip.index') }}" class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-2.5 text-xs font-extrabold text-slate-950 transition hover:bg-amber-400">
                        Activate VIP
                        <span aria-hidden="true">←</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-6">
    <div
        class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">Account information</div>
                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Your main account details</div>
            </div>

            <div wire:loading wire:target="save,cancel"
                 class="text-xs font-extrabold text-brand-700 dark:text-brand-300">
                Processing...
            </div>
        </div>

        @if($errorMessage)
            <div
                class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-6 text-red-800 sm:mt-5 sm:p-4 sm:text-sm sm:leading-7 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="mt-4 space-y-4 sm:mt-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Name</label>
                    <input
                        type="text"
                        wire:model.defer="first_name"
                        autocomplete="given-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('first_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('first_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Name
                        Last name</label>
                    <input
                        type="text"
                        wire:model.defer="last_name"
                        autocomplete="family-name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('last_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('last_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Display name</label>
                    <input
                        type="text"
                        wire:model.defer="display_name"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                            @error('display_name')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('display_name')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Username</label>
                    <input
                        type="text"
                        value="{{ $this->usernameLabel() }}"
                        disabled
                        class="mt-2 w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                    >
                    <div class="mt-2 text-[11px] font-bold leading-5 text-slate-400 dark:text-slate-500">
                        Username cannot be changed.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Email</label>
                    <input
                        type="email"
                        wire:model.defer="email"
                        autocomplete="email"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold outline-none transition focus:ring-4
                            @error('email')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('email')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Number
                        Mobile</label>
                    <input
                        type="text"
                        wire:model.defer="mobile"
                        autocomplete="tel"
                        dir="ltr"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-left text-sm font-bold outline-none transition focus:ring-4
                            @error('mobile')
                                border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:bg-red-50 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:border-red-500/40 dark:focus:ring-red-500/20
                            @else
                                border-slate-200 bg-slate-50 text-slate-800 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                            @enderror"
                    >
                    @error('mobile')
                    <div
                        class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pt-1 sm:pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>

    <div
        class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">Additional information</div>
        <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Important account statuses</div>

        <div class="mt-4 space-y-3 sm:mt-5 sm:space-y-4">
            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Joined on</div>
                    <div
                        class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $this->joinedAtLabel() }}</div>
                </div>
            </div>

            <div
                @class([
                    'rounded-2xl border p-4',
                    'border-amber-200 bg-amber-50/70 dark:border-amber-500/25 dark:bg-amber-500/5' => $this->vipExpiresAt,
                    'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/70' => ! $this->vipExpiresAt,
                ])>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 [&_svg]:size-5">@include('icons.crown-03')</span>
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white">Account type</div>
                            <p class="mt-1 text-xs leading-6 text-slate-600 dark:text-slate-400">
                                {{ $this->vipExpiresAt ? 'Subscription VIP is active.' : 'You do not yet have an VIP active subscription.' }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit shrink-0 rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->accountTypeClass() }}">
                        {{ $this->accountTypeLabel() }}
                    </span>
                </div>

                @if($this->vipExpiresAt)
                    <div class="mt-3 border-t border-amber-200 pt-3 text-xs leading-6 text-amber-900 dark:border-amber-500/20 dark:text-amber-200">
                        Valid until <time datetime="{{ $this->vipExpiresAt->toIso8601String() }}" class="font-extrabold">{{ (($this->vipExpiresAt) ? \Illuminate\Support\Carbon::parse($this->vipExpiresAt)->format('j F Y') : '') }} hours {{ (($this->vipExpiresAt) ? \Illuminate\Support\Carbon::parse($this->vipExpiresAt)->format('H:i') : '') }}</time>
                    </div>
                @else
                    <p class="mt-3 text-xs leading-6 text-slate-600 dark:text-slate-400">with VIP Access selected product versions without separate purchase and get a special badge next to comments.</p>
                    <a href="{{ route('vip.index') }}" class="mt-3 inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-2.5 text-xs font-extrabold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                        View plans VIP
                        <span aria-hidden="true">←</span>
                    </a>
                @endif
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Email status</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->emailVerificationClass() }}">
                            {{ $this->emailVerificationLabel() }}
                        </span>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Mobile status</div>

                    <span
                        class="inline-flex w-fit rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $this->mobileVerificationClass() }}">
                            {{ $this->mobileVerificationLabel() }}
                        </span>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Account status</div>

                    <span
                        class="inline-flex w-fit rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                            {{ $this->userStatusLabel() }}
                        </span>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
