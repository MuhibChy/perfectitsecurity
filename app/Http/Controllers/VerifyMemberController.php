<?php

namespace App\Http\Controllers;

use App\Services\MemberIdCardService;
use Illuminate\Http\Request;

/**
 * Public QR verification page. Minimal disclosure only: name, member ID,
 * role, account/identity/card status. Never address, documents, contact
 * details, financial data or secrets.
 */
class VerifyMemberController extends Controller
{
    public function show(Request $request, string $token, MemberIdCardService $cards)
    {
        abort_if(strlen($token) > 128, 404);
        $result = $cards->verifyToken($token);

        return view('verify.member', ['result' => $result]);
    }
}
