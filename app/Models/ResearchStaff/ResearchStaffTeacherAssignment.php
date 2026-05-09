<?php

namespace App\Models\ResearchStaff;

use App\Models\TeacherAssignment;

class ResearchStaffTeacherAssignment extends TeacherAssignment
{
    protected $table = 'teacher_assignments';

    protected $connection = 'mysql_research_staff';
}
