<?php
// Kairat's Last Stand - Правильная версия игры
session_start();

// Простая база данных в файле
$db_file = 'game_data.json';

// Функция для загрузки данных
function loadData() {
    global $db_file;
    if (file_exists($db_file)) {
        return json_decode(file_get_contents($db_file), true);
    }
    return ['users' => [], 'scores' => []];
}

// Функция для сохранения данных
function saveData($data) {
    global $db_file;
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
}

// Обработка API запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    $data = loadData();
    
    switch ($action) {
        case 'register':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            $name = $input['name'] ?? '';
            
            if ($email && $password && $name) {
                $data['users'][$email] = [
                    'name' => $name,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                saveData($data);
                echo json_encode(['success' => true, 'message' => 'User registered successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            }
            exit;
            
        case 'login':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            if (isset($data['users'][$email]) && password_verify($password, $data['users'][$email]['password'])) {
                $_SESSION['user'] = $email;
                echo json_encode(['success' => true, 'user' => $data['users'][$email]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            exit;
            
        case 'save_score':
            $score = $input['score'] ?? 0;
            $user = $_SESSION['user'] ?? 'guest';
            
            $data['scores'][] = [
                'user' => $user,
                'score' => $score,
                'date' => date('Y-m-d H:i:s')
            ];
            
            // Сортируем по очкам и оставляем топ 20
            usort($data['scores'], function($a, $b) {
                return $b['score'] - $a['score'];
            });
            $data['scores'] = array_slice($data['scores'], 0, 20);
            
            saveData($data);
            echo json_encode(['success' => true]);
            exit;
            
        case 'get_leaderboard':
            $scores = $data['scores'] ?? [];
            $leaderboard = [];
            
            foreach ($scores as $score) {
                $user_name = $score['user'] === 'guest' ? 'Guest' : ($data['users'][$score['user']]['name'] ?? $score['user']);
                $leaderboard[] = [
                    'name' => $user_name,
                    'score' => $score['score'],
                    'date' => $score['date']
                ];
            }
            
            echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
            exit;
    }
}

// Если это не API запрос, показываем игру
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairat's Last Stand - Penalty Shootout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            overflow: hidden;
            height: 100vh;
        }
        
        .game-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .game-header {
            text-align: center;
            margin-bottom: 20px;
            z-index: 10;
        }
        
        .game-title {
            font-size: 3em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            color: #ffd700;
        }
        
        .game-subtitle {
            font-size: 1.3em;
            margin: 10px 0;
            opacity: 0.9;
        }
        
        .goalkeeper-info {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            text-align: center;
        }
        
        .goalkeeper-name {
            font-size: 1.5em;
            color: #ffd700;
            margin-bottom: 5px;
        }
        
        .goalkeeper-team {
            font-size: 1.1em;
            color: #ff6b35;
        }
        
        .auth-section {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .auth-form input {
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background: rgba(255,255,255,0.9);
        }
        
        .auth-form button {
            padding: 12px;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .auth-form button:hover {
            background: #e55a2b;
        }
        
        .game-info {
            text-align: center;
            margin-bottom: 20px;
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .score-display {
            font-size: 1.8em;
            margin: 10px 0;
            color: #ffd700;
        }
        
        .lives-display {
            font-size: 1.3em;
            margin: 10px 0;
            color: #ff6b35;
        }
        
        .streak-display {
            font-size: 1.2em;
            margin: 10px 0;
            color: #90EE90;
        }
        
        .game-canvas {
            border: 4px solid #fff;
            border-radius: 15px;
            background: #228B22;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            position: relative;
        }
        
        .game-controls {
            margin-top: 20px;
            text-align: center;
        }
        
        .control-button {
            padding: 15px 30px;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            cursor: pointer;
            margin: 0 10px;
            transition: all 0.3s;
        }
        
        .control-button:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .control-button.secondary {
            background: #4a7c59;
        }
        
        .control-button.secondary:hover {
            background: #3a6b49;
        }
        
        .leaderboard {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            padding: 20px;
            border-radius: 10px;
            max-width: 300px;
            backdrop-filter: blur(10px);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .leaderboard h3 {
            margin: 0 0 15px 0;
            color: #ffd700;
            text-align: center;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        
        .leaderboard-item:first-child {
            background: rgba(255,215,0,0.3);
            border: 2px solid #ffd700;
        }
        
        .hidden {
            display: none;
        }
        
        .instructions {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            max-width: 300px;
        }
        
        .instructions h4 {
            color: #ffd700;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            list-style: none;
            padding: 0;
        }
        
        .instructions li {
            margin: 5px 0;
            font-size: 0.9em;
        }
        
        .game-over {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 3px solid #ff6b35;
        }
        
        .game-over h2 {
            color: #ff6b35;
            margin-bottom: 20px;
            font-size: 2em;
        }
        
        .final-score {
            font-size: 1.5em;
            color: #ffd700;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1 class="game-title">⚽ Kairat's Last Stand</h1>
            <p class="game-subtitle">Penalty Shootout Victory Over Celtic</p>
        </div>
        
        <div class="goalkeeper-info">
            <div class="goalkeeper-name">Danil Anarbekov</div>
            <div class="goalkeeper-team">FC Kairat Goalkeeper</div>
        </div>
        
        <div class="auth-section" id="authSection">
            <div id="loginForm">
                <h3>Login to Track Your Scores</h3>
                <form class="auth-form" onsubmit="login(event)">
                    <input type="email" id="loginEmail" placeholder="Email" required>
                    <input type="password" id="loginPassword" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <button class="control-button secondary" onclick="showRegister()">Register</button>
            </div>
            
            <div id="registerForm" class="hidden">
                <h3>Register New Account</h3>
                <form class="auth-form" onsubmit="register(event)">
                    <input type="text" id="registerName" placeholder="Your Name" required>
                    <input type="email" id="registerEmail" placeholder="Email" required>
                    <input type="password" id="registerPassword" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>
                <button class="control-button secondary" onclick="showLogin()">Login</button>
            </div>
        </div>
        
        <div class="game-info">
            <div class="score-display">Score: <span id="score">0</span></div>
            <div class="lives-display">Lives: <span id="lives">3</span></div>
            <div class="streak-display">Streak: <span id="streak">0</span></div>
        </div>
        
        <canvas id="gameCanvas" class="game-canvas" width="900" height="500"></canvas>
        
        <div class="game-controls">
            <button class="control-button" onclick="startGame()">Start Game</button>
            <button class="control-button secondary" onclick="showLeaderboard()">Leaderboard</button>
            <button class="control-button secondary" onclick="logout()">Logout</button>
        </div>
    </div>
    
    <div class="leaderboard" id="leaderboard">
        <h3>🏆 Top Players</h3>
        <div id="leaderboardList">Loading...</div>
    </div>
    
    <div class="instructions">
        <h4>How to Play:</h4>
        <ul>
            <li>🖱️ Move mouse to control goalkeeper</li>
            <li>⌨️ Press SPACEBAR to jump/save</li>
            <li>⚽ Save penalty shots from Celtic</li>
            <li>🏆 Get streak bonuses!</li>
            <li>💖 Extra life every 100 points</li>
        </ul>
    </div>
    
    <div class="game-over hidden" id="gameOver">
        <h2>Game Over!</h2>
        <div class="final-score">Final Score: <span id="finalScore">0</span></div>
        <button class="control-button" onclick="restartGame()">Play Again</button>
    </div>
    
    <script>
        // Game variables
        let gameRunning = false;
        let score = 0;
        let lives = 3;
        let streak = 0;
        let gameSpeed = 2;
        let user = null;
        let ballSpeed = 3;
        
        // Canvas setup
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        
        // Game objects
        let goalkeeper = { 
            x: 440, 
            y: 240, 
            width: 50, 
            height: 80, 
            jumping: false,
            jumpHeight: 0,
            maxJumpHeight: 100
        };
        
        let ball = { 
            x: 0, 
            y: 0, 
            vx: 0, 
            vy: 0, 
            radius: 20, 
            active: false,
            trail: []
        };
        
        let aiPlayer = { 
            x: 470, 
            y: 380, 
            width: 40, 
            height: 60, 
            kicking: false,
            kickTimer: 0
        };
        
        let goal = {
            x: 350,
            y: 200,
            width: 240,
            height: 80
        };
        
        // Game functions
        function startGame() {
            if (!gameRunning) {
                gameRunning = true;
                score = 0;
                lives = 3;
                streak = 0;
                ballSpeed = 3;
                updateDisplay();
                gameLoop();
            }
        }
        
        function gameLoop() {
            if (!gameRunning) return;
            
            update();
            draw();
            requestAnimationFrame(gameLoop);
        }
        
        function update() {
            // Update goalkeeper
            if (goalkeeper.jumping) {
                goalkeeper.jumpHeight += 8;
                if (goalkeeper.jumpHeight >= goalkeeper.maxJumpHeight) {
                    goalkeeper.jumping = false;
                }
            } else if (goalkeeper.jumpHeight > 0) {
                goalkeeper.jumpHeight -= 5;
            }
            
            goalkeeper.y = 240 - goalkeeper.jumpHeight;
            
            // Update AI player kick timer
            aiPlayer.kickTimer++;
            
            // Update ball
            if (ball.active) {
                // Add to trail
                ball.trail.push({x: ball.x, y: ball.y});
                if (ball.trail.length > 10) {
                    ball.trail.shift();
                }
                
                ball.x += ball.vx;
                ball.y += ball.vy;
                ball.vy += 0.4; // gravity
                
                // Check collision with goalkeeper
                if (ball.x > goalkeeper.x - 25 && ball.x < goalkeeper.x + 75 &&
                    ball.y > goalkeeper.y - 10 && ball.y < goalkeeper.y + 90) {
                    saveBall();
                }
                
                // Check if ball went past goalkeeper
                if (ball.y > 400) {
                    missBall();
                }
                
                // Check if ball went out of bounds
                if (ball.x < 0 || ball.x > canvas.width || ball.y > canvas.height) {
                    ball.active = false;
                }
            } else {
                // Randomly kick ball
                if (aiPlayer.kickTimer > 120 && Math.random() < 0.02) {
                    kickBall();
                }
            }
        }
        
        function draw() {
            // Clear canvas
            ctx.fillStyle = '#228B22';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw field lines
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.strokeRect(50, 50, canvas.width - 100, canvas.height - 100);
            
            // Draw goal posts
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 8;
            // Left post
            ctx.beginPath();
            ctx.moveTo(goal.x, goal.y);
            ctx.lineTo(goal.x, goal.y + goal.height);
            ctx.stroke();
            // Right post
            ctx.beginPath();
            ctx.moveTo(goal.x + goal.width, goal.y);
            ctx.lineTo(goal.x + goal.width, goal.y + goal.height);
            ctx.stroke();
            // Crossbar
            ctx.beginPath();
            ctx.moveTo(goal.x, goal.y);
            ctx.lineTo(goal.x + goal.width, goal.y);
            ctx.stroke();
            
            // Draw goal net
            ctx.strokeStyle = 'rgba(255,255,255,0.3)';
            ctx.lineWidth = 1;
            // Vertical net lines
            for (let i = 0; i < 12; i++) {
                ctx.beginPath();
                ctx.moveTo(goal.x + (i * 20), goal.y);
                ctx.lineTo(goal.x + (i * 20), goal.y + goal.height);
                ctx.stroke();
            }
            // Horizontal net lines
            for (let i = 0; i < 8; i++) {
                ctx.beginPath();
                ctx.moveTo(goal.x, goal.y + (i * 10));
                ctx.lineTo(goal.x + goal.width, goal.y + (i * 10));
                ctx.stroke();
            }
            
            // Draw ball trail
            if (ball.trail.length > 1) {
                ctx.strokeStyle = 'rgba(255,255,255,0.3)';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.moveTo(ball.trail[0].x, ball.trail[0].y);
                for (let i = 1; i < ball.trail.length; i++) {
                    ctx.lineTo(ball.trail[i].x, ball.trail[i].y);
                }
                ctx.stroke();
            }
            
            // Draw ball
            if (ball.active) {
                // Ball shadow
                ctx.fillStyle = 'rgba(0,0,0,0.4)';
                ctx.beginPath();
                ctx.arc(ball.x + 4, ball.y + 4, ball.radius, 0, Math.PI * 2);
                ctx.fill();
                
                // Ball main color
                ctx.fillStyle = '#fff';
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
                ctx.fill();
                
                // Ball outline
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 4;
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
                ctx.stroke();
                
                // Ball pattern lines
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.moveTo(ball.x - ball.radius, ball.y);
                ctx.lineTo(ball.x + ball.radius, ball.y);
                ctx.moveTo(ball.x, ball.y - ball.radius);
                ctx.lineTo(ball.x, ball.y + ball.radius);
                ctx.stroke();
                
                // Ball highlight
                ctx.fillStyle = 'rgba(255,255,255,0.6)';
                ctx.beginPath();
                ctx.arc(ball.x - 5, ball.y - 5, ball.radius/3, 0, Math.PI * 2);
                ctx.fill();
            }
            
            // Draw goalkeeper (Kairat - yellow/black stripes)
            ctx.fillStyle = '#ffd700';
            ctx.fillRect(goalkeeper.x, goalkeeper.y, goalkeeper.width, goalkeeper.height);
            
            // Goalkeeper stripes
            ctx.fillStyle = '#000';
            for (let i = 0; i < 6; i++) {
                ctx.fillRect(goalkeeper.x + 5, goalkeeper.y + (i * 12), 40, 6);
            }
            
            // Goalkeeper face
            ctx.fillStyle = '#ffdbac';
            ctx.beginPath();
            ctx.arc(goalkeeper.x + 25, goalkeeper.y + 15, 12, 0, Math.PI * 2);
            ctx.fill();
            
            // Goalkeeper eyes
            ctx.fillStyle = '#000';
            ctx.beginPath();
            ctx.arc(goalkeeper.x + 20, goalkeeper.y + 12, 2, 0, Math.PI * 2);
            ctx.arc(goalkeeper.x + 30, goalkeeper.y + 12, 2, 0, Math.PI * 2);
            ctx.fill();
            
            // Draw AI player (Celtic - white/green stripes)
            ctx.fillStyle = '#fff';
            ctx.fillRect(aiPlayer.x, aiPlayer.y, aiPlayer.width, aiPlayer.height);
            
            // Celtic stripes
            ctx.fillStyle = '#228B22';
            for (let i = 0; i < 4; i++) {
                ctx.fillRect(aiPlayer.x + 5, aiPlayer.y + (i * 12), 30, 6);
            }
            
            // AI player face
            ctx.fillStyle = '#ffdbac';
            ctx.beginPath();
            ctx.arc(aiPlayer.x + 20, aiPlayer.y + 15, 10, 0, Math.PI * 2);
            ctx.fill();
            
            // AI player eyes
            ctx.fillStyle = '#000';
            ctx.beginPath();
            ctx.arc(aiPlayer.x + 16, aiPlayer.y + 12, 2, 0, Math.PI * 2);
            ctx.arc(aiPlayer.x + 24, aiPlayer.y + 12, 2, 0, Math.PI * 2);
            ctx.fill();
            
            // Draw kick animation
            if (aiPlayer.kicking) {
                ctx.strokeStyle = '#ff6b35';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.moveTo(aiPlayer.x + 40, aiPlayer.y + 30);
                ctx.lineTo(aiPlayer.x + 60, aiPlayer.y + 20);
                ctx.stroke();
            }
        }
        
        function kickBall() {
            ball.x = aiPlayer.x + 40;
            ball.y = aiPlayer.y + 30;
            
            // Calculate trajectory to goal (straight forward)
            const goalCenterX = goal.x + goal.width / 2;
            const goalCenterY = goal.y + goal.height / 2;
            
            // Random target within goal (more centered and realistic)
            const targetX = goalCenterX + (Math.random() - 0.5) * 80; // Slightly wider spread
            const targetY = goalCenterY + (Math.random() - 0.5) * 30; // Reduced vertical spread
            
            // Calculate velocity to reach target
            const distanceX = targetX - ball.x;
            const distanceY = targetY - ball.y;
            const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
            
            // Adjust speed based on distance and difficulty
            const speed = (ballSpeed + Math.random() * 1.5) * 1.3; // Slightly increased speed
            ball.vx = (distanceX / distance) * speed;
            ball.vy = (distanceY / distance) * speed; // No upward arc for straighter shot
            
            ball.active = true;
            ball.trail = [];
            aiPlayer.kicking = true;
            aiPlayer.kickTimer = 0;
            
            setTimeout(() => {
                aiPlayer.kicking = false;
            }, 300);
        }
        
        function saveBall() {
            ball.active = false;
            score += 10;
            streak++;
            
            // Streak bonuses
            if (streak === 3) {
                score += 30;
                showBonus('3 Saves Streak! +30 points');
            }
            if (streak === 6) {
                score += 50;
                showBonus('6 Saves Streak! +50 points');
            }
            if (streak === 10) {
                score += 100;
                showBonus('10 Saves Streak! +100 points');
            }
            
            // Extra life every 100 points
            if (score % 100 === 0 && lives < 5) {
                lives++;
                showBonus('Extra Life!');
            }
            
            // Increase difficulty
            if (score % 50 === 0) {
                ballSpeed += 0.5;
            }
            
            updateDisplay();
        }
        
        function missBall() {
            ball.active = false;
            lives--;
            streak = 0;
            updateDisplay();
            
            if (lives <= 0) {
                gameOver();
            }
        }
        
        function gameOver() {
            gameRunning = false;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('gameOver').classList.remove('hidden');
            
            if (user) {
                saveScore(score);
            }
        }
        
        function restartGame() {
            document.getElementById('gameOver').classList.add('hidden');
            startGame();
        }
        
        function updateDisplay() {
            document.getElementById('score').textContent = score;
            document.getElementById('lives').textContent = lives;
            document.getElementById('streak').textContent = streak;
        }
        
        function showBonus(message) {
            // Create bonus text
            const bonus = document.createElement('div');
            bonus.textContent = message;
            bonus.style.position = 'fixed';
            bonus.style.top = '50%';
            bonus.style.left = '50%';
            bonus.style.transform = 'translate(-50%, -50%)';
            bonus.style.color = '#ffd700';
            bonus.style.fontSize = '2em';
            bonus.style.fontWeight = 'bold';
            bonus.style.textShadow = '2px 2px 4px rgba(0,0,0,0.8)';
            bonus.style.zIndex = '1000';
            bonus.style.pointerEvents = 'none';
            document.body.appendChild(bonus);
            
            // Animate bonus text
            let opacity = 1;
            let y = 0;
            const animate = () => {
                opacity -= 0.02;
                y -= 2;
                bonus.style.opacity = opacity;
                bonus.style.transform = `translate(-50%, ${-50 + y}%)`;
                
                if (opacity > 0) {
                    requestAnimationFrame(animate);
                } else {
                    document.body.removeChild(bonus);
                }
            };
            animate();
        }
        
        // Mouse controls
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            goalkeeper.x = e.clientX - rect.left - 25;
            if (goalkeeper.x < goal.x + 20) goalkeeper.x = goal.x + 20;
            if (goalkeeper.x > goal.x + goal.width - 70) goalkeeper.x = goal.x + goal.width - 70;
        });
        
        // Keyboard controls
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && gameRunning && !goalkeeper.jumping) {
                e.preventDefault();
                goalkeeper.jumping = true;
            }
        });
        
        // Auth functions
        async function login(event) {
            event.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', email, password })
            });
            
            const result = await response.json();
            if (result.success) {
                user = result.user;
                document.getElementById('authSection').classList.add('hidden');
                loadLeaderboard();
            } else {
                alert(result.message);
            }
        }
        
        async function register(event) {
            event.preventDefault();
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'register', name, email, password })
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Registration successful! Please login.');
                showLogin();
            } else {
                alert(result.message);
            }
        }
        
        function showLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('registerForm').classList.add('hidden');
        }
        
        function showRegister() {
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
        }
        
        function logout() {
            user = null;
            document.getElementById('authSection').classList.remove('hidden');
        }
        
        async function saveScore(score) {
            if (user) {
                await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'save_score', score })
                });
                loadLeaderboard();
            }
        }
        
        async function loadLeaderboard() {
            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_leaderboard' })
            });
            
            const result = await response.json();
            if (result.success) {
                const list = document.getElementById('leaderboardList');
                list.innerHTML = result.leaderboard.map((item, index) => 
                    `<div class="leaderboard-item">
                        <span>${index + 1}. ${item.name}</span>
                        <span>${item.score}</span>
                    </div>`
                ).join('');
            }
        }
        
        function showLeaderboard() {
            loadLeaderboard();
        }
        
        // Load leaderboard on page load
        loadLeaderboard();
    </script>
</body>
</html>
