<?php

namespace App\Models;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'capacity',
        'status',
        'user_id'
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}
