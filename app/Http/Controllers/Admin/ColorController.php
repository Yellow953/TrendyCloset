<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The colour list — what the admin product form's colour select and every
 * storefront swatch (via Swatch) are built from.
 */
class ColorController extends Controller
{
    public function index()
    {
        $usage = ProductVariant::query()
            ->whereNotNull('color')
            ->selectRaw('lower(color) as color, count(*) as c')
            ->groupBy('color')
            ->pluck('c', 'color');

        return view('admin.colors.index', [
            'active' => 'colors',
            'colors' => Color::ordered()->get(),
            'usage' => $usage,
        ]);
    }

    public function create()
    {
        return view('admin.colors.form', [
            'active' => 'colors',
            'color' => new Color(['is_active' => true, 'position' => 0]),
        ]);
    }

    public function store(Request $request)
    {
        $color = Color::create($this->validated($request));

        return redirect()
            ->route('admin.colors.edit', $color)
            ->with('status', $color->name.' created.');
    }

    public function edit(Color $color)
    {
        return view('admin.colors.form', [
            'active' => 'colors',
            'color' => $color,
        ]);
    }

    public function update(Request $request, Color $color)
    {
        $color->update($this->validated($request, $color));

        return back()->with('status', $color->name.' saved.');
    }

    public function destroy(Color $color)
    {
        if (ProductVariant::whereRaw('lower(color) = ?', [Str::lower($color->name)])->exists()) {
            return back()->withErrors([
                'color' => $color->name.' is still used by product variants. Deactivate it instead, or remove it from those variants first.',
            ]);
        }

        $name = $color->name;
        $color->delete();

        return redirect()
            ->route('admin.colors.index')
            ->with('status', $name.' deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Color $color = null): array
    {
        $request->merge(['name' => Str::title(trim((string) $request->input('name')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('colors', 'name')->ignore($color)],
            'hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['position'] = $data['position'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
