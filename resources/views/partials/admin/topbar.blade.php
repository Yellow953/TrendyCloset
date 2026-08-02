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

            {{-- Who is signed in. A <details>, so it opens without JS; admin.js
                 only adds the outside-click and Escape a bare one lacks. --}}
            <details class="ad-menu relative ml-1" data-admin-menu>
                <summary class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white py-1 pr-2 pl-1 transition-colors hover:bg-slate-50"
                         aria-haspopup="menu" aria-label="Account">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-[11.5px] font-semibold text-white">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden max-w-[130px] truncate text-[13px] font-semibold text-slate-700 md:block">
                        {{ auth()->user()->name }}
                    </span>
                    <x-admin.icon name="chevron" class="h-3.5 w-3.5 text-slate-400" />
                </summary>

                <div class="ad-card absolute right-0 z-30 mt-2 w-60 overflow-hidden">
                    <div class="border-b border-slate-100 px-4 py-3.5">
                        <div class="truncate text-[13px] font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                        <div class="mt-0.5 truncate text-[11.5px] font-normal text-slate-400">{{ auth()->user()->email }}</div>
                        <span class="ad-badge ad-badge-neutral mt-2.5">{{ auth()->user()->role->label() }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 px-4 py-3 text-left text-[13px] font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            <x-admin.icon name="logout" class="h-4 w-4 text-slate-400" />
                            Sign out
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>
