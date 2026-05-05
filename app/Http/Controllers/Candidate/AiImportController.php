<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Services\AiImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AiImportController extends Controller
{
    public function __construct(private AiImportService $ai) {}

    private const IMPORT_LIMIT = 3;

    public function fromText(Request $request): JsonResponse
    {
        $cv = $request->user()->cv;

        if ($cv->ai_import_count >= self::IMPORT_LIMIT) {
            return response()->json(['error' => 'Hai raggiunto il limite di 3 importazioni AI. Contatta l\'amministratore per richiedere un reset.'], 422);
        }

        $request->validate([
            'text' => ['required', 'string', 'max:50000'],
        ]);

        try {
            $projects = $this->ai->importFromText($request->string('text'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $cv->increment('ai_import_count');

        return response()->json(['projects' => $projects]);
    }

    public function fromPdf(Request $request): JsonResponse
    {
        $cv = $request->user()->cv;

        if ($cv->ai_import_count >= self::IMPORT_LIMIT) {
            return response()->json(['error' => 'Hai raggiunto il limite di 3 importazioni AI. Contatta l\'amministratore per richiedere un reset.'], 422);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $projects = $this->ai->importFromPdf($request->file('file')->getRealPath());
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $cv->increment('ai_import_count');

        return response()->json(['projects' => $projects]);
    }
}
