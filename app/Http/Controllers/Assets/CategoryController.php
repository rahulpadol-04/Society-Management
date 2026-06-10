<?php

declare(strict_types=1);

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\StoreAssetCategoryRequest;
use App\Http\Requests\Assets\UpdateAssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AssetCategory::class);

        $categories = AssetCategory::withCount('assets')->latest()->get();

        return view('assets.categories.index', compact('categories'));
    }

    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        AssetCategory::create($request->validated());

        return redirect()->route('assets.categories.index')
            ->with('success', 'Category created.');
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return redirect()->route('assets.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('assets.categories.index')
            ->with('success', 'Category deleted.');
    }
}
