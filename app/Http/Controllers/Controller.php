<?php

namespace App\Http\Controllers;

use App\Services\AcademicCalendar\AcademicCalendarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function academicProcessUnavailableView(
        string $processKey,
        ?string $activityLabel = null,
        string $redirectRoute = 'home'
    ): View {
        return view(
            'academic-calendar.unavailable',
            AcademicCalendarService::unavailableActivityViewData($processKey, $activityLabel, null, $redirectRoute)
        );
    }
}
