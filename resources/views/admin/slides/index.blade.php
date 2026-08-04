@extends('layouts.admin')

@section('title', 'Home slider')
@section('heading', 'Home slider')
@section('subheading', 'The rotating hero at the top of the home page. Slides play in position order; hidden slides are skipped.')

@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="ad-btn">View home ↗</a>
    <a href="{{ route('admin.slides.create') }}" class="ad-btn-primary">＋ New slide</a>
@endsection

@section('content')
    <div class="ad-card">
        @if($slides->isEmpty())
            <x-admin.empty icon="slides" title="No slides yet"
                           body="With no slides the home page falls back to the three it shipped with. Add one to take it over.">
                <a href="{{ route('admin.slides.create') }}" class="ad-btn-primary">＋ New slide</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Slide</th>
                            <th>Button</th>
                            <th class="text-right">Position</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slides as $slide)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="h-11 w-16 shrink-0 overflow-hidden rounded-md border border-slate-100 bg-slate-100">
                                            @if($slide->image_url)
                                                <img src="{{ $slide->image_url }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            @if($slide->eyebrow)
                                                <span class="block truncate text-[11px] font-semibold tracking-[0.08em] text-slate-400 uppercase">{{ $slide->eyebrow }}</span>
                                            @endif
                                            <a href="{{ route('admin.slides.edit', $slide) }}" class="block truncate font-medium hover:text-slate-900">
                                                {{ $slide->title }} <span class="font-normal text-slate-400">{{ $slide->accent }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="block truncate text-[13px]">{{ $slide->cta_label ?: '—' }}</span>
                                    <span class="mt-0.5 block max-w-[240px] truncate text-[11.5px] font-normal text-slate-400">{{ $slide->cta_url ?: '/shop' }}</span>
                                </td>

                                <td class="ad-figure text-right font-normal text-slate-400">{{ $slide->position }}</td>

                                <td>
                                    <form method="POST" action="{{ route('admin.slides.toggle', $slide) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="ad-badge {{ $slide->is_active ? 'ad-badge-good' : 'ad-badge-neutral' }}"
                                                title="{{ $slide->is_active ? 'Hide this slide' : 'Show this slide' }}">
                                            {{ $slide->is_active ? 'Live' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.slides.edit', $slide) }}" class="ad-btn ad-btn-sm">Edit</a>
                                        <button type="button" data-modal-open="delete-slide-{{ $slide->id }}"
                                                class="ad-btn ad-btn-sm text-rose-600 hover:border-rose-600 hover:text-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($slides->isNotEmpty() && $slides->every(fn ($slide) => ! $slide->is_active))
        <p class="mt-4 text-[13px] text-slate-500">
            Every slide is hidden, so the home page is showing the three slides it shipped with. Set one live to take the hero over.
        </p>
    @endif
@endsection

@section('modals')
    @foreach($slides as $slide)
        <x-admin.confirm :id="'delete-slide-'.$slide->id"
                         :action="route('admin.slides.destroy', $slide)"
                         :title="'Delete “'.$slide->title.'”?'"
                         confirm="Delete slide"
                         body="The slide and its uploaded photograph are removed from the home page straight away." />
    @endforeach
@endsection
