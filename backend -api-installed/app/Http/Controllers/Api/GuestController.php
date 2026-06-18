<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    public function initSession()
    {
        $sessionId = 'guest_' . Str::random(32);

        return response()->json([
            'session_id' => $sessionId,
            'message' => 'Guest session initialized',
        ]);
    }
}
