<?php

namespace App\Http\Controllers;

use App\Helpers\AuthUserHelper;
use App\Services\Projects\TeacherIdeaBalanceService;
use App\Services\Projects\TeacherWorkloadService;
use Illuminate\View\View;

class TeacherLoadController extends Controller
{
    public function __construct(
        private readonly TeacherWorkloadService $workloads,
        private readonly TeacherIdeaBalanceService $balances,
    ) {
    }

    public function index(): View
    {
        $user = AuthUserHelper::fullUser();
        $normalizedRole = match ((string) ($user?->role ?? '')) {
            'committe_leader' => 'committee_leader',
            default => (string) ($user?->role ?? ''),
        };

        abort_unless(in_array($normalizedRole, ['professor', 'committee_leader'], true), 403);

        return view('projects.my-load', [
            'loadSummary' => $this->workloads->summaryForUser($user),
            'ideaBalance' => $this->balances->recommendationsForUser($user),
        ]);
    }
}
