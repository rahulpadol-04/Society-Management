<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreCmsPageRequest;
use App\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('cms.view'), 403);

        $pages = CmsPage::latest()->get();

        return view('superadmin.cms.index', compact('pages'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsPage::class);

        return view('superadmin.cms.create');
    }

    public function store(StoreCmsPageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $page = CmsPage::create($data);

        return redirect()->route('cms.index')
            ->with('success', "Page \"{$page->title}\" created.");
    }

    public function show(CmsPage $cmsPage): View
    {
        $this->authorize('view', $cmsPage);

        return view('superadmin.cms.show', ['page' => $cmsPage]);
    }

    public function edit(CmsPage $cmsPage): View
    {
        $this->authorize('update', $cmsPage);

        return view('superadmin.cms.edit', ['page' => $cmsPage]);
    }

    public function update(StoreCmsPageRequest $request, CmsPage $cmsPage): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'published' && ! $cmsPage->published_at) {
            $data['published_at'] = now();
        }

        $cmsPage->update($data);

        return redirect()->route('cms.index')
            ->with('success', "Page \"{$cmsPage->title}\" updated.");
    }

    public function destroy(CmsPage $cmsPage): RedirectResponse
    {
        $this->authorize('delete', $cmsPage);

        $cmsPage->delete();

        return redirect()->route('cms.index')
            ->with('success', 'Page deleted.');
    }

    /** Toggle published / draft status. */
    public function publish(CmsPage $cmsPage): RedirectResponse
    {
        $this->authorize('publish', $cmsPage);

        if ($cmsPage->status === 'published') {
            $cmsPage->update(['status' => 'draft', 'published_at' => null]);
            $message = "Page \"{$cmsPage->title}\" unpublished.";
        } else {
            $cmsPage->update(['status' => 'published', 'published_at' => now()]);
            $message = "Page \"{$cmsPage->title}\" published.";
        }

        return redirect()->back()->with('success', $message);
    }
}
