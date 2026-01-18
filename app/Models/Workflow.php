<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_active',
        'definition',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'definition' => 'array',
    ];

    public function triggers()
    {
        return $this->hasMany(WorkflowTrigger::class);
    }

    public function conditions()
    {
        return $this->hasMany(WorkflowCondition::class)->orderBy('position');
    }

    public function actions()
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('position');
    }

    public function logs()
    {
        return $this->hasMany(WorkflowLog::class);
    }
}
