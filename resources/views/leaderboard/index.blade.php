@extends('layouts.app')

@section('title', 'Leaderboard - Kairat\'s Last Stand')

@section('content')
<div class="leaderboard-container">
    <div class="text-center mb-5">
        <h1 class="display-4 text-white mb-3">🏆 Leaderboard</h1>
        <p class="lead text-white-50">Top 20 goalkeepers defending Kairat's honor!</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="leaderboard-card">
                <div class="leaderboard-header">
                    <h3 class="text-white mb-0">⚽ Top Players</h3>
                </div>
                
                @if($topPlayers->count() > 0)
                    <div class="leaderboard-list">
                        @foreach($topPlayers as $index => $playerData)
                            <div class="leaderboard-item {{ $index < 3 ? 'podium' : '' }}">
                                <div class="rank">
                                    @if($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        <span class="rank-number">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                
                                <div class="player-info">
                                    <div class="player-name">
                                        {{ $playerData['user']->name ?? $playerData['user']->email }}
                                    </div>
                                    <div class="player-email">
                                        {{ $playerData['user']->email }}
                                    </div>
                                </div>
                                
                                <div class="score">
                                    <span class="score-value">{{ number_format($playerData['highest_score']) }}</span>
                                    <span class="score-label">points</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-players">
                        <div class="text-center py-5">
                            <h4 class="text-white-50">No players yet!</h4>
                            <p class="text-white-50">Be the first to play and set a high score!</p>
                            <a href="{{ route('game.index') }}" class="btn btn-primary">
                                🚀 Start Playing
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('game.index') }}" class="btn btn-secondary btn-lg">
            🏠 Back to Game
        </a>
    </div>
</div>

<style>
.leaderboard-container {
    min-height: 80vh;
    padding: 20px 0;
}

.leaderboard-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.leaderboard-header {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.leaderboard-list {
    max-height: 600px;
    overflow-y: auto;
}

.leaderboard-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.leaderboard-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.leaderboard-item.podium {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 255, 255, 0.05));
}

.leaderboard-item:last-child {
    border-bottom: none;
}

.rank {
    width: 60px;
    text-align: center;
    font-size: 1.5rem;
    margin-right: 20px;
}

.rank-number {
    color: #fff;
    font-weight: bold;
    font-size: 1.2rem;
}

.player-info {
    flex: 1;
    margin-right: 20px;
}

.player-name {
    color: #fff;
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.player-email {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.score {
    text-align: right;
}

.score-value {
    color: #ffd700;
    font-size: 1.8rem;
    font-weight: bold;
    display: block;
}

.score-label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    text-transform: uppercase;
}

.no-players {
    padding: 40px 20px;
}

/* Scrollbar styling */
.leaderboard-list::-webkit-scrollbar {
    width: 8px;
}

.leaderboard-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.leaderboard-list::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

.leaderboard-list::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

@media (max-width: 768px) {
    .leaderboard-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .rank {
        margin-right: 0;
    }
    
    .player-info {
        margin-right: 0;
    }
    
    .score {
        text-align: center;
    }
}
</style>
@endsection
