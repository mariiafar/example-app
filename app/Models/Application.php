<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
    'date', 'time', 'client_name', 'phone', 'email', 'service_id', 
    'master_id', 'notes', 'user_id', 'status', 'source', 'deposit',
    'payment_status', 'payment_id', 'time_end'
];

    protected $casts = [
        'date' => 'date',
        'deposit' => 'decimal:2',
    ];

    
    public function getTimeAttribute($value)
    {
        if (!$value) return null;
        
        try {
            
            if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
                return $value;
            }
            
            
            if (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                return substr($value, 0, 5);
            }
            
            
            if ($value instanceof \DateTime) {
                return $value->format('H:i');
            }
            
            return $value;
        } catch (\Exception $e) {
            return $value;
        }
    }

    
    public function setTimeAttribute($value)
    {
        if ($value) {
            try {
                
                if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $this->attributes['time'] = $value . ':00';
                } 
                
                else if (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                    $this->attributes['time'] = $value;
                }
                
                else if ($value instanceof \DateTime) {
                    $this->attributes['time'] = $value->format('H:i:s');
                }
                else {
                    $this->attributes['time'] = $value;
                }
            } catch (\Exception $e) {
                $this->attributes['time'] = $value;
            }
        } else {
            $this->attributes['time'] = null;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($application) {
            
            $timeForDb = $application->time . ':00';
            
            \App\Models\Schedule::where('master_id', $application->master_id)
                ->where('date', $application->date)
                ->where('time_start', $timeForDb)
                ->update(['status' => 'busy']);
        });
    }

    
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}