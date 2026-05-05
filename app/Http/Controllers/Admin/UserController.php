<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvite;
use App\Models\Cv;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UserController extends Controller
{
    public function index(): InertiaResponse
    {
        $users = User::where('role', 'candidate')->with('cv:id,user_id,ai_import_count')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => bcrypt(Str::random(32)),
            'role'                 => 'candidate',
            'must_change_password' => true,
            'email_verified_at'    => now(),
        ]);

        Cv::create([
            'user_id'    => $user->id,
            'status'     => 'draft',
            'first_name' => '',
            'last_name'  => '',
        ]);

        $token = Password::createToken($user);

        Mail::to($user->email)->send(new UserInvite($user, $token));

        return redirect()->back()->with('success', "Invito inviato a {$user->email}");
    }

    public function resetAiImport(User $user): RedirectResponse
    {
        abort_if(!$user->cv, 404, 'CV non trovato per questo utente.');

        $user->cv->update(['ai_import_count' => 0]);

        return redirect()->back()->with('success', "Contatore AI reset per {$user->name}.");
    }

    public function suspend(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Non puoi sospendere un amministratore.');

        $user->update(['is_suspended' => true]);

        return redirect()->back()->with('success', 'Utente sospeso con successo.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $user->update(['is_suspended' => false]);

        return redirect()->back()->with('success', 'Utente riattivato con successo.');
    }
}
