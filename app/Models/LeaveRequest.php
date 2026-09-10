<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'attachment_path',
        'status',
        'actioned_by',
        'actioned_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'float',
        'actioned_at' => 'datetime',
        'leave_type' => LeaveType::class,
        'status' => LeaveStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === LeaveStatus::Rejected;
    }

    public function isMedicalCertificate(): bool
    {
        return $this->leave_type === LeaveType::MedicalLeave;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Pending->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Approved->value);
    }

    public function scopeActiveOnDate(Builder $query, Carbon|string $date): Builder
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return $query->where('status', LeaveStatus::Approved->value)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr);
    }
}
