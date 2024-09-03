<?php

namespace SolutionForest\InspireCms\DataTypes;

class UserRole
{
    public string $guardName;

    public function __construct(
        public string $name,
    ) 
    { 
        $this->guardName = $guardName ?? config('inspirecms.auth.guard', 'inspirecms');
    }
}
