<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The category tree — what the storefront nav, the mega-menu and the listing
 * sidebar are all built from.
 */
class CategoryController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    public function index()
    {
        return view('admin.categories.index', [
            'active' => 'categories',
            'roots' => Category::with(['children.children', 'children.products', 'products'])
                ->roots()
                ->ordered()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'active' => 'categories',
            'category' => new Category(['is_active' => true, 'position' => 0]),
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $category = Category::create($this->validated($request));

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', $category->name.' created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'active' => 'categories',
            'category' => $category,
            'parents' => $this->parentOptions($category),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('status', $category->name.' saved.');
    }

    public function destroy(Category $category)
    {
        // Products are filed against categories, so emptying has to be
        // deliberate — refuse rather than silently orphan the catalogue.
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => $category->name.' still has products filed under it. Move them first.',
            ]);
        }

        if ($category->children()->exists()) {
            return back()->withErrors([
                'category' => $category->name.' still has subcategories. Delete or reparent them first.',
            ]);
        }

        $this->images->forget($category->image_path);
        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', $name.' deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category)],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$category?->id])],
            'description' => ['nullable', 'string', 'max:2000'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => array_merge(['nullable'], ImageStore::RULES),
            'image_credit' => ['nullable', 'string', 'max:255'],
            'image_credit_href' => ['nullable', 'url', 'max:2048'],
        ], [
            'parent_id.not_in' => 'A category cannot be its own parent.',
        ]);

        $image = $data['image'] ?? null;
        unset($data['image']);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['name'], $category);
        $data['position'] = $data['position'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->forget($category?->image_path);
            $stored = $this->images->store($request->file('image'), 'categories');
            $data['image_url'] = $stored['url'];
            $data['image_path'] = $stored['path'];
        }

        return $data;
    }

    private function uniqueSlug(string $name, ?Category $category): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $n = 2;

        while (Category::where('slug', $slug)->when($category, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /**
     * Categories that may act as a parent. A category cannot be parented to
     * itself or to one of its own descendants, or the tree stops being a tree.
     *
     * @return array<int, string>
     */
    private function parentOptions(?Category $category = null): array
    {
        $forbidden = $category ? $category->selfAndDescendantIds() : [];

        return Category::with('parent')
            ->ordered()
            ->get()
            ->reject(fn (Category $c) => in_array($c->id, $forbidden, true))
            ->mapWithKeys(fn (Category $c) => [
                $c->id => $c->parent ? $c->parent->name.' — '.$c->name : $c->name,
            ])
            ->all();
    }
}
