<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $fillable = [
        'enquiry_id',
        'follow_up_date',
        'notes',
        'status',
        'user_id',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
