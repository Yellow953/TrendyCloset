@extends('layouts.admin')

@php $editing = $category->exists; @endphp

@section('title', $editing ? $category->name : 'New category')
@section('heading', $editing ? $category->name : 'New category')
@section('subheading', 'Categories are what the shop nav, the mega-menu and the listing sidebar are built from.')

@section('breadcrumb')
    <a href="{{ route('admin.categories.index') }}" class="hover:text-slate-900">Categories</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $editing ? 'Edit' : 'New' }}</span>
@endsection

@section('actions')
    @if($editing)
        <a href="{{ route('listing', $category) }}" target="_blank" rel="noopener" class="ad-btn">View on shop ↗</a>
        <button type="button" data-modal-open="delete-category" class="ad-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
    @endif
    <a href="{{ route('admin.categories.index') }}" class="ad-btn">Cancel</a>
    <button type="submit" form="category-form" class="ad-btn-primary">{{ $editing ? 'Save changes' : 'Create category' }}</button>
@endsection

@section('content')
    <form id="category-form" method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

            <div class="flex flex-col gap-5">
                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Details</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="name" label="Name" :value="$category->name" required placeholder="Dresses" />

                        <x-admin.field name="slug" label="URL slug" :value="$category->slug" placeholder="dresses"
                                       hint="The category lives at /shop/{slug}. Leave blank to derive it from the name." />

                        <x-admin.field name="description" label="Description" type="textarea" :rows="5"
                                       :value="$category->description"
                                       hint="Shown at the top of the listing and quoted in /llms.txt, so write it as a sentence someone would say." />
                    </div>
                </div>

                @if($editing && $category->products()->exists())
                    <div class="ad-card">
                        <div class="ad-card-head">
                            <div class="ad-card-title">Filed here</div>
                            <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-[12px] font-medium text-slate-900 hover:underline">
                                All {{ $category->products()->count() }}
                            </a>
                        </div>
                        <div class="flex flex-col divide-y divide-slate-100">
                            @foreach($category->products()->with('images')->limit(6)->get() as $p)
                                <a href="{{ route('admin.products.edit', $p) }}" class="flex items-center gap-3 px-5 py-3 transition-colors hover:bg-slate-50">
                                    <div class="h-10 w-8 shrink-0 overflow-hidden rounded border border-slate-100 bg-slate-100">
                                        @if($p->image_url)<img src="{{ $p->image_url }}" alt="" class="h-full w-full object-cover">@endif
                                    </div>
                                    <span class="flex-1 truncate text-[13px]">{{ $p->name }}</span>
                                    <span class="ad-figure text-[12.5px] font-medium">{{ $p->price_label }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-5">
                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Placement</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="parent_id" label="Parent" :options="$parents" :value="$category->parent_id"
                                       hint="Leave blank for a top-level section. A category cannot be parented to itself or to anything beneath it." />

                        <x-admin.field name="position" label="Position" type="number" :value="$category->position ?? 0" min="0"
                                       hint="Lower numbers come first; ties fall back to alphabetical." />

                        <x-admin.toggle name="is_active" label="Live on the shop" :checked="$category->is_active ?? true"
                                        hint="Hidden categories drop out of the nav and the sitemap." />
                    </div>
                </div>

                <div class="ad-card">
                    <div class="ad-card-head"><div class="ad-card-title">Image</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        @if($category->image_url)
                            <div class="aspect-[4/3] overflow-hidden rounded-lg border border-slate-100 bg-slate-100">
                                <img src="{{ $category->image_url }}" alt="" class="h-full w-full object-cover">
                            </div>
                        @endif

                        <label class="flex cursor-pointer flex-col items-center rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center transition-colors hover:border-slate-900">
                            <span class="text-[18px] text-slate-400" aria-hidden="true">⬆</span>
                            <span class="mt-1.5 text-[13px] font-medium">{{ $category->image_url ? 'Replace image' : 'Choose an image' }}</span>
                            <span class="mt-1 text-[11.5px] font-normal text-slate-400">Used by the category circles on the home page</span>
                            <input type="file" name="image" accept="image/*" class="hidden" data-upload="#category-preview">
                        </label>

                        <div id="category-preview" class="flex flex-wrap gap-2"></div>
                        @error('image')<p class="ad-error">{{ $message }}</p>@enderror

                        <x-admin.field name="image_credit" label="Photo credit" :value="$category->image_credit"
                                       placeholder="Jane Doe / Unsplash" />
                        <x-admin.field name="image_credit_href" label="Credit link" type="url" :value="$category->image_credit_href"
                                       placeholder="https://unsplash.com/@jane" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('modals')
    @if($editing)
        <x-admin.confirm id="delete-category"
                         :action="route('admin.categories.destroy', $category)"
                         :title="'Delete '.$category->name.'?'"
                         confirm="Delete category"
                         body="A category with products or subcategories under it cannot be deleted — move those first." />
    @endif
@endsection
