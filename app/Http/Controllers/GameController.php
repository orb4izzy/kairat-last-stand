<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    /**
     * Show the main game page.
     */
    public function index()
    {
        return view('game.index');
    }

    /**
     * Start a new game session.
     */
    public function startGame(Request $request)
    {
        $user = Auth::user();
        
        // Create a new game session
        $gameSession = GameSession::create([
            'user_id' => $user->id,
            'score' => 0,
            'highest_streak' => 0,
            'shots_saved' => 0,
            'shots_conceded' => 0,
            'game_duration' => 0,
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $gameSession->id,
        ]);
    }

    /**
     * Save game result.
     */
    public function saveResult(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:game_sessions,id',
            'score' => 'required|integer|min:0',
            'highest_streak' => 'required|integer|min:0',
            'shots_saved' => 'required|integer|min:0',
            'shots_conceded' => 'required|integer|min:0',
            'game_duration' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $gameSession = GameSession::where('id', $request->session_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$gameSession) {
            return response()->json([
                'success' => false,
                'message' => 'Game session not found',
            ], 404);
        }

        $gameSession->update([
            'score' => $request->score,
            'highest_streak' => $request->highest_streak,
            'shots_saved' => $request->shots_saved,
            'shots_conceded' => $request->shots_conceded,
            'game_duration' => $request->game_duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Game result saved successfully',
        ]);
    }
}
