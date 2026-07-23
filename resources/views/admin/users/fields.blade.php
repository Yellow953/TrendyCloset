{{-- Staff form body, shared by the add and edit modals. --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="_user_id" value="{{ $user->id }}">

    <div class="flex flex-col gap-4 px-6 py-5">
        <x-admin.field name="name" label="Name" :value="$user->name" required />
        <x-admin.field name="email" label="Email" type="email" :value="$user->email" required
                       hint="What they sign in with." />
        <x-admin.field name="role" label="Role" :value="$user->role?->value" required
                       :options="collect(\App\Enums\UserRole::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()"
                       hint="Administrators also manage discount codes and staff. Staff can do everything else." />

        <div class="border-t border-slate-100 pt-4">
            <x-admin.field name="password" label="{{ $new ? 'Password' : 'New password' }}" type="password"
                           :required="$new" autocomplete="new-password"
                           :hint="$new ? 'At least the default strength.' : 'Leave blank to keep the current password.'" />
            <x-admin.field name="password_confirmation" label="Confirm password" type="password"
                           :required="$new" autocomplete="new-password" class="mt-4" />
        </div>
    </div>

    <div class="flex justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-6 py-4">
        <button type="button" data-modal-close class="ad-btn">Cancel</button>
        <button type="submit" class="ad-btn-primary">{{ $submit }}</button>
    </div>
</form>
