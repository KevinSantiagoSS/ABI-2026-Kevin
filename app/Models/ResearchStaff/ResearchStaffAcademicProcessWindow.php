<?php

namespace App\Models\ResearchStaff;

use App\Models\AcademicProcessWindow;

class ResearchStaffAcademicProcessWindow extends AcademicProcessWindow
{
    protected $table = 'academic_process_windows';

    protected $connection = 'mysql_research_staff';
}
