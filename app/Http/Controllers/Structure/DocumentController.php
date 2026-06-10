<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Http\Requests\Structure\StoreDocumentRequest;
use App\Models\SocietyDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SocietyDocument::class);

        return view('structure.documents.index', [
            'documents' => SocietyDocument::with('uploader')->latest()->get(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('documents/'.current_society_id(), 'public');

        SocietyDocument::create([
            'title'       => $request->validated('title'),
            'category'    => $request->validated('category'),
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getClientMimeType(),
            'size'        => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'is_public'   => $request->boolean('is_public'),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function download(SocietyDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(SocietyDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
