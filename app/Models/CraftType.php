<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CraftType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
    ];

    public function masterClasses(): HasMany
    {
        return $this->hasMany(MasterClass::class);
    }
}
