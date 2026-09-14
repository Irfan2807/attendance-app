<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'day_name',
        'state_codes',
        'is_nationwide',
        'is_subject_to_change',
        'year',
    ];

    protected $casts = [
        'date' => 'date',
        'state_codes' => 'array',
        'is_nationwide' => 'boolean',
        'is_subject_to_change' => 'boolean',
        'year' => 'integer',
    ];

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeForState(Builder $query, string $stateCode): Builder
    {
        $code = strtoupper(trim($stateCode));

        return $query->where(function (Builder $q) use ($code) {
            $q->where('is_nationwide', true)
              ->orWhereJsonContains('state_codes', $code);
        });
    }

    public function scopeInDateRange(Builder $query, $startDate, $endDate): Builder
    {
        $start = $startDate instanceof Carbon ? $startDate->toDateString() : (string) $startDate;
        $end = $endDate instanceof Carbon ? $endDate->toDateString() : (string) $endDate;

        return $query->whereBetween('date', [$start, $end]);
    }

    public function appliesToState(?string $stateCode = null): bool
    {
        if ($this->is_nationwide) {
            return true;
        }

        if (! $stateCode || empty($this->state_codes)) {
            return false;
        }

        return in_array(strtoupper(trim($stateCode)), $this->state_codes, true);
    }
}
