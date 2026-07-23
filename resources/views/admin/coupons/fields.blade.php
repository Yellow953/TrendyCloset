{{-- The coupon form body, shared by the create and edit modals. `_coupon_id`
     lets the index re-open the right dialog after a validation failure. --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="_coupon_id" value="{{ $coupon->id }}">

    <div class="flex flex-col gap-4 px-6 py-5">
        <div class="grid grid-cols-2 gap-4">
            <x-admin.field name="code" label="Code" :value="$coupon->code" required placeholder="WELCOME10"
                           style="text-transform: uppercase" />
            <x-admin.field name="type" label="Type" :value="$coupon->type" required
                           :options="['percent' => 'Percentage off', 'fixed' => 'Fixed amount off']" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-admin.field name="value" label="Value" type="number" step="0.01" :value="$coupon->value" required
                           hint="A percent (0–100) or a dollar amount, matching the type." />
            <x-admin.field name="min_subtotal" label="Minimum spend" type="number" step="0.01" prefix="$"
                           :value="$coupon->min_subtotal" placeholder="0.00" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-admin.field name="starts_at" label="Starts" type="datetime-local"
                           :value="$coupon->starts_at?->format('Y-m-d\TH:i')" hint="Blank means live now." />
            <x-admin.field name="expires_at" label="Expires" type="datetime-local"
                           :value="$coupon->expires_at?->format('Y-m-d\TH:i')" hint="Blank means no expiry." />
        </div>

        <x-admin.field name="usage_limit" label="Usage limit" type="number" min="1" :value="$coupon->usage_limit"
                       placeholder="Unlimited" hint="Total number of orders that may use this code." />

        <div class="flex flex-col gap-3 border-t border-slate-100 pt-4">
            <x-admin.toggle name="free_shipping" label="Also gives free shipping" :checked="$coupon->free_shipping" />
            <x-admin.toggle name="is_active" label="Active" :checked="$coupon->is_active ?? true"
                            hint="Switch off to retire a code without deleting it." />
        </div>
    </div>

    <div class="flex justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
        <button type="button" data-modal-close class="ad-btn">Cancel</button>
        <button type="submit" class="ad-btn-primary">{{ $submit }}</button>
    </div>
</form>
