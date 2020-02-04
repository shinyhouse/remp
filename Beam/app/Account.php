<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $fillable = ['uuid', 'name'];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
