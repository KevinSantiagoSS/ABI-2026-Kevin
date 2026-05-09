<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Project;
use App\Models\ThematicArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankApprovedIdeasForProfessorsController extends Controller
{
    /**
     * Los profesores pueden consultar el banco de ideas aprobadas
     * sin depender de la ventana del calendario.
     */
    public function index(Request $request)
    {
        $professor = Professor::where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->first();

        if (! $professor || ! $professor->city_program_id) {
            $perPage = (int) $request->input('per_page', 10);

            return view('projects.professor.approved', [
                'projects' => Project::whereRaw('1 = 0')->paginate($perPage),
                'thematicAreas' => collect(),
                'thematicAreaId' => null,
                'perPage' => $perPage,
            ])->with('error', 'Completa tu asignacion de programa para ver el banco de ideas aprobadas.');
        }

        $perPage = (int) $request->input('per_page', 10);
        $thematicAreaId = $request->input('thematic_area_id');

        $program = $professor->cityProgram?->program;
        $researchGroupId = $program?->research_group_id;

        $thematicAreas = collect();

        if ($researchGroupId) {
            $thematicAreas = ThematicArea::query()
                ->whereHas('investigationLine', function ($query) use ($researchGroupId) {
                    $query->where('research_group_id', $researchGroupId);
                })
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get();
        }

        $projectsQuery = Project::query()
            ->whereHas('projectStatus', fn ($query) => $query->where('name', 'Aprobado'))
            ->whereHas('professors', function ($query) use ($professor) {
                $query->where('city_program_id', $professor->city_program_id);
            });

        if (! empty($thematicAreaId)) {
            $projectsQuery->where('thematic_area_id', $thematicAreaId);
        }

        $projects = $projectsQuery
            ->with([
                'projectStatus',
                'thematicArea.investigationLine',
                'versions.contentVersions.content',
                'contentFrameworkProjects.contentFramework.framework',
                'professors',
                'students',
            ])
            ->paginate($perPage)
            ->withQueryString();

        return view('projects.professor.approved', [
            'projects' => $projects,
            'thematicAreas' => $thematicAreas,
            'thematicAreaId' => $thematicAreaId,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Los profesores pueden ver el detalle de una idea aprobada
     * sin depender de la ventana del calendario.
     */
    public function show(Project $project)
    {
        $professor = Professor::where('user_id', Auth::id())
            ->whereNull('deleted_at')
            ->firstOrFail();

        $sameProgram = $project->students()
                ->where('city_program_id', $professor->city_program_id)
                ->exists()
            || $project->professors()
                ->where('city_program_id', $professor->city_program_id)
                ->exists();

        if (! $sameProgram) {
            abort(403, 'No tienes permiso para ver este proyecto.');
        }

        $project->load([
            'projectStatus',
            'thematicArea.investigationLine',
            'versions.contentVersions.content',
            'contentFrameworkProjects.contentFramework.framework',
            'students',
            'professors',
        ]);

        $latestVersion = $project->versions()->latest('created_at')->first();

        $contentValues = [];
        if ($latestVersion) {
            $contentValues = $latestVersion->contentVersions
                ->mapWithKeys(fn ($contentVersion) => [$contentVersion->content->name => $contentVersion->value])
                ->toArray();
        }

        $frameworksSelected = $project->contentFrameworkProjects()
            ->with('contentFramework.framework')
            ->get()
            ->map(fn ($item) => $item->contentFramework);

        return view('projects.professor.show', compact(
            'project',
            'latestVersion',
            'contentValues',
            'frameworksSelected'
        ));
    }
}