<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;

class ApiToken extends Model
{
    use SoftDeletes, LogsActivity;

    protected static $logFillable = true;

    protected $dates = [
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'name' => 'string',
    ];

    public static $rules = [
        'name' => 'required|string|max:255',
        'active' => 'required|boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (ApiToken $apiToken) {
            $apiToken->token = Uuid::uuid4()->toString();
        });
    }
}
