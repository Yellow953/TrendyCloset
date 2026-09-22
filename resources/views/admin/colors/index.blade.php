@extends('layouts.admin')

@section('title', 'Colors')
@section('heading', 'Colors')
@section('subheading', 'The colours a product\'s variants can be built from — every storefront swatch reads its hex from here.')

@section('actions')
    <a href="{{ route('admin.colors.create') }}" class="bo-btn-primary">＋ New colour</a>
@endsection

@section('content')
    <div class="bo-card">
        @if($colors->isEmpty())
            <x-admin.empty icon="colors" title="No colours yet"
                           body="A product's size/colour rows pick from this list, so add at least one before building variants.">
                <a href="{{ route('admin.colors.create') }}" class="bo-btn-primary">＋ New colour</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="bo-table">
                    <thead>
                        <tr>
                            <th>Colour</th>
                            <th>Hex</th>
                            <th class="text-right">Used by</th>
                            <th class="text-right">Position</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colors as $color)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-swatch :color="$color->name" />
                                        <a href="{{ route('admin.colors.edit', $color) }}" class="font-medium hover:text-slate-900">{{ $color->name }}</a>
                                    </div>
                                </td>
                                <td class="bo-figure font-mono text-slate-500">{{ $color->hex }}</td>
                                <td class="bo-figure text-right font-normal text-slate-600">{{ $usage[\Illuminate\Support\Str::lower($color->name)] ?? 0 }}</td>
                                <td class="bo-figure text-right font-normal text-slate-400">{{ $color->position }}</td>
                                <td>
                                    <span class="bo-badge {{ $color->is_active ? 'bo-badge-good' : 'bo-badge-neutral' }}">
                                        {{ $color->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.colors.edit', $color) }}" class="bo-btn bo-btn-sm">Edit</a>
                                        <button type="button" data-modal-open="delete-color-{{ $color->id }}"
                                                class="bo-btn bo-btn-sm text-rose-600 hover:border-rose-600 hover:text-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('modals')
    @foreach($colors as $color)
        <x-admin.confirm :id="'delete-color-'.$color->id"
                         :action="route('admin.colors.destroy', $color)"
                         :title="'Delete '.$color->name.'?'"
                         confirm="Delete colour"
                         body="A colour still used by a product variant cannot be deleted — deactivate it instead." />
    @endforeach
@endsection
