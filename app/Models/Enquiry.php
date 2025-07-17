<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
   protected $fillable = [
        'customer_name',
        'contact_number',
        'email',
        'details',
        'status',
        'user_id',
    ];

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
