@extends('layouts.admin')

@php $editing = $slide->exists; @endphp

@section('title', $editing ? $slide->title : 'New slide')
@section('heading', $editing ? $slide->title : 'New slide')
@section('subheading', 'One slide of the home-page hero: a photograph on the right, four lines of copy and a button on the left.')

@section('breadcrumb')
    <a href="{{ route('admin.slides.index') }}" class="hover:text-slate-900">Home slider</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $editing ? 'Edit' : 'New' }}</span>
@endsection

@section('actions')
    @if($editing)
        <button type="button" data-modal-open="delete-slide" class="ad-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
    @endif
    <a href="{{ route('admin.slides.index') }}" class="ad-btn">Cancel</a>
    <button type="submit" form="slide-form" class="ad-btn-primary">{{ $editing ? 'Save changes' : 'Create slide' }}</button>
@endsection

@section('content')
    <form id="slide-form" method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.slides.update', $slide) : route('admin.slides.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

            <div class="flex flex-col gap-5">
                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Copy</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="eyebrow" label="Eyebrow" :value="$slide->eyebrow"
                                       placeholder="THE SALE IS LIVE"
                                       hint="The small spaced line above the headline. Written in capitals." />

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-admin.field name="title" label="Headline" :value="$slide->title" required
                                           placeholder="Up to 40% off" />

                            <x-admin.field name="accent" label="Second line" :value="$slide->accent"
                                           placeholder="your summer favourites"
                                           hint="Set in italic serif under the headline." />
                        </div>

                        <x-admin.field name="copy" label="Paragraph" type="textarea" :rows="3" :value="$slide->copy"
                                       placeholder="Marked-down pieces from every section — while sizes last."
                                       hint="One or two sentences. Long copy wraps awkwardly against the photograph." />
                    </div>
                </div>

                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Button</div></div>
                    <div class="grid grid-cols-1 gap-4 px-5 py-5 sm:grid-cols-2">
                        <x-admin.field name="cta_label" label="Button text" :value="$slide->cta_label"
                                       placeholder="Shop Sale" />

                        <x-admin.field name="cta_url" label="Button link" :value="$slide->cta_url"
                                       placeholder="/shop?edit=sale"
                                       hint="A path on this shop — /shop, /shop/dresses, /shop?edit=new, /shop?edit=sale — or a full URL. Blank sends shoppers to /shop." />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5">
                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Placement</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="position" label="Position" type="number" :value="$slide->position ?? 0" min="0"
                                       hint="Lower numbers play first." />

                        <x-admin.toggle name="is_active" label="Live on the home page" :checked="$slide->is_active ?? true"
                                        hint="Hidden slides are skipped by the rotation." />
                    </div>
                </div>

                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Photograph</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        @if($slide->image_url)
                            <div class="aspect-[7/6] overflow-hidden rounded-lg border border-slate-100 bg-slate-100">
                                <img src="{{ $slide->image_url }}" alt="" class="h-full w-full object-cover">
                            </div>
                        @endif

                        <label class="flex cursor-pointer flex-col items-center rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center transition-colors hover:border-slate-900">
                            <span class="text-[18px] text-slate-400" aria-hidden="true">⬆</span>
                            <span class="mt-1.5 text-[13px] font-medium">{{ $slide->image_url ? 'Replace photograph' : 'Choose a photograph' }}</span>
                            <span class="mt-1 text-[11.5px] font-normal text-slate-400">Fills the right half of the hero — upright or square, at least 1400px wide</span>
                            <input type="file" name="image" accept="image/*" class="hidden" data-upload="#slide-preview">
                        </label>

                        <div id="slide-preview" class="flex flex-wrap gap-2"></div>
                        @error('image')<p class="ad-error">{{ $message }}</p>@enderror

                        <x-admin.field name="image_credit" label="Photo credit" :value="$slide->image_credit"
                                       placeholder="Jane Doe / Unsplash" />
                        <x-admin.field name="image_credit_href" label="Credit link" type="url" :value="$slide->image_credit_href"
                                       placeholder="https://unsplash.com/@jane" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('modals')
    @if($editing)
        <x-admin.confirm id="delete-slide"
                         :action="route('admin.slides.destroy', $slide)"
                         :title="'Delete “'.$slide->title.'”?'"
                         confirm="Delete slide"
                         body="The slide and its uploaded photograph are removed from the home page straight away." />
    @endif
@endsection
