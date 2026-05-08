<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CvAdminController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = User::with('cv')->where('role', 'candidate');

        if ($request->filled('status')) {
            $query->whereHas('cv', fn($q) => $q->where('status', $request->input('status')));
        }

        $counts = [
            'draft'    => Cv::where('status', 'draft')->count(),
            'locked'   => Cv::where('status', 'locked')->count(),
            'archived' => Cv::where('status', 'archived')->count(),
        ];

        $users = $query->orderBy('name')->get()->map(fn(User $u) => [
            'cv_id'      => $u->cv?->id,
            'status'     => $u->cv?->status,
            'first_name' => $u->cv?->first_name,
            'last_name'  => $u->cv?->last_name,
            'updated_at' => $u->cv?->updated_at,
            'user'       => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role],
        ]);

        return Inertia::render('Admin/Dashboard', [
            'cvs'    => $users,
            'counts' => $counts,
        ]);
    }

    public function lock(Cv $cv): RedirectResponse
    {
        $this->authorize('lock', $cv);

        $cv->update(['status' => 'locked']);

        return redirect()->back()->with('success', 'CV locked successfully.');
    }

    public function unlock(Cv $cv): RedirectResponse
    {
        $this->authorize('lock', $cv);

        $cv->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'CV unlocked successfully.');
    }

    public function archive(Cv $cv): RedirectResponse
    {
        $this->authorize('archive', $cv);

        $cv->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'CV archived successfully.');
    }
}
