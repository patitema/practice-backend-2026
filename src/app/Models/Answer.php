<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['response_id', 'question_id', 'option_id', 'text_value'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the response that owns the answer.
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * Get the question for this answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the option selected for this answer.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
