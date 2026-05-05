<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CvController extends Controller
{
    public function edit(Request $request): InertiaResponse
    {
        $cv = $request->user()
            ->cv
            ->load(['projects.technologies', 'educations', 'trainings']);

        $this->authorize('view', $cv);

        return Inertia::render('Cv/Editor', [
            'cv' => $cv,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $cv = $request->user()->cv;

        $this->authorize('update', $cv);

        $validated = $request->validate([
            'first_name'                       => ['required', 'string', 'max:255'],
            'last_name'                        => ['required', 'string', 'max:255'],
            'birth_date'                       => ['nullable', 'date'],
            'nationality'                      => ['nullable', 'string', 'max:255'],
            'current_function'                 => ['nullable', 'string', 'max:255'],
            'profile_for'                      => ['nullable', 'string', 'max:255'],
            'date_available'                   => ['nullable', 'date'],
            'education_level'                  => ['nullable', 'in:master,bachelor,secondary'],
            'years_after_secondary'            => ['nullable', 'integer', 'min:0'],
            'it_career_start'                  => ['nullable', 'date'],
            'profile_summary'                  => ['nullable', 'string'],
            'standards_certificates'           => ['nullable', 'string'],
            'languages'                        => ['nullable', 'array'],
            'languages.*.name'                 => ['nullable', 'string'],
            'languages.*.spoken'               => ['nullable', 'string'],
            'languages.*.written'              => ['nullable', 'string'],
            'languages.*.certificate'          => ['nullable', 'string'],
            'contract_type'                    => ['nullable', 'in:permanent,non-permanent,freelancer'],
            'proposed_level'                   => ['nullable', 'string', 'max:255'],
            'educations'                       => ['nullable', 'array'],
            'educations.*.certificate_diploma' => ['nullable', 'string', 'max:255'],
            'educations.*.institute'           => ['nullable', 'string', 'max:255'],
            'educations.*.start_date'          => ['nullable', 'string', 'max:20'],
            'educations.*.end_date'            => ['nullable', 'string', 'max:20'],
            'educations.*.order'               => ['nullable', 'integer'],
            'trainings'                        => ['nullable', 'array'],
            'trainings.*.training_name'        => ['nullable', 'string', 'max:255'],
            'trainings.*.company_institute'    => ['nullable', 'string', 'max:255'],
            'trainings.*.date_followed'        => ['nullable', 'string', 'max:50'],
            'trainings.*.certificate_obtained' => ['nullable', 'string', 'max:255'],
            'trainings.*.order'                => ['nullable', 'integer'],
        ]);

        $cv->update(collect($validated)->only([
            'first_name', 'last_name', 'birth_date', 'nationality', 'current_function',
            'profile_for', 'date_available', 'education_level', 'years_after_secondary',
            'it_career_start', 'profile_summary', 'standards_certificates', 'languages',
            'contract_type', 'proposed_level',
        ])->toArray());

        // Sync educations
        $cv->educations()->delete();
        foreach ($validated['educations'] ?? [] as $i => $edu) {
            $cv->educations()->create([
                'certificate_diploma' => $edu['certificate_diploma'] ?? null,
                'institute'           => $edu['institute'] ?? null,
                'start_date'          => $edu['start_date'] ?? null,
                'end_date'            => $edu['end_date'] ?? null,
                'order'               => $edu['order'] ?? $i,
            ]);
        }

        // Sync trainings
        $cv->trainings()->delete();
        foreach ($validated['trainings'] ?? [] as $i => $tr) {
            $cv->trainings()->create([
                'training_name'        => $tr['training_name'] ?? null,
                'company_institute'    => $tr['company_institute'] ?? null,
                'date_followed'        => $tr['date_followed'] ?? null,
                'certificate_obtained' => $tr['certificate_obtained'] ?? null,
                'order'                => $tr['order'] ?? $i,
            ]);
        }

        return response()->json(['saved_at' => now()->toTimeString()]);
    }

    public function storeProject(Request $request): JsonResponse
    {
        $cv = $request->user()->cv;

        $this->authorize('update', $cv);

        $validated = $request->validate([
            'project_name'              => ['nullable', 'string', 'max:255'],
            'employer'                  => ['nullable', 'string', 'max:255'],
            'client'                    => ['nullable', 'string', 'max:255'],
            'start_date'                => ['required', 'date'],
            'end_date'                  => ['nullable', 'date'],
            'project_size'              => ['nullable', 'in:S,M,L,XL'],
            'description'               => ['nullable', 'string'],
            'roles'                     => ['nullable', 'array'],
            'roles.*'                   => ['string'],
            'responsibilities'          => ['nullable', 'array'],
            'responsibilities.*'        => ['string'],
            'order'                     => ['nullable', 'integer'],
            'technologies'              => ['nullable', 'array'],
            'technologies.*.technology_name' => ['required', 'string', 'max:255'],
            'technologies.*.competence' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $project = $cv->projects()->create(collect($validated)->except('technologies')->toArray());

        if (!empty($validated['technologies'])) {
            $project->technologies()->createMany($validated['technologies']);
        }

        return response()->json($project->load('technologies'), 201);
    }

    public function updateProject(Request $request, Project $project): JsonResponse
    {
        $cv = $request->user()->cv;

        $this->authorize('update', $cv);
        abort_if($project->cv_id !== $cv->id, 403);

        $validated = $request->validate([
            'project_name'              => ['nullable', 'string', 'max:255'],
            'employer'                  => ['nullable', 'string', 'max:255'],
            'client'                    => ['nullable', 'string', 'max:255'],
            'start_date'                => ['required', 'date'],
            'end_date'                  => ['nullable', 'date'],
            'project_size'              => ['nullable', 'in:S,M,L,XL'],
            'description'               => ['nullable', 'string'],
            'roles'                     => ['nullable', 'array'],
            'roles.*'                   => ['string'],
            'responsibilities'          => ['nullable', 'array'],
            'responsibilities.*'        => ['string'],
            'order'                     => ['nullable', 'integer'],
            'technologies'              => ['nullable', 'array'],
            'technologies.*.technology_name' => ['required', 'string', 'max:255'],
            'technologies.*.competence' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $project->update(collect($validated)->except('technologies')->toArray());

        $project->technologies()->delete();

        if (!empty($validated['technologies'])) {
            $project->technologies()->createMany($validated['technologies']);
        }

        return response()->json($project->load('technologies'));
    }

    public function destroyProject(Request $request, Project $project): Response
    {
        $cv = $request->user()->cv;

        abort_if($project->cv_id !== $cv->id, 403);

        $project->delete();

        return response()->noContent();
    }
}
