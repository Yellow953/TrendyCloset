<footer class="border-t border-slate-200 px-5 py-6 md:px-8">
    <div class="mx-auto flex w-full max-w-[1400px] flex-wrap items-center justify-between gap-3 text-[12px] font-normal text-slate-400">
        <div>Trendy Closet · Back office &copy; {{ now()->year }}</div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="transition-colors hover:text-slate-700">Storefront</a>
            <a href="{{ route('policies', 'privacy') }}" class="transition-colors hover:text-slate-700">Privacy</a>
            <span>Laravel {{ Illuminate\Foundation\Application::VERSION }}</span>
        </div>
    </div>
</footer>
