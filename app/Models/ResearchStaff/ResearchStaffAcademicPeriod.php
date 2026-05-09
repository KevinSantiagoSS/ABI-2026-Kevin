<?php

namespace App\Models\ResearchStaff;

use App\Models\AcademicPeriod;

class ResearchStaffAcademicPeriod extends AcademicPeriod
{
    protected $table = 'academic_periods';

    protected $connection = 'mysql_research_staff';
}
