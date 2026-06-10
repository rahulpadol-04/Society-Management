<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreBlogRequest;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('blog.view'), 403);

        $posts = Blog::with('author')->latest()->get();

        return view('superadmin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        $this->authorize('create', Blog::class);

        return view('superadmin.blog.create');
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blog = Blog::create($data);

        return redirect()->route('blog.index')
            ->with('success', "Post \"{$blog->title}\" created.");
    }

    public function show(Blog $blog): View
    {
        $this->authorize('view', $blog);

        $blog->load('author');

        return view('superadmin.blog.show', compact('blog'));
    }

    public function edit(Blog $blog): View
    {
        $this->authorize('update', $blog);

        return view('superadmin.blog.edit', compact('blog'));
    }

    public function update(StoreBlogRequest $request, Blog $blog): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'published' && ! $blog->published_at) {
            $data['published_at'] = now();
        }

        $blog->update($data);

        return redirect()->route('blog.index')
            ->with('success', "Post \"{$blog->title}\" updated.");
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $this->authorize('delete', $blog);

        $blog->delete();

        return redirect()->route('blog.index')
            ->with('success', 'Post deleted.');
    }

    /** Toggle published / draft. */
    public function publish(Blog $blog): RedirectResponse
    {
        $this->authorize('publish', $blog);

        if ($blog->status === 'published') {
            $blog->update(['status' => 'draft', 'published_at' => null]);
            $message = "Post \"{$blog->title}\" unpublished.";
        } else {
            $blog->update(['status' => 'published', 'published_at' => now()]);
            $message = "Post \"{$blog->title}\" published.";
        }

        return redirect()->back()->with('success', $message);
    }
}
