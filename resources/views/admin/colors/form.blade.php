@extends('layouts.admin')

@php $editing = $color->exists; @endphp

@section('title', $editing ? $color->name : 'New colour')
@section('heading', $editing ? $color->name : 'New colour')
@section('subheading', 'Colours show up wherever a variant does — the filter rail, the product page swatches and the admin size/colour rows.')

@section('breadcrumb')
    <a href="{{ route('admin.colors.index') }}" class="hover:text-slate-900">Colors</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $editing ? 'Edit' : 'New' }}</span>
@endsection

@section('actions')
    @if($editing)
        <button type="button" data-modal-open="delete-color" class="bo-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
    @endif
    <a href="{{ route('admin.colors.index') }}" class="bo-btn">Cancel</a>
    <button type="submit" form="color-form" class="bo-btn-primary">{{ $editing ? 'Save changes' : 'Create colour' }}</button>
@endsection

@section('content')
    <form id="color-form" method="POST"
          action="{{ $editing ? route('admin.colors.update', $color) : route('admin.colors.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">
            <div class="bo-card">
                <div class="bo-card-head"><div class="bo-card-title">Details</div></div>
                <div class="flex flex-col gap-4 px-5 py-5">
                    <x-admin.field name="name" label="Name" :value="$color->name" required placeholder="Dusty Rose" />

                    <div>
                        <label class="bo-label">Hex <span class="font-normal text-slate-400">· required</span></label>
                        <div class="flex items-center gap-2" data-color-sync>
                            <input type="color" data-color-sync-picker value="{{ old('hex', $color->hex ?: '#d8cec7') }}"
                                   class="h-9 w-11 shrink-0 cursor-pointer rounded border border-slate-200 bg-white p-0.5"
                                   aria-label="Pick a colour">
                            <input type="text" name="hex" data-color-sync-text value="{{ old('hex', $color->hex) }}"
                                   placeholder="#d8cec7" class="bo-input-sm flex-1 font-mono">
                        </div>
                        @error('hex')
                            <p class="bo-error">{{ $message }}</p>
                        @else
                            <p class="bo-hint">Pick a colour, or paste/tweak the hex directly — they stay in sync.</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-head"><div class="bo-card-title">Placement</div></div>
                <div class="flex flex-col gap-4 px-5 py-5">
                    <x-admin.field name="position" label="Position" type="number" :value="$color->position ?? 0" min="0"
                                   hint="Lower numbers come first in the colour select; ties fall back to alphabetical." />

                    <x-admin.toggle name="is_active" label="Selectable on new variants" :checked="$color->is_active ?? true"
                                     hint="Deactivating hides it from the picker for new variants but keeps existing products that already use it working." />
                </div>
            </div>
        </div>
    </form>
@endsection

@section('modals')
    @if($editing)
        <x-admin.confirm id="delete-color"
                         :action="route('admin.colors.destroy', $color)"
                         :title="'Delete '.$color->name.'?'"
                         confirm="Delete colour"
                         body="A colour still used by a product variant cannot be deleted — deactivate it instead." />
    @endif
@endsection
