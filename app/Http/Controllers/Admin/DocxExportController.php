<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Services\DocxGeneratorService;
use Illuminate\Http\Response;

class DocxExportController extends Controller
{
    public function __construct(private DocxGeneratorService $generator) {}

    public function export(Cv $cv): Response
    {
        $this->authorize('view', $cv);

        $cv->loadMissing(['user', 'projects.technologies', 'educations', 'trainings']);

        $tmpFile  = $this->generator->generate($cv);
        $filename = 'CV_' . ($cv->last_name ?? 'Unknown') . '_' . ($cv->first_name ?? '') . '_DIGIT-TM-II.docx';
        $filename = preg_replace('/\s+/', '_', $filename);

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($content),
        ]);
    }
}
