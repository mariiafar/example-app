<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Database\Factories\ServiceFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'tattoo' => 'Татуировка',
        'piercing' => 'Пирсинг',
        'laser_removal' => 'Лазерное удаление',
    ];

    protected $fillable = [
        'name',
        'type',
        'price',
        'duration',
        'description',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration' => 'integer'
    ];
    

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' ₽';
    }

    public function getFormattedDurationAttribute(): string
    {
        return $this->duration . ' мин';
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    protected static function newFactory()
    {
        return ServiceFactory::new();
    }

    // Связь с мастерами
    public function masters()
    {
        return $this->belongsToMany(User::class, 'master_service', 'service_id', 'master_id')
            ->withTimestamps();
    }
}