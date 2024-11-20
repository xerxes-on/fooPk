<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Resources\ClientNote as Resource;
use App\Http\Controllers\Controller;
use App\Models\ClientNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller for client notes.
 */
final class ClientNotesAdminController extends Controller
{
    /**
     * Store a note.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // TODO: make separate form request
        $this->validate(
            $request,
            [
                'clientId' => ['required', 'numeric', 'exists:users,id'],
                'noteText' => ['string', 'max:10000', 'required'],
            ]
        );
        $note = ClientNote::create(
            [
                'text'      => $request->noteText,
                'client_id' => $request->clientId,
                'author_id' => $request->user()->id,
            ]
        );

        $note->load(['author:id,name', 'client:id,first_name,last_name,email']);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'note' => new Resource($note)])
            : back()->with('success', true);
    }

    /**
     * Update a note.
     */
    public function update(Request $request, ClientNote $clientNote): RedirectResponse|JsonResponse
    {
        $this->validate($request, ['noteText' => ['string', 'max:10000', 'required']]);
        $clientNote->update(['text' => $request->noteText]);
        $clientNote->load(['author:id,name', 'client:id,first_name,last_name,email']);
        return $request->expectsJson()
            ? response()->json(['success' => true, 'note' => $clientNote])
            : back()->with('success', true);
    }

    /**
     * Remove a note.
     */
    public function destroy(ClientNote $clientNote): void
    {
        //
    }
}
