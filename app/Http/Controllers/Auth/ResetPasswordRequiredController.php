<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordRequiredController extends Controller
{
    public function show(Request $request): Response
    {
        $user  = $request->user();
        $token = Password::createToken($user);

        return Inertia::render('Auth/ResetPasswordRequired', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
