@extends('layouts.storefront')

@section('content')
    <div class="bg-cream px-8 py-12 text-center md:px-16">
        <h1 class="text-[34px] font-normal">{{ $page['title'] }}</h1>
        <div class="mx-auto mt-2.5 max-w-[560px] text-[14.5px] font-light text-muted-2">{{ $page['intro'] }}</div>
    </div>

    <div class="flex flex-col gap-10 px-8 py-12 md:px-16 lg:flex-row">
        {{-- Section nav — one entry per policy page --}}
        <nav class="flex flex-col gap-0.5 lg:flex-[0_0_260px]">
            @foreach($topics as $slug => $topic)
                <a href="{{ route('policies', $slug) }}"
                   class="border-b border-line px-[18px] py-3.5 text-[14px] transition-colors {{ $slug === $current ? 'border-ink bg-ink font-medium text-white' : 'text-muted-3 hover:text-blush' }}">
                    {{ $topic['title'] }}
                </a>
            @endforeach
            <div class="mt-6 bg-cream-3 p-5 text-[13.5px] font-light leading-[1.7] text-muted-2">
                Still stuck? <a href="{{ route('contact') }}" class="tc-link">Message us</a> — we answer within 24 hours.
            </div>
        </nav>

        {{-- Document --}}
        <div class="flex flex-1 flex-col gap-7">
            @foreach($page['sections'] as $section)
                <section>
                    <h2 class="mb-2 text-[17px] font-medium">{{ $section['heading'] }}</h2>
                    <p class="max-w-[720px] text-[14.5px] font-light leading-[1.8] text-muted-3">{{ $section['body'] }}</p>
                </section>
            @endforeach

            @if($sizeRuns)
                {{-- Built from the size runs actually stocked --}}
                <section>
                    <h2 class="mb-3 text-[17px] font-medium">Sizes we stock</h2>
                    <div class="flex flex-col gap-5">
                        @foreach($sizeRuns as $label => $sizes)
                            @continue($sizes->isEmpty())
                            <div>
                                <div class="mb-2 text-[13px] font-medium tracking-[0.08em] text-blush">{{ Str::upper($label) }}</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($sizes as $size)
                                        <span class="min-w-[52px] border border-line-2 py-2 text-center text-[14px]">{{ $size }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 text-[17px] font-medium">Measurements</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[460px] border-collapse text-[14px] font-light">
                            <thead>
                                <tr class="border-b border-line-2 text-left text-[12.5px] font-medium tracking-[0.08em] text-muted">
                                    <th class="py-2.5 pr-4">SIZE</th><th class="py-2.5 pr-4">BUST (cm)</th><th class="py-2.5 pr-4">WAIST (cm)</th><th class="py-2.5">HIPS (cm)</th>
                                </tr>
                            </thead>
                            <tbody class="text-muted-3">
                                @foreach([['XS', '78–82', '60–64', '86–90'], ['S', '82–86', '64–68', '90–94'], ['M', '86–92', '68–74', '94–100'], ['L', '92–98', '74–80', '100–106'], ['XL', '98–104', '80–86', '106–112'], ['2XL', '104–112', '86–94', '112–120']] as $row)
                                    <tr class="border-b border-line">
                                        <td class="py-2.5 pr-4 font-medium text-ink">{{ $row[0] }}</td>
                                        <td class="py-2.5 pr-4">{{ $row[1] }}</td>
                                        <td class="py-2.5 pr-4">{{ $row[2] }}</td>
                                        <td class="py-2.5">{{ $row[3] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <div class="border-t border-line pt-5 text-[13px] font-light text-muted">
                Last updated {{ now()->format('F Y') }}.
            </div>
        </div>
    </div>
@endsection
