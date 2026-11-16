<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Запис от ръчна проверка: споменава ли ни даден AI асистент на даден въпрос. */
class SeoAiCheck extends Model
{
    public const ASSISTANTS = ['ChatGPT', 'Gemini', 'Claude', 'Perplexity', 'Copilot', 'Google AI Overviews'];

    protected $fillable = ['checked_on', 'assistant', 'prompt', 'mentioned', 'position_note', 'link', 'user_id'];

    protected $casts = ['checked_on' => 'date', 'mentioned' => 'boolean'];
}
