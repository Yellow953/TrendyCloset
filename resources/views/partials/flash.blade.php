{{-- Flash + validation feedback for the storefront's forms (bag, coupons,
     favourites, contact). Rendered by the layout, above the page content. --}}
@if(session('status'))
    <div class="border-b border-line bg-cream-3 px-5 py-3 text-center text-[13.5px] font-normal text-ink md:px-10">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="border-b border-blush bg-cream-2 px-5 py-3 text-center text-[13.5px] font-normal text-blush md:px-10">
        {{ $errors->first() }}
    </div>
@endif
