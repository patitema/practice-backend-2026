<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    /**
     * Get the questions with this type.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
