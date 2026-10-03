<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DirectoryService;

/**
 * Customer-facing contact directory (§11): support-team cards only.
 * Direct phone/whatsapp/email unlocks solely with a working relationship
 * (assignment / ticket / project / order link); staff viewers bypass.
 */
class DirectoryController extends Controller
{
    public function __construct(private DirectoryService $directory) {}

    public function index()
    {
        $staff = $this->directory->staffCards(auth()->user());
        return view('customer.directory.index', compact('staff'));
    }

    public function show(User $user)
    {
        abort_if($user->isCustomer(), 404);
        abort_unless($user->is_active, 404);
        $viewer = auth()->user();
        $card = $this->directory->publicCard($user);
        $contact = $this->directory->canSeeDirectContact($viewer, $user)
            ? $this->directory->directContact($user) : null;
        return view('customer.directory.show', ['staff' => $user, 'card' => $card, 'contact' => $contact]);
    }
}
