<?php

namespace App\Http\Controllers;

use App\Models\MemberIdCard;
use App\Services\MemberIdCardService;
use App\Services\QrService;
use Illuminate\Http\Request;

/**
 * Member digital ID card: view, PDF download, (re)issue and revoke.
 * The card shows business-safe data only — never salary, bank, ID numbers
 * or secrets. PDF download is ownership/role authorized.
 */
class IdCardController extends Controller
{
    public function __construct(private MemberIdCardService $cards, private QrService $qr)
    {
    }

    public function show()
    {
        $user = auth()->user()->load('activeIdCard');
        $card = $user->activeIdCard;
        // Signed token is recomputable server-side, so the QR renders on
        // every view and PDF; revocation is enforced at verification time.
        $qrUrl = $card ? $this->qr->dataUri($this->cards->verificationUrl($this->cards->tokenFor($card))) : null;

        return view('identity.card', compact('user', 'card', 'qrUrl'));
    }

    public function issue(Request $request)
    {
        $user = auth()->user();
        ['card' => $card, 'token' => $token] = $this->cards->issue($user, $user->isAdmin() ? $user : null);
        $qrUrl = $this->qr->dataUri($this->cards->verificationUrl($token));

        return view('identity.card', [
            'user' => $user->load('activeIdCard'), 'card' => $card, 'qrUrl' => $qrUrl,
        ])->with('success', "Card {$card->card_number} issued.");
    }

    public function pdf()
    {
        $user = auth()->user()->load('activeIdCard');
        $card = $user->activeIdCard;
        abort_unless($card && $card->isValid(), 404, 'No valid ID card.');
        $qrSvg = $this->qr->svg($this->cards->verificationUrl($this->cards->tokenFor($card)), 120);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('identity.card-pdf', ['user' => $user, 'card' => $card, 'qrSvg' => $qrSvg])
            ->setPaper('a4', 'portrait');

        return $pdf->download("member-id-{$user->member_number}.pdf");
    }

    public function revoke(Request $request, MemberIdCard $card)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $this->cards->revoke($card, auth()->user(), $data['reason']);

        return back()->with('success', "Card {$card->card_number} revoked.");
    }
}
