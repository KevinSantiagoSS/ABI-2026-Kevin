<?php

namespace App\Models\ResearchStaff;

use App\Models\LoadProjection;

class ResearchStaffLoadProjection extends LoadProjection
{
    protected $table = 'load_projections';

    protected $connection = 'mysql_research_staff';
}
