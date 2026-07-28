<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Services\ImageStore;
use Illuminate\Http\Request;

/**
 * The rotating hero on the home page — the first thing a shopper sees, so it
 * is merchandising rather than code.
 */
class HeroSlideController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    public function index()
    {
        return view('admin.slides.index', [
            'active' => 'slides',
            'slides' => HeroSlide::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.slides.form', [
            'active' => 'slides',
            'slide' => new HeroSlide([
                'is_active' => true,
                'position' => (int) HeroSlide::query()->max('position') + 1,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $slide = HeroSlide::create($this->validated($request));

        return redirect()
            ->route('admin.slides.edit', $slide)
            ->with('status', 'Slide created.');
    }

    public function edit(HeroSlide $slide)
    {
        return view('admin.slides.form', [
            'active' => 'slides',
            'slide' => $slide,
        ]);
    }

    public function update(Request $request, HeroSlide $slide)
    {
        $slide->update($this->validated($request, $slide));

        return back()->with('status', 'Slide saved.');
    }

    /** Publish / unpublish from the list, without opening the slide. */
    public function toggle(HeroSlide $slide)
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return back()->with('status', $slide->is_active ? 'Slide is live.' : 'Slide hidden.');
    }

    public function destroy(HeroSlide $slide)
    {
        $this->images->forget($slide->image_path);
        $slide->delete();

        return redirect()
            ->route('admin.slides.index')
            ->with('status', 'Slide deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?HeroSlide $slide = null): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'accent' => ['nullable', 'string', 'max:120'],
            'copy' => ['nullable', 'string', 'max:400'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            // Relative paths are the normal answer here (/shop?edit=sale), so
            // this cannot be validated as a URL.
            'cta_url' => ['nullable', 'string', 'max:2048'],
            // The photo is the slide — a new one cannot be saved without it.
            'image' => array_merge([$slide?->image_url ? 'nullable' : 'required'], ImageStore::RULES),
            'image_credit' => ['nullable', 'string', 'max:255'],
            'image_credit_href' => ['nullable', 'url', 'max:2048'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.required' => 'A slide needs a photograph.',
        ]);

        unset($data['image']);

        $data['position'] = $data['position'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->forget($slide?->image_path);
            $stored = $this->images->store($request->file('image'), 'hero');
            $data['image_url'] = $stored['url'];
            $data['image_path'] = $stored['path'];
        }

        return $data;
    }
}
