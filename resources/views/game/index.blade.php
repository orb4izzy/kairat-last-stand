@extends('layouts.app')

@section('title', 'Kairat\'s Last Stand - Game')

@section('content')
<div class="game-container">
    <!-- Main Menu -->
    <div id="mainMenu" class="game-screen">
        <div class="text-center">
            <h1 class="display-4 text-white mb-4">⚽ Kairat's Last Stand</h1>
            <p class="lead text-white-50 mb-5">Defend the goal as Danil Anarbekov against Celtic's penalty shots!</p>
            
            <div class="game-info mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-card">
                            <h5 class="text-white">🎯 Objective</h5>
                            <p class="text-white-50">Save as many penalty shots as possible</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <h5 class="text-white">🎮 Controls</h5>
                            <p class="text-white-50">Mouse: Move goalkeeper<br>Space: Jump/Save</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <h5 class="text-white">🏆 Scoring</h5>
                            <p class="text-white-50">+10 per save<br>Streak bonuses available!</p>
                        </div>
                    </div>
                </div>
            </div>

            <button id="startGameBtn" class="btn btn-primary btn-lg px-5 py-3">
                🚀 Start Game
            </button>
        </div>
    </div>

    <!-- Game Screen -->
    <div id="gameScreen" class="game-screen" style="display: none;">
        <div class="game-ui">
            <div class="ui-top">
                <div class="score-display">
                    <span class="label">Score:</span>
                    <span id="score">0</span>
                </div>
                <div class="lives-display">
                    <span class="label">Lives:</span>
                    <div id="lives">
                        <span class="life">❤️</span>
                        <span class="life">❤️</span>
                        <span class="life">❤️</span>
                    </div>
                </div>
                <div class="streak-display">
                    <span class="label">Streak:</span>
                    <span id="streak">0</span>
                </div>
            </div>
        </div>
        
        <canvas id="gameCanvas" width="800" height="600"></canvas>
        
        <div class="game-controls">
            <p class="text-white text-center">Move mouse to control goalkeeper • Press SPACE to jump/save</p>
        </div>
    </div>

    <!-- Game Over Screen -->
    <div id="gameOverScreen" class="game-screen" style="display: none;">
        <div class="text-center">
            <h1 class="display-4 text-white mb-4">🏁 Game Over!</h1>
            
            <div class="final-stats mb-4">
                <div class="stat-card">
                    <h3 class="text-white">Final Score</h3>
                    <div class="stat-value" id="finalScore">0</div>
                </div>
                <div class="stat-card">
                    <h3 class="text-white">Highest Streak</h3>
                    <div class="stat-value" id="finalStreak">0</div>
                </div>
                <div class="stat-card">
                    <h3 class="text-white">Shots Saved</h3>
                    <div class="stat-value" id="finalSaves">0</div>
                </div>
            </div>

            <div class="game-over-actions">
                <button id="playAgainBtn" class="btn btn-primary btn-lg me-3">
                    🔄 Play Again
                </button>
                <button id="backToMenuBtn" class="btn btn-secondary btn-lg">
                    🏠 Back to Menu
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.game-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.game-screen {
    width: 100%;
    max-width: 900px;
}

.info-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.game-ui {
    position: relative;
    margin-bottom: 20px;
}

.ui-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(0, 0, 0, 0.7);
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.score-display, .lives-display, .streak-display {
    display: flex;
    align-items: center;
    gap: 10px;
}

.label {
    color: #fff;
    font-weight: bold;
}

#score, #streak {
    color: #ffd700;
    font-size: 1.5rem;
    font-weight: bold;
}

.life {
    font-size: 1.5rem;
    margin-right: 5px;
}

#gameCanvas {
    border: 3px solid #fff;
    border-radius: 10px;
    background: linear-gradient(180deg, #87CEEB 0%, #98FB98 100%);
    display: block;
    margin: 0 auto;
}

.game-controls {
    text-align: center;
    margin-top: 15px;
}

.final-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.stat-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    min-width: 150px;
}

.stat-value {
    color: #ffd700;
    font-size: 2.5rem;
    font-weight: bold;
    margin-top: 10px;
}

.game-over-actions {
    margin-top: 30px;
}

@media (max-width: 768px) {
    #gameCanvas {
        width: 100%;
        height: auto;
    }
    
    .final-stats {
        flex-direction: column;
        align-items: center;
    }
    
    .ui-top {
        flex-direction: column;
        gap: 10px;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Game state
let gameState = {
    isPlaying: false,
    score: 0,
    lives: 3,
    streak: 0,
    sessionId: null,
    startTime: null
};

// Initialize game when page loads
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startGameBtn');
    const playAgainBtn = document.getElementById('playAgainBtn');
    const backToMenuBtn = document.getElementById('backToMenuBtn');
    
    startBtn.addEventListener('click', startGame);
    playAgainBtn.addEventListener('click', startGame);
    backToMenuBtn.addEventListener('click', backToMenu);
});

async function startGame() {
    try {
        // Start new game session
        const response = await fetch('/api/game/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            gameState.sessionId = data.session_id;
            gameState.isPlaying = true;
            gameState.score = 0;
            gameState.lives = 3;
            gameState.streak = 0;
            gameState.startTime = Date.now();
            
            // Show game screen
            document.getElementById('mainMenu').style.display = 'none';
            document.getElementById('gameOverScreen').style.display = 'none';
            document.getElementById('gameScreen').style.display = 'block';
            
            // Initialize game canvas
            initGame();
        }
    } catch (error) {
        console.error('Error starting game:', error);
        alert('Failed to start game. Please try again.');
    }
}

function backToMenu() {
    document.getElementById('gameScreen').style.display = 'none';
    document.getElementById('gameOverScreen').style.display = 'none';
    document.getElementById('mainMenu').style.display = 'block';
}

function updateUI() {
    document.getElementById('score').textContent = gameState.score;
    document.getElementById('streak').textContent = gameState.streak;
    
    const livesElement = document.getElementById('lives');
    livesElement.innerHTML = '';
    for (let i = 0; i < gameState.lives; i++) {
        livesElement.innerHTML += '<span class="life">❤️</span>';
    }
}

function gameOver() {
    gameState.isPlaying = false;
    
    // Update final stats
    document.getElementById('finalScore').textContent = gameState.score;
    document.getElementById('finalStreak').textContent = gameState.streak;
    document.getElementById('finalSaves').textContent = Math.floor(gameState.score / 10);
    
    // Save game result
    saveGameResult();
    
    // Show game over screen
    document.getElementById('gameScreen').style.display = 'none';
    document.getElementById('gameOverScreen').style.display = 'block';
}

async function saveGameResult() {
    if (!gameState.sessionId) return;
    
    try {
        const gameDuration = Math.floor((Date.now() - gameState.startTime) / 1000);
        
        const response = await fetch('/api/game/save-result', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                session_id: gameState.sessionId,
                score: gameState.score,
                highest_streak: gameState.streak,
                shots_saved: Math.floor(gameState.score / 10),
                shots_conceded: 3 - gameState.lives,
                game_duration: gameDuration
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('Failed to save game result:', data.message);
        }
    } catch (error) {
        console.error('Error saving game result:', error);
    }
}

// Game will be initialized in the next part
function initGame() {
    // This will be implemented in the main game JavaScript file
    console.log('Game initialized');
    updateUI();
}
</script>
@endsection
