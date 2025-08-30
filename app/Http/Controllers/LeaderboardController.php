<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Show the leaderboard page.
     */
    public function index()
    {
        // Get top 20 players with their highest scores
        $topPlayers = User::with(['gameSessions' => function ($query) {
            $query->orderBy('score', 'desc');
        }])
        ->get()
        ->map(function ($user) {
            $highestScore = $user->gameSessions->max('score') ?? 0;
            return [
                'user' => $user,
                'highest_score' => $highestScore,
            ];
        })
        ->sortByDesc('highest_score')
        ->take(20)
        ->values();

        return view('leaderboard.index', compact('topPlayers'));
    }
}
