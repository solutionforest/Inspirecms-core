<?php

namespace SolutionForest\InspireCms\Tests\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \SolutionForest\InspireCms\Models\User
{
    use HasFactory;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
