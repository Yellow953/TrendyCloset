{{-- The offer form body, shared by the create and edit modals. `_offer_id`
     lets the index re-open the right dialog after a validation failure.
     `data-toggle-field="type"` (see initFieldToggles in admin.js) shows only
     the field groups the chosen type actually reads. --}}
<form method="POST" action="{{ $action }}" data-toggle-field="type">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="_offer_id" value="{{ $offer->id }}">

    <div class="flex flex-col gap-4 px-6 py-5">
        <div class="grid grid-cols-2 gap-4">
            <x-admin.field name="name" label="Name" :value="$offer->name" required placeholder="Spring BOGO"
                           hint="Internal label — shoppers never see this." />
            <x-admin.field name="type" label="Type" :value="$offer->type?->value" required
                           :options="collect(\App\Enums\OfferType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])" />
        </div>

        <x-admin.field name="headline" label="Customer-facing text" :value="$offer->headline"
                       placeholder="Auto-generated from the rule below"
                       hint="Shown on the header banner, the product badge and the bag. Leave blank to generate one from the rule." />

        {{-- Spend threshold --}}
        <div data-toggle-when="spend" class="flex flex-col gap-4 border-t border-slate-100 pt-4">
            <div class="grid grid-cols-2 gap-4">
                <x-admin.field name="discount_type" label="Discount" :value="$offer->discount_type"
                               :options="['percent' => 'Percentage off', 'fixed' => 'Fixed amount off']" />
                <x-admin.field name="min_subtotal" label="Minimum spend" type="number" step="0.01" prefix="$"
                               :value="$offer->min_subtotal" placeholder="0.00" hint="Blank applies to every order." />
            </div>
            <x-admin.toggle name="free_shipping" label="Also gives free shipping" :checked="$offer->free_shipping" />
        </div>

        {{-- Category % off and Buy X Get Y share the same scope: a category
             (widened to its subcategories) or a hand-picked set of products.
             The nested data-toggle-field further gates on which is chosen —
             nesting rather than a single condition, since it needs the AND of
             "type is one of these two" and "scope is this one". --}}
        <div data-toggle-when="category_percent,bogo" class="flex flex-col gap-4 border-t border-slate-100 pt-4">
            <div data-toggle-field="scope" class="flex flex-col gap-4">
                <x-admin.field name="scope" label="Applies to" required
                               :value="$offer->products->isNotEmpty() ? 'products' : 'category'"
                               :options="['category' => 'A category', 'products' => 'Specific products']" />

                <div data-toggle-when="category">
                    <x-admin.field name="category_id" label="Category" :value="$offer->category_id"
                                   :options="$categoryOptions"
                                   hint="Required for Category % off. Leave blank on Buy X Get Y to cover the whole shop." />
                </div>

                <div data-toggle-when="products">
                    <label for="products" class="bo-label">Products</label>
                    <select id="products" name="products[]" multiple size="6" class="bo-input h-auto">
                        @foreach($productOptions as $id => $name)
                            <option value="{{ $id }}" @selected(in_array($id, old('products', $offer->products->pluck('id')->all())))>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('products')
                        <p class="bo-error">{{ $message }}</p>
                    @else
                        <p class="bo-hint">⌘/Ctrl-click to select more than one.</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Percent value: Spend threshold (as a percent or fixed amount,
             per the Discount field above) and Category % off. --}}
        <div data-toggle-when="spend,category_percent">
            <x-admin.field name="value" label="Value" type="number" step="0.01" :value="$offer->value"
                           hint="A percent (0–100), or — for Spend threshold set to Fixed amount — a dollar amount." />
        </div>

        {{-- Buy X Get Y --}}
        <div data-toggle-when="bogo" class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
            <x-admin.field name="buy_qty" label="Buy" type="number" min="1" :value="$offer->buy_qty ?? 2"
                           hint="Qualifying items the shopper must buy…" />
            <x-admin.field name="get_qty" label="Get free" type="number" min="1" :value="$offer->get_qty ?? 1"
                           hint="…before this many more are free." />
        </div>

        <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
            <x-admin.field name="starts_at" label="Starts" type="datetime-local"
                           :value="$offer->starts_at?->format('Y-m-d\TH:i')" hint="Blank means live now." />
            <x-admin.field name="expires_at" label="Expires" type="datetime-local"
                           :value="$offer->expires_at?->format('Y-m-d\TH:i')" hint="Blank means no expiry." />
        </div>

        <x-admin.toggle name="is_active" label="Active" :checked="$offer->is_active ?? true"
                        hint="Switch off to retire an offer without deleting it." />
    </div>

    <div class="flex justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
        <button type="button" data-modal-close class="bo-btn">Cancel</button>
        <button type="submit" class="bo-btn-primary">{{ $submit }}</button>
    </div>
</form>
