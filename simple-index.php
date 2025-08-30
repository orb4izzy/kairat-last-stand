<?php
// Простая версия игры без Laravel зависимостей
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
    <title>Kairat's Last Stand</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Arial', sans-serif;
            color: white;
            overflow: hidden;
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
            font-size: 2.5em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .game-subtitle {
            font-size: 1.2em;
            margin: 10px 0;
            opacity: 0.9;
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
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .auth-form button {
            padding: 10px;
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
        }
        
        .score-display {
            font-size: 1.5em;
            margin: 10px 0;
        }
        
        .lives-display {
            font-size: 1.2em;
            margin: 10px 0;
        }
        
        .game-canvas {
            border: 3px solid #fff;
            border-radius: 10px;
            background: #4a7c59;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
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
        
        .leaderboard {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            padding: 20px;
            border-radius: 10px;
            max-width: 300px;
            backdrop-filter: blur(10px);
        }
        
        .leaderboard h3 {
            margin: 0 0 15px 0;
            color: #ff6b35;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1 class="game-title">⚽ Kairat's Last Stand</h1>
            <p class="game-subtitle">Defend the goal as Danil Anarbekov!</p>
        </div>
        
        <div class="auth-section" id="authSection">
            <div id="loginForm">
                <h3>Login</h3>
                <form class="auth-form" onsubmit="login(event)">
                    <input type="email" id="loginEmail" placeholder="Email" required>
                    <input type="password" id="loginPassword" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <button class="control-button" onclick="showRegister()">Register</button>
            </div>
            
            <div id="registerForm" class="hidden">
                <h3>Register</h3>
                <form class="auth-form" onsubmit="register(event)">
                    <input type="text" id="registerName" placeholder="Name" required>
                    <input type="email" id="registerEmail" placeholder="Email" required>
                    <input type="password" id="registerPassword" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>
                <button class="control-button" onclick="showLogin()">Login</button>
            </div>
        </div>
        
        <div class="game-info">
            <div class="score-display">Score: <span id="score">0</span></div>
            <div class="lives-display">Lives: <span id="lives">3</span></div>
        </div>
        
        <canvas id="gameCanvas" class="game-canvas" width="800" height="400"></canvas>
        
        <div class="game-controls">
            <button class="control-button" onclick="startGame()">Start Game</button>
            <button class="control-button" onclick="showLeaderboard()">Leaderboard</button>
            <button class="control-button" onclick="logout()">Logout</button>
        </div>
    </div>
    
    <div class="leaderboard" id="leaderboard">
        <h3>🏆 Top Players</h3>
        <div id="leaderboardList">Loading...</div>
    </div>
    
    <script>
        // Game variables
        let gameRunning = false;
        let score = 0;
        let lives = 3;
        let gameSpeed = 2;
        let streak = 0;
        let user = null;
        
        // Canvas setup
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        
        // Game objects
        let goalkeeper = { x: 400, y: 300, width: 40, height: 60, jumping: false };
        let ball = { x: 0, y: 0, vx: 0, vy: 0, radius: 15, active: false };
        let aiPlayer = { x: 100, y: 200, width: 30, height: 50, kicking: false };
        
        // Game functions
        function startGame() {
            if (!gameRunning) {
                gameRunning = true;
                score = 0;
                lives = 3;
                streak = 0;
                gameSpeed = 2;
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
                goalkeeper.y -= 5;
                if (goalkeeper.y < 200) {
                    goalkeeper.jumping = false;
                }
            } else if (goalkeeper.y < 300) {
                goalkeeper.y += 2;
            }
            
            // Update ball
            if (ball.active) {
                ball.x += ball.vx;
                ball.y += ball.vy;
                ball.vy += 0.3; // gravity
                
                // Check collision with goalkeeper
                if (ball.x > goalkeeper.x - 20 && ball.x < goalkeeper.x + 60 &&
                    ball.y > goalkeeper.y - 10 && ball.y < goalkeeper.y + 70) {
                    saveBall();
                }
                
                // Check if ball went past goalkeeper
                if (ball.y > 350) {
                    missBall();
                }
            } else {
                // Randomly kick ball
                if (Math.random() < 0.01) {
                    kickBall();
                }
            }
        }
        
        function draw() {
            // Clear canvas
            ctx.fillStyle = '#4a7c59';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw goal
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.strokeRect(350, 200, 100, 150);
            
            // Draw goalkeeper (Kairat - yellow/black)
            ctx.fillStyle = '#ffd700';
            ctx.fillRect(goalkeeper.x, goalkeeper.y, goalkeeper.width, goalkeeper.height);
            ctx.fillStyle = '#000';
            ctx.fillRect(goalkeeper.x + 5, goalkeeper.y + 5, 30, 10);
            ctx.fillRect(goalkeeper.x + 5, goalkeeper.y + 20, 30, 10);
            ctx.fillRect(goalkeeper.x + 5, goalkeeper.y + 35, 30, 10);
            
            // Draw AI player (Celtic - white/green)
            ctx.fillStyle = '#fff';
            ctx.fillRect(aiPlayer.x, aiPlayer.y, aiPlayer.width, aiPlayer.height);
            ctx.fillStyle = '#228B22';
            ctx.fillRect(aiPlayer.x + 5, aiPlayer.y + 5, 20, 5);
            ctx.fillRect(aiPlayer.x + 5, aiPlayer.y + 15, 20, 5);
            ctx.fillRect(aiPlayer.x + 5, aiPlayer.y + 25, 20, 5);
            
            // Draw ball
            if (ball.active) {
                ctx.fillStyle = '#fff';
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
                ctx.fill();
                
                // Ball pattern
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
                ctx.stroke();
            }
        }
        
        function kickBall() {
            ball.x = aiPlayer.x + 30;
            ball.y = aiPlayer.y + 25;
            ball.vx = gameSpeed + Math.random() * 2;
            ball.vy = -2 - Math.random() * 2;
            ball.active = true;
            aiPlayer.kicking = true;
            
            setTimeout(() => {
                aiPlayer.kicking = false;
            }, 500);
        }
        
        function saveBall() {
            ball.active = false;
            score += 10;
            streak++;
            
            // Streak bonuses
            if (streak === 3) score += 30;
            if (streak === 6) score += 50;
            if (streak === 10) score += 100;
            
            // Extra life every 100 points
            if (score % 100 === 0 && lives < 5) {
                lives++;
            }
            
            // Increase difficulty
            if (score % 50 === 0) {
                gameSpeed += 0.5;
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
            if (user) {
                saveScore(score);
            }
            alert(`Game Over! Final Score: ${score}`);
        }
        
        function updateDisplay() {
            document.getElementById('score').textContent = score;
            document.getElementById('lives').textContent = lives;
        }
        
        // Mouse controls
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            goalkeeper.x = e.clientX - rect.left - 20;
            if (goalkeeper.x < 350) goalkeeper.x = 350;
            if (goalkeeper.x > 410) goalkeeper.x = 410;
        });
        
        // Keyboard controls
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && gameRunning && !goalkeeper.jumping) {
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
