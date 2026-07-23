@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'The tree behind the shop nav, the mega-menu and the listing sidebar. Products are filed on leaves; browsing a parent widens to everything beneath it.')

@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="ad-btn-primary">＋ New category</a>
@endsection

@section('content')
    @php
        // One row renderer, recursing down the tree so depth is a visual
        // indent rather than a separate screen per level.
        $row = function (\App\Models\Category $category, int $depth = 0) use (&$row) {
            return view('admin.categories.row', ['category' => $category, 'depth' => $depth, 'row' => $row]);
        };
    @endphp

    <div class="ad-card">
        @if($roots->isEmpty())
            <x-admin.empty icon="⋔" title="No categories yet"
                           body="The shop nav is built from this tree, so start with a top-level section such as “Winter” or “Dresses”.">
                <a href="{{ route('admin.categories.create') }}" class="ad-btn-primary">＋ New category</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="ad-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Products</th>
                            <th class="text-right">Position</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roots as $root)
                            {!! $row($root) !!}
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('modals')
    @php
        $flat = collect();
        $collect = function ($categories) use (&$collect, $flat) {
            foreach ($categories as $category) {
                $flat->push($category);
                $collect($category->children);
            }
        };
        $collect($roots);
    @endphp

    @foreach($flat as $category)
        <x-admin.confirm :id="'delete-category-'.$category->id"
                         :action="route('admin.categories.destroy', $category)"
                         :title="'Delete '.$category->name.'?'"
                         confirm="Delete category"
                         body="A category with products or subcategories under it cannot be deleted — move those first." />
    @endforeach
@endsection
