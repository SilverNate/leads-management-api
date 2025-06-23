<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     * This ensures the ErrorLog model uses the 'pgsql_logging' connection.
     *
     * @var string
     */
    protected $connection = 'pgsql_logging';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'error_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'error_message',
        'endpoint',
        'status_code',
    ];

    /**
     * Indicates if the model should be timestamped.
     * We'll manage 'timestamp' manually as 'created_at'.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Set the 'timestamp' column on creation.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->timestamp = $model->freshTimestamp();
        });
    }
}