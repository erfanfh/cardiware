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

    $isAllowed = function ($sidebarItem) : bool {
        $allowed = true;

        $canChild  = $sidebarItem['can']  ?? null;
        $gateChild = $sidebarItem['gate'] ?? null;

        if ($canChild) {
            $allowed = auth()->check() && auth()->user()->can($canChild);
        }
        if ($allowed && $gateChild) {
            $allowed = \Illuminate\Support\Facades\Gate::allows($gateChild);
        }

        return $allowed;
    };

    $isItemActive = function (array $sidebarItem) use (&$isItemActive): bool {
        $itemRoute = $sidebarItem['route'] ?? null;
        $itemChildren = $sidebarItem['children'] ?? [];
        $itemParams = $sidebarItem['routeParameters'][0] ?? null;
        $itemHasChildren = is_array($itemChildren) && count($itemChildren) > 0;

        $activePatterns = (array)($sidebarItem['active'] ?? ($itemRoute ? [$itemRoute, $itemRoute . '.*'] : []));

        foreach ($activePatterns as $pattern) {
            if (request()->routeIs($pattern)) {
                if (!empty($itemParams)) {
                    $expectedRoute = route($itemRoute, $itemParams);
                    $currentRoute = url()->current();
                    return $currentRoute === $expectedRoute;
                }
                return true;
            }
        }

        if ($itemHasChildren) {
            foreach ($itemChildren as $child) {
                if (is_array($child) && $isItemActive($child)) {
                    return true;
                }
            }
        }

        return false;
    };

    $isActive = $isItemActive($item);

    $allowed = $isAllowed($item);

    $allowedChildren = [];
    if ($hasChildren) {
        foreach ($children as $child) {
            if (is_array($child) && $isAllowed($child)) {
                $allowedChildren[] = $child;
            }
        }
    }

    $hasAllowedChildren = count($allowedChildren) > 0;
@endphp

@if($allowed)
    @if(!$hasChildren || $hasAllowedChildren)
        <li
            x-data="{ open: {{ $hasAllowedChildren && $isActive ? 'true' : 'false' }} }"
            class="select-none"
        >
            @if(!$hasChildren || !$hasAllowedChildren)
                {{-- اگر parent child قابل مشاهده نداشت، می‌تونه به لینک تبدیل شود.
                    (اگر نمی‌خوای لینک نمایش بدی و می‌خوای کلاً hide باشه، این بخش را حذف کن) --}}
                <a
                    href="{{ $href }}"
                    class="
                    flex items-center px-4 py-2 rounded-2xl transition-colors duration-200 hover:bg-brand-500 hover:text-white
                    {{ $isActive ? 'bg-brand-500 text-white' : '' }}
                    hover:text-gray-700
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
                    cursor-pointer w-full flex items-center px-4 py-2 rounded-2xl transition-colors duration-200 text-right hover:bg-brand-500 hover:text-white
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

                {{-- Children (فقط مجازها) --}}
                <div
                    x-show="open"
                    x-transition
                    class="mt-1 mr-4 pr-2 border-r border-gray-100"
                >
                    <ul class="space-y-1">
                        @foreach($allowedChildren as $child)
                            <x-admin.sidebar.sidebar-item :item="$child"/>
                        @endforeach
                    </ul>
                </div>
            @endif
        </li>
    @endif
@endif
