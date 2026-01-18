<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'date',
        'time_start',
        'time_end',
        'status'
    ];

    protected $attributes = [
        'status' => 'available',
    ];

    protected $casts = [
        'date' => 'date',
    ];

 
public function getTimeStartAttribute($value)
{
    return $value ? Carbon::parse($value)->format('H:i') : null;
}

public function setTimeStartAttribute($value)
{
    if (!$value) {
        $this->attributes['time_start'] = null;
        return;
    }

    $this->attributes['time_start'] = Carbon::parse($value)->format('H:i:s');
}

public function getTimeEndAttribute($value)
{
    return $value ? Carbon::parse($value)->format('H:i') : null;
}

public function setTimeEndAttribute($value)
{
    if (!$value) {
        $this->attributes['time_end'] = null;
        return;
    }

    $this->attributes['time_end'] = Carbon::parse($value)->format('H:i:s');
}
    // === RELATIONS ===

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }
}
