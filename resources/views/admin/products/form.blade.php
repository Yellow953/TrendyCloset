@extends('layouts.admin')

@php $editing = $product->exists; @endphp

@section('title', $editing ? $product->name : 'New product')
@section('heading', $editing ? $product->name : 'New product')
@section('subheading', $editing ? 'Last saved '.$product->updated_at->diffForHumans() : 'A piece needs a name, a category, a price and at least one size before the shop can sell it.')

@section('breadcrumb')
    <a href="{{ route('admin.products.index') }}" class="hover:text-slate-900">Products</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $editing ? 'Edit' : 'New' }}</span>
@endsection

@section('actions')
    @if($editing)
        <a href="{{ route('product', $product) }}" target="_blank" rel="noopener" class="bo-btn">View on shop ↗</a>
        <button type="button" data-modal-open="delete-product" class="bo-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
    @endif
    <a href="{{ route('admin.products.index') }}" class="bo-btn">Cancel</a>
    <button type="submit" form="product-form" class="bo-btn-primary">{{ $editing ? 'Save changes' : 'Create product' }}</button>
@endsection

@section('content')
    <form id="product-form" method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

            {{-- ------------------------------------------------ main column --}}
            <div class="flex flex-col gap-5">

                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">The piece</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="name" label="Name" :value="$product->name" required
                                       placeholder="Linen Wrap Dress" />

                        <x-admin.field name="slug" label="URL slug" :value="$product->slug"
                                       placeholder="linen-wrap-dress"
                                       hint="Leave blank and one is derived from the name. Changing it breaks existing links." />

                        <x-admin.field name="description" label="Description" type="textarea" :rows="6"
                                       :value="$product->description"
                                       placeholder="How it is cut, what it is made of, how it wears…"
                                       hint="This is what the product page and the AI-facing /llms.txt both quote. Write it as prose, not bullet points." />
                    </div>
                </div>

                {{-- Sizes and colours -------------------------------------- --}}
                @php $variants = old('variants', $editing ? $product->variants->map(fn ($v) => [
                    'id' => $v->id, 'sku' => $v->sku, 'size' => $v->size, 'color' => $v->color,
                    'price_override' => $v->price_override, 'stock' => $v->stock, 'is_active' => $v->is_active,
                ])->all() : []); @endphp

                <div class="bo-card" data-repeater data-repeater-next="{{ count($variants) + 50 }}">
                    <div class="bo-card-head">
                        <div>
                            <div class="bo-card-title">Sizes &amp; colours</div>
                            <p class="mt-0.5 text-[12px] font-normal text-slate-400">Stock lives here, not on the product. A piece with no rows cannot be added to a bag.</p>
                        </div>
                        <button type="button" data-repeater-add class="bo-btn bo-btn-sm">＋ Add row</button>
                    </div>

                    {{-- Shared by every row's colour field below (both the
                         rendered rows and the __INDEX__ template) — a
                         datalist suggests the swatches Swatch.php can paint,
                         but the field stays free text so a one-off colour
                         can still be typed in and saved as-is. --}}
                    <datalist id="swatch-colors">
                        @foreach(\App\Support\Swatch::names() as $color)
                            <option value="{{ $color }}">
                        @endforeach
                    </datalist>

                    <div class="overflow-x-auto">
                        <table class="bo-table">
                            <thead>
                                <tr>
                                    <th class="w-[110px]">Size</th>
                                    <th class="w-[140px]">Colour</th>
                                    <th class="w-[130px]">SKU</th>
                                    <th class="w-[110px]">Price override</th>
                                    <th class="w-[90px]">Stock</th>
                                    <th class="w-[70px]">Live</th>
                                    <th class="w-[50px]"></th>
                                </tr>
                            </thead>
                            <tbody data-repeater-rows>
                                @foreach($variants as $i => $variant)
                                    <tr data-repeater-row>
                                        <td class="px-5 py-2.5">
                                            <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id'] ?? '' }}">
                                            <select name="variants[{{ $i }}][size]" class="bo-input-sm">
                                                <option value=""></option>
                                                @foreach(\App\Models\ProductVariant::SIZES as $size)
                                                    <option value="{{ $size }}" @selected(($variant['size'] ?? '') === $size)>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-5 py-2.5">
                                            <input name="variants[{{ $i }}][color]" value="{{ $variant['color'] ?? '' }}"
                                                   list="swatch-colors" placeholder="Colour" class="bo-input-sm">
                                        </td>
                                        <td class="px-5 py-2.5"><input name="variants[{{ $i }}][sku]" value="{{ $variant['sku'] ?? '' }}" placeholder="TC-001-M" class="bo-input-sm"></td>
                                        <td class="px-5 py-2.5"><input name="variants[{{ $i }}][price_override]" value="{{ $variant['price_override'] ?? '' }}" type="number" step="0.01" min="0" placeholder="—" class="bo-input-sm"></td>
                                        <td class="px-5 py-2.5"><input name="variants[{{ $i }}][stock]" value="{{ $variant['stock'] ?? 0 }}" type="number" min="0" class="bo-input-sm"></td>
                                        <td class="px-5 py-2.5">
                                            <input type="hidden" name="variants[{{ $i }}][is_active]" value="0">
                                            <input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked($variant['is_active'] ?? true) class="h-4 w-4 accent-slate-900">
                                        </td>
                                        <td class="px-5 py-2.5 text-right">
                                            <button type="button" data-repeater-remove class="bo-btn bo-btn-sm text-rose-600 hover:border-rose-600" title="Remove row">✕</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div data-repeater-empty class="{{ count($variants) ? 'hidden' : '' }}">
                        <x-admin.empty icon="ruler" title="No sizes yet"
                                       body="Add a row for each size/colour combination you stock. Leave size blank for a colour-only piece, or colour blank for a size-only one." />
                    </div>

                    {{-- The row the Add button clones. `__INDEX__` is swapped for
                         a fresh number by initRepeater() in app.js. --}}
                    <template>
                        <tr data-repeater-row>
                            <td class="px-5 py-2.5">
                                <input type="hidden" name="variants[__INDEX__][id]" value="">
                                <select name="variants[__INDEX__][size]" class="bo-input-sm">
                                    <option value=""></option>
                                    @foreach(\App\Models\ProductVariant::SIZES as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-2.5">
                                <input name="variants[__INDEX__][color]" list="swatch-colors" placeholder="Colour" class="bo-input-sm">
                            </td>
                            <td class="px-5 py-2.5"><input name="variants[__INDEX__][sku]" placeholder="TC-001-M" class="bo-input-sm"></td>
                            <td class="px-5 py-2.5"><input name="variants[__INDEX__][price_override]" type="number" step="0.01" min="0" placeholder="—" class="bo-input-sm"></td>
                            <td class="px-5 py-2.5"><input name="variants[__INDEX__][stock]" type="number" min="0" value="0" class="bo-input-sm"></td>
                            <td class="px-5 py-2.5">
                                <input type="hidden" name="variants[__INDEX__][is_active]" value="0">
                                <input type="checkbox" name="variants[__INDEX__][is_active]" value="1" checked class="h-4 w-4 accent-slate-900">
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <button type="button" data-repeater-remove class="bo-btn bo-btn-sm text-rose-600 hover:border-rose-600" title="Remove row">✕</button>
                            </td>
                        </tr>
                    </template>
                </div>
            </div>

            {{-- ----------------------------------------------------- sidebar --}}
            <div class="flex flex-col gap-5">

                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">Visibility</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.toggle name="is_active" label="Live on the shop" :checked="$product->is_active ?? true"
                                        hint="Draft pieces are hidden from every listing, search result and sitemap." />
                        <x-admin.toggle name="is_featured" label="Feature on the home page" :checked="$product->is_featured ?? false"
                                        hint="Adds it to the Featured Products carousel." />
                    </div>
                </div>

                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">Pricing</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="price" label="Price" type="number" step="0.01" prefix="$"
                                       :value="$product->price" required placeholder="0.00" />

                        <x-admin.field name="compare_at_price" label="Compare-at price" type="number" step="0.01" prefix="$"
                                       :value="$product->compare_at_price" placeholder="0.00"
                                       hint="The struck-through “was” price. Set it above the price and the piece joins the Sale edit with a derived percentage badge." />

                        <x-admin.field name="sale_ends_at" label="Deal ends" type="datetime-local"
                                       :value="$product->sale_ends_at?->format('Y-m-d\TH:i')"
                                       hint="Only pieces with a future date appear in Deal of the Week, with a live countdown." />
                    </div>
                </div>

                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">Organise</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="category_id" label="Category" :options="$categories" required
                                       :value="$product->category_id"
                                       hint="File pieces on a leaf. Browsing a parent widens to everything beneath it." />

                        <x-admin.field name="badge" label="Badge" :value="$product->badge" placeholder="NEW"
                                       hint="The corner label on the card. “NEW” also puts the piece in the New in edit. Leave blank to let a sale percentage fill it." />

                        <x-admin.field name="rating" label="Editorial rating" type="number" :value="$product->rating"
                                       min="1" max="5"
                                       hint="Shown as stars. Editorial, not customer reviews — which is why no rating schema is emitted." />
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Images: its own full-width section, kept outside the main form because
         each existing photo's actions are their own small form (forms cannot
         nest) — the picker input instead points back at #product-form via its
         `form` attribute, so a chosen file still submits with the rest of the
         piece on Save. Everything sits in one horizontally-scrolling row. --}}
    <div class="bo-card mt-5">
        <div class="bo-card-head">
            <div>
                <div class="bo-card-title">Images</div>
                <p class="mt-0.5 text-[12px] font-normal text-slate-400">The primary image is what every product card and search result leads with.</p>
            </div>
            @if($editing)
                <span class="bo-badge bo-badge-neutral">{{ $product->images->count() }} {{ Str::plural('image', $product->images->count()) }}</span>
            @endif
        </div>

        <div class="flex gap-4 overflow-x-auto px-5 py-5">
            <label class="flex aspect-[4/5] w-[140px] shrink-0 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 text-center transition-colors hover:border-slate-900">
                <span class="text-[18px] text-slate-400" aria-hidden="true">⬆</span>
                <span class="mt-2 text-[12.5px] font-medium">Choose images</span>
                <span class="mt-1 text-[11px] font-normal text-slate-400">JPG, PNG, WebP or AVIF · up to 5 MB each</span>
                <input form="product-form" type="file" name="photos[]" accept="image/*" multiple class="hidden" data-upload="#photo-preview">
            </label>

            @if($editing)
                @foreach($product->images as $image)
                    <div class="w-[140px] shrink-0">
                        <div class="relative aspect-[4/5] overflow-hidden rounded-lg border {{ $image->is_primary ? 'border-slate-900 ring-2 ring-slate-900/20' : 'border-slate-100' }} bg-slate-100">
                            <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">

                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 rounded-full bg-slate-900 px-2 py-0.5 text-[10px] font-medium text-white">Primary</span>
                            @endif

                            @unless($image->disk_path)
                                <span class="absolute top-2 right-2 rounded-full bg-slate-800/75 px-2 py-0.5 text-[10px] font-normal text-white" title="A remote URL — deleting the row leaves the file alone">Linked</span>
                            @endunless
                        </div>

                        <div class="mt-2 flex items-center justify-between gap-1.5">
                            @unless($image->is_primary)
                                <form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-[11.5px] font-medium text-slate-900 hover:underline">Make primary</button>
                                </form>
                            @else
                                <span class="text-[11.5px] font-normal text-slate-400">Leads the gallery</span>
                            @endunless

                            <button type="button" data-modal-open="delete-image-{{ $image->id }}"
                                    class="text-[11.5px] font-medium text-rose-600 hover:underline">Remove</button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div id="photo-preview" class="{{ ($editing && $product->images->isNotEmpty()) ? 'border-t border-slate-100' : '' }} flex flex-wrap gap-2 px-5 {{ ($editing && $product->images->isNotEmpty()) ? 'py-4' : 'pb-5' }}"></div>

        <div class="px-5 pb-5">
            @error('photos.*')<p class="bo-error">{{ $message }}</p>@enderror

            <p class="bo-hint">
                @if($editing)
                    Newly chosen files are added to the row above when you save. The first image on a product with none becomes its primary one.
                @else
                    Uploads are attached once the product is created.
                @endif
            </p>
        </div>
    </div>
@endsection

@section('modals')
    @if($editing)
        <x-admin.confirm id="delete-product"
                         :action="route('admin.products.destroy', $product)"
                         :title="'Delete '.$product->name.'?'"
                         confirm="Delete product"
                         body="Its photographs, sizes and colours go with it. Orders already placed keep their own copy of the name and price, so sales history is not affected." />

        @foreach($product->images as $image)
            <x-admin.confirm :id="'delete-image-'.$image->id"
                             :action="route('admin.products.images.destroy', [$product, $image])"
                             title="Remove this photograph?"
                             confirm="Remove image"
                             :body="$image->disk_path
                                 ? 'The file is deleted from storage as well.'
                                 : 'This is a linked remote image, so only the catalogue row is removed.'" />
        @endforeach
    @endif
@endsection
