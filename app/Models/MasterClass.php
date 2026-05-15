<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterClass extends Model
{
    public const TITLE_MAX_LENGTH = 255;

    public const DESCRIPTION_MAX_LENGTH = 500;

    public const TIME_SLOTS = [
        '09:00' => '09:00 - 11:00',
        '11:00' => '11:00 - 13:00',
        '13:00' => '13:00 - 15:00',
        '15:00' => '15:00 - 17:00',
    ];

    protected $fillable = [
        'craft_type_id',
        'user_id',
        'title',
        'description',
        'price',
        'scheduled_date',
        'start_time',
        'max_people',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function craftType(): BelongsTo
    {
        return $this->belongsTo(CraftType::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->withTimestamps();
    }

    public function scopeForCraftType(Builder $query, int $craftTypeId): Builder
    {
        return $query->where('craft_type_id', $craftTypeId);
    }

    public function getFreePlacesAttribute(): int
    {
        return max(0, (int) $this->max_people - $this->participants->count());
    }

    public function getTimeLabelAttribute(): string
    {
        return self::TIME_SLOTS[$this->start_time] ?? $this->start_time;
    }

    public function getStartsAtAttribute(): Carbon
    {
        return Carbon::parse($this->scheduled_date->format('Y-m-d').' '.$this->start_time);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->starts_at->lt(now());
    }
}
