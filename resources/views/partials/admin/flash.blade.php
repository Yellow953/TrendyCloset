{{-- Flash + validation feedback. Unlike the storefront, the back office wants
     to see every field that failed, not just the first — you are editing a
     form, not being nudged. --}}
@if(session('status'))
    <div class="ad-card mb-5 flex items-start gap-3 border-l-2 border-l-jade px-5 py-3.5">
        <span class="mt-0.5 text-[13px] text-emerald-600">✓</span>
        <p class="text-[13.5px] font-normal text-slate-600">{{ session('status') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="ad-card mb-5 border-l-2 border-l-rose px-5 py-3.5">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 text-[13px] text-rose-600">!</span>
            <div>
                <p class="text-[13.5px] font-medium text-rose-600">
                    {{ $errors->count() === 1 ? 'Something needs fixing' : $errors->count().' things need fixing' }}
                </p>
                <ul class="mt-1.5 flex flex-col gap-1">
                    @foreach($errors->all() as $error)
                        <li class="text-[13px] font-normal text-slate-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
