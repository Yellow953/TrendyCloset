@extends('layouts.admin')

@section('title', 'Opening hours')
@section('heading', 'Opening hours')
@section('subheading', 'When the shop in Dekwaneh is open. Shown in the footer, on Contact and About, and to search engines.')

@section('actions')
    <button type="submit" form="hours-form" class="bo-btn-primary">Save hours</button>
@endsection

@section('content')
    <form id="hours-form" method="POST" action="{{ route('admin.hours.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">
            <div class="bo-card">
                <div class="bo-card-head"><div class="bo-card-title">Week</div></div>
                <div class="divide-y divide-slate-100">
                    @foreach($week as $day => $hours)
                        @php
                            $closed = (bool) old("days.$day.is_closed", $hours->is_closed);
                            $dayErrors = $errors->get("days.$day.*");
                        @endphp
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-3 px-5 py-4">
                            <div class="w-[110px] text-[13.5px] font-semibold text-slate-800">{{ $hours->name }}</div>

                            <div class="flex items-center gap-2">
                                <input type="time" name="days[{{ $day }}][opens_at]" aria-label="{{ $hours->name }} opens"
                                       value="{{ old("days.$day.opens_at", $hours->opens_at) }}"
                                       class="bo-input w-[130px] {{ $errors->has("days.$day.opens_at") ? 'border-rose-600' : '' }}">
                                <span class="text-slate-400">–</span>
                                <input type="time" name="days[{{ $day }}][closes_at]" aria-label="{{ $hours->name }} closes"
                                       value="{{ old("days.$day.closes_at", $hours->closes_at) }}"
                                       class="bo-input w-[130px] {{ $errors->has("days.$day.closes_at") ? 'border-rose-600' : '' }}">
                            </div>

                            <label class="flex cursor-pointer items-center gap-2 text-[13px] text-slate-700">
                                <input type="hidden" name="days[{{ $day }}][is_closed]" value="0">
                                <input type="checkbox" name="days[{{ $day }}][is_closed]" value="1" @checked($closed)
                                       class="h-4 w-4 accent-slate-900">
                                Closed
                            </label>

                            @if($dayErrors)
                                <p class="bo-error w-full">{{ collect($dayErrors)->flatten()->first() }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bo-card h-fit">
                <div class="bo-card-head"><div class="bo-card-title">On the site</div></div>
                <div class="px-5 py-5 text-[13.5px] leading-[1.8] text-slate-700">
                    @foreach(\App\Models\OpeningHour::lines() as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                    <p class="bo-hint mt-3">Days with the same hours are grouped automatically.</p>
                </div>
            </div>
        </div>
    </form>
@endsection
