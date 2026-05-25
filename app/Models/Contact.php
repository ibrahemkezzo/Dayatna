<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Contact extends Model
{
    protected $fillable = ['type', 'value'];

    public function contactable(): MorphTo {
        return $this->morphTo();
    }

    
}
