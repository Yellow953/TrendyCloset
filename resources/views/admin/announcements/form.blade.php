@extends('layouts.admin')

@php $editing = $announcement->exists; @endphp

@section('title', $editing ? 'Edit announcement' : 'New announcement')
@section('heading', $editing ? 'Edit announcement' : 'New announcement')
@section('subheading', 'One line of the pre-header bar. With more than one live, the bar rotates through them.')

@section('breadcrumb')
    <a href="{{ route('admin.announcements.index') }}" class="hover:text-slate-900">Announcements</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $editing ? 'Edit' : 'New' }}</span>
@endsection

@section('actions')
    @if($editing)
        <button type="button" data-modal-open="delete-announcement" class="bo-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
    @endif
    <a href="{{ route('admin.announcements.index') }}" class="bo-btn">Cancel</a>
    <button type="submit" form="announcement-form" class="bo-btn-primary">{{ $editing ? 'Save changes' : 'Create announcement' }}</button>
@endsection

@section('content')
    <form id="announcement-form" method="POST"
          action="{{ $editing ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

            <div class="flex flex-col gap-5">
                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">Message</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="message" label="Text" type="textarea" :rows="2" :value="$announcement->message" required
                                       placeholder="Up to 40% off your summer favourites — while sizes last"
                                       hint="Shown centred in the bar. Keep it to one short line." />

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-admin.field name="link_label" label="Link text" :value="$announcement->link_label"
                                           placeholder="Shop now" />

                            <x-admin.field name="link_url" label="Link" :value="$announcement->link_url"
                                           placeholder="/shop?edit=sale"
                                           hint="A path on this shop, or a full URL. Blank sends shoppers to /shop." />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5">
                <div class="bo-card">
                    <div class="bo-card-head"><div class="bo-card-title">Placement</div></div>
                    <div class="flex flex-col gap-4 px-5 py-5">
                        <x-admin.field name="position" label="Position" type="number" :value="$announcement->position ?? 0" min="0"
                                       hint="Lower numbers play first." />

                        <x-admin.toggle name="is_active" label="Live in the bar" :checked="$announcement->is_active ?? true"
                                        hint="Hidden announcements are skipped by the rotation." />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('modals')
    @if($editing)
        <x-admin.confirm id="delete-announcement"
                         :action="route('admin.announcements.destroy', $announcement)"
                         title="Delete this announcement?"
                         confirm="Delete announcement"
                         body="It is removed from the bar's rotation straight away." />
    @endif
@endsection
