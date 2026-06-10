<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SocietyProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        abort_unless($request->user()->can('society-profile.view'), 403);

        if (! current_society()) {
            return redirect()->route('dashboard')
                ->with('info', 'Select or impersonate a society to manage its profile.');
        }

        return view('structure.profile', ['society' => current_society()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('society-profile.update'), 403);

        $society = current_society();

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'email'               => ['nullable', 'email', 'max:150'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'address_line1'       => ['nullable', 'string', 'max:200'],
            'address_line2'       => ['nullable', 'string', 'max:200'],
            'city'                => ['nullable', 'string', 'max:100'],
            'state'               => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'max:100'],
            'postal_code'         => ['nullable', 'string', 'max:12'],
            'timezone'            => ['nullable', 'string', 'max:64'],
            'logo'                => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($society->logo) {
                Storage::disk('public')->delete($society->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos/'.$society->id, 'public');
        }

        $society->update($data);

        return back()->with('success', 'Society profile updated.');
    }
}
