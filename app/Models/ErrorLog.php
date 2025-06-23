<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;


    protected $connection = 'pgsql_logging';

    protected $table = 'error_logs';

    protected $fillable = [
        'error_message',
        'endpoint',
        'status_code',
    ];


    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime',
    ];


    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->timestamp = $model->freshTimestamp();
        });
    }
}
