<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CvAdminController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Cv::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $counts = [
            'draft'    => Cv::where('status', 'draft')->count(),
            'locked'   => Cv::where('status', 'locked')->count(),
            'archived' => Cv::where('status', 'archived')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'cvs'    => $query->get(),
            'counts' => $counts,
        ]);
    }

    public function lock(Cv $cv): RedirectResponse
    {
        $this->authorize('lock', $cv);

        $cv->update(['status' => 'locked']);

        return redirect()->back()->with('success', 'CV bloccato con successo.');
    }

    public function unlock(Cv $cv): RedirectResponse
    {
        $this->authorize('lock', $cv);

        $cv->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'CV sbloccato con successo.');
    }

    public function archive(Cv $cv): RedirectResponse
    {
        $this->authorize('archive', $cv);

        $cv->update(['status' => 'archived']);

        return redirect()->back()->with('success', 'CV archiviato con successo.');
    }
}
