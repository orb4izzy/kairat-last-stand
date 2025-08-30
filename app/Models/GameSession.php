<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'score',
        'highest_streak',
        'shots_saved',
        'shots_conceded',
        'game_duration',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
        'highest_streak' => 'integer',
        'shots_saved' => 'integer',
        'shots_conceded' => 'integer',
        'game_duration' => 'integer',
    ];

    /**
     * Get the user that owns the game session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get top scores.
     */
    public function scopeTopScores($query, $limit = 20)
    {
        return $query->orderBy('score', 'desc')->limit($limit);
    }
}
