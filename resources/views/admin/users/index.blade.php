@extends('layouts.admin')

@section('title', 'Staff')
@section('heading', 'Staff')
@section('subheading', 'Who may sign in to the back office. Accounts are created here — the storefront has no registration, and customers are not users.')

@section('actions')
    <button type="button" data-modal-open="user-new" class="ad-btn-primary">＋ Add staff</button>
@endsection

@section('content')
    <div class="ad-card">
        <div class="overflow-x-auto">
            <table class="ad-table">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Added</th><th class="text-right"></th></tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[12px] font-medium text-slate-900">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </span>
                                    <span class="font-medium">{{ $user->name }}</span>
                                    @if($user->is(auth()->user()))
                                        <span class="ad-badge ad-badge-neutral">You</span>
                                    @endif
                                </div>
                            </td>
                            <td class="font-normal text-slate-600">{{ $user->email }}</td>
                            <td>
                                <span class="ad-badge {{ $user->isAdmin() ? 'border-slate-900/35 bg-slate-900/10 text-slate-900' : 'ad-badge-neutral' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="font-normal whitespace-nowrap text-slate-400">{{ $user->created_at->format('j M Y') }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" data-modal-open="user-{{ $user->id }}" class="ad-btn ad-btn-sm">Edit</button>
                                    @unless($user->is(auth()->user()))
                                        <button type="button" data-modal-open="delete-user-{{ $user->id }}" class="ad-btn ad-btn-sm text-rose-600 hover:border-rose-600" title="Remove">✕</button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('modals')
    <x-admin.modal id="user-new" title="Add staff" subtitle="They can sign in as soon as you save."
                   :autoopen="$errors->any() && ! old('_user_id')">
        @include('admin.users.fields', ['user' => new \App\Models\User(['role' => \App\Enums\UserRole::Staff]), 'action' => route('admin.users.store'), 'method' => 'POST', 'submit' => 'Create account', 'new' => true])
    </x-admin.modal>

    @foreach($users as $user)
        <x-admin.modal :id="'user-'.$user->id" :title="'Edit '.$user->name"
                       :autoopen="$errors->any() && old('_user_id') == $user->id">
            @include('admin.users.fields', ['user' => $user, 'action' => route('admin.users.update', $user), 'method' => 'PUT', 'submit' => 'Save', 'new' => false])
        </x-admin.modal>

        @unless($user->is(auth()->user()))
            <x-admin.confirm :id="'delete-user-'.$user->id"
                             :action="route('admin.users.destroy', $user)"
                             :title="'Remove '.$user->name.'?'"
                             confirm="Remove account"
                             body="They lose access immediately. This cannot be undone." />
        @endunless
    @endforeach
@endsection
