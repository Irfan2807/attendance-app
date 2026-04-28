<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_name',
        'latitude',
        'longitude',
        'status',
        'clock_in_time',
        'clock_out_time',
        'verification_notes',
        'approval_notes',
        'approved_by',
        'approved_at',
    ];
    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // This is the missing link!
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Approver relationship
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}