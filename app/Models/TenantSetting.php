<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'settings',
    ];

    protected $casts = [
        'settings' => AsEncryptedArrayObject::class,
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
