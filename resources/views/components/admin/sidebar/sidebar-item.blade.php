@props(['item'])

@php
    $label = $item['label'] ?? '';
    $route = $item['route'] ?? null;
    $routeParameters = $item['routeParameters'] ?? [];
    $href  = $route ? route($route, $routeParameters) : ($item['href'] ?? '#');

    $iconView = $item['icon'] ?? null;

    $can  = $item['can']  ?? null;
    $gate = $item['gate'] ?? null;

    $children = $item['children'] ?? [];
    $hasChildren = is_array($children) && count($children) > 0;

    $activePatterns = (array)($item['active'] ?? ($route ? [$route, $route.'.*'] : []));
    $isActive = false;
    foreach ($activePatterns as $pattern) {
        if (request()->routeIs($pattern)) { $isActive = true; break; }
    }

    $allowed = true;
    if ($can)  $allowed = auth()->check() && auth()->user()->can($can);
    if ($allowed && $gate) $allowed = \Illuminate\Support\Facades\Gate::allows($gate);
@endphp

@if($allowed)
    <li
        x-data="{ open: {{ $hasChildren && $isActive ? 'true' : 'false' }} }"
        class="select-none"
    >
        @if(!$hasChildren)
            <a
                href="{{ $href }}"
                class="
                flex items-center px-4 py-2 rounded-md transition-colors duration-200
                {{ $isActive ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-200 dark:hover:bg-gray-700' }}
                hover:text-gray-700 dark:hover:text-gray-200
            "
            >
            <span class="shrink-0">
                @if($iconView)
                    @includeIf($iconView)
                @else
                    <svg style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.6">
                        <path stroke-linecap="round" d="M5 12h14"/>
                    </svg>
                @endif
            </span>
                <span class="mx-3 font-medium text-sm">{{ $label }}</span>
            </a>
        @else
            {{-- Parent (toggle) --}}
            <button
                type="button"
                class="
                cursor-pointer w-full flex items-center px-4 py-2 rounded-md transition-colors duration-200 text-right
                {{ $isActive ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-200 dark:hover:bg-gray-700' }}
                hover:text-gray-700 dark:hover:text-gray-200
            "
                @click="open = !open"
            >
            <span class="shrink-0">
                @if($iconView)
                    @includeIf($iconView)
                @else
                    <svg style="width:18px;height:18px" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.6">
                        <path stroke-linecap="round" d="M6 8h12M6 12h12M6 16h12"/>
                    </svg>
                @endif
            </span>

                <span class="mx-3 font-medium text-sm flex-1">{{ $label }}</span>

                <svg class="shrink-0 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     style="width:16px;height:16px"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                </svg>
            </button>

            {{-- Children --}}
            <div
                x-show="open"
                x-transition
                class="mt-1 mr-4 pr-2 border-r border-gray-100 dark:border-gray-800"
            >
                <ul class="space-y-1">
                    @foreach($children as $child)
                        <x-admin.sidebar.sidebar-item :item="$child"/>
                    @endforeach
                </ul>
            </div>
        @endif
    </li>
@endif
