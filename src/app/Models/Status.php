<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    /**
     * Get the surveys with this status.
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
