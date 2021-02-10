<?php

namespace App\Repositories\Line;

interface AssessmentRepositoryInterface
{
    public function createAssessment($attr);

    public function getAssessments($attr);
}
