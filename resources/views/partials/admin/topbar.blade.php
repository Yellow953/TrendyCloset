{{-- Sticky top bar: the nav toggle on small screens, a jump-to search across
     the catalogue, and the way back out to the shop. --}}
<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex w-full max-w-[1400px] items-center gap-4 px-5 py-3 md:px-8">

        <button type="button" data-admin-nav-open
                class="-ml-1 flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-800 lg:hidden"
                aria-label="Open navigation">
            <x-admin.icon name="menu" />
        </button>

        <form method="GET" action="{{ route('admin.products.index') }}" class="relative hidden max-w-[400px] flex-1 sm:block">
            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-slate-400">
                <x-admin.icon name="search" class="h-4 w-4" />
            </span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…"
                   class="ad-input rounded-lg py-2 pl-10 text-[13px]">
        </form>

        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
               class="ad-btn ad-btn-sm hidden sm:inline-flex">
                <x-admin.icon name="bell" class="h-4 w-4 text-slate-400" />
                <span class="ad-figure">{{ $openOrders ?? 0 }}</span> to fulfil
            </a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="ad-btn ad-btn-sm">
                View shop <x-admin.icon name="external" class="h-3.5 w-3.5 text-slate-400" />
            </a>
        </div>
    </div>
</header>
