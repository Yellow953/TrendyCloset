{{-- Back-office navigation. A fixed rail on desktop, a slide-in panel below
     `lg`. `$active` is set by every admin action; the counts come from the view
     composer in AppServiceProvider. --}}
@php
    $sections = [
        [
            'label' => null,
            'links' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard'],
            ],
        ],
        [
            'label' => 'Catalogue',
            'links' => [
                ['key' => 'products', 'label' => 'Products', 'icon' => 'products', 'route' => 'admin.products.index'],
                ['key' => 'categories', 'label' => 'Categories', 'icon' => 'categories', 'route' => 'admin.categories.index'],
            ],
        ],
        [
            'label' => 'Trade',
            'links' => [
                ['key' => 'orders', 'label' => 'Orders', 'icon' => 'orders', 'route' => 'admin.orders.index', 'count' => $openOrders ?? 0],
                ['key' => 'customers', 'label' => 'Customers', 'icon' => 'customers', 'route' => 'admin.customers.index'],
                ['key' => 'messages', 'label' => 'Messages', 'icon' => 'messages', 'route' => 'admin.messages.index', 'count' => $unreadMessageCount ?? 0],
            ],
        ],
        [
            'label' => 'Settings',
            'admin' => true,
            'links' => [
                ['key' => 'coupons', 'label' => 'Discount codes', 'icon' => 'coupons', 'route' => 'admin.coupons.index'],
                ['key' => 'users', 'label' => 'Staff', 'icon' => 'users', 'route' => 'admin.users.index'],
            ],
        ],
    ];
@endphp

<aside data-admin-nav
       class="ad-sidebar fixed inset-y-0 left-0 z-40 flex flex-col bg-black lg:translate-x-0">

    {{-- Product name — plain, unbranded --}}
    <div class="ad-sidebar-head flex items-center justify-between px-6 py-[22px]">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[15px] font-bold text-black">T</span>
            <span class="ad-hide-collapsed text-[15px] font-semibold tracking-[-0.01em] whitespace-nowrap text-white">Trendy Closet</span>
        </a>

        {{-- Collapse the rail (desktop) / close the panel (mobile) --}}
        <button type="button" data-admin-nav-collapse
                class="ad-hide-collapsed -mr-1 hidden h-8 w-8 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-white/10 hover:text-white lg:flex"
                aria-label="Collapse navigation">
            <x-admin.icon name="sidebar" />
        </button>
        <button type="button" data-admin-nav-close
                class="-mr-1 flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Close navigation">
            <x-admin.icon name="close" />
        </button>
    </div>

    {{-- Expand handle — the only control visible while collapsed --}}
    <button type="button" data-admin-nav-expand
            class="ad-show-collapsed mx-auto mb-1 hidden h-8 w-8 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-white/10 hover:text-white"
            aria-label="Expand navigation">
        <x-admin.icon name="sidebar" />
    </button>

    <nav class="flex-1 overflow-y-auto px-3.5 pb-4">
        @foreach($sections as $section)
            @continue(($section['admin'] ?? false) && ! auth()->user()->isAdmin())

            @if($section['label'])
                <div class="ad-hide-collapsed mt-6 mb-2 px-3 text-[10.5px] font-semibold tracking-[0.08em] whitespace-nowrap text-slate-500 uppercase">
                    {{ $section['label'] }}
                </div>
            @else
                <div class="ad-hide-collapsed mt-1"></div>
            @endif

            <div class="flex flex-col gap-1">
                @foreach($section['links'] as $link)
                    <a href="{{ route($link['route']) }}" title="{{ $link['label'] }}"
                       class="ad-nav-link {{ $active === $link['key'] ? 'is-active' : '' }}">
                        <span class="ad-nav-icon">
                            <x-admin.icon :name="$link['icon']" />
                            {{-- Collapsed rail keeps a dot so unread work is still visible --}}
                            @if(($link['count'] ?? 0) > 0)
                                <span class="ad-show-collapsed absolute -top-0.5 -right-0.5 hidden h-2 w-2 rounded-full bg-white ring-2 ring-black"></span>
                            @endif
                        </span>
                        <span class="ad-hide-collapsed flex-1 whitespace-nowrap">{{ $link['label'] }}</span>
                        @if(($link['count'] ?? 0) > 0)
                            <span class="ad-hide-collapsed ad-figure rounded-md {{ $active === $link['key'] ? 'bg-white/20' : 'bg-slate-800' }} px-1.5 py-0.5 text-[10.5px] font-semibold text-white">{{ $link['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Who is signed in --}}
    <div class="border-t border-white/10 px-3.5 py-4">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-800 text-[12px] font-semibold text-white">
                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="ad-hide-collapsed min-w-0 flex-1">
                <div class="truncate text-[12.5px] font-semibold text-white">{{ auth()->user()->name }}</div>
                <div class="text-[11px] font-normal text-slate-400">{{ auth()->user()->role->label() }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="ad-nav-link w-full text-left" title="Sign out">
                <span class="ad-nav-icon"><x-admin.icon name="logout" /></span>
                <span class="ad-hide-collapsed whitespace-nowrap">Sign out</span>
            </button>
        </form>
    </div>
</aside>
