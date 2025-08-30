// Kairat's Last Stand - 2D Pixel Art Game
class KairatGame {
    constructor() {
        this.canvas = document.getElementById('gameCanvas');
        this.ctx = this.canvas.getContext('2d');
        this.width = this.canvas.width;
        this.height = this.canvas.height;
        
        // Game state
        this.isPlaying = false;
        this.gameTime = 0;
        this.difficulty = 1;
        
        // Goalkeeper
        this.goalkeeper = {
            x: this.width / 2 - 25,
            y: this.height / 2 - 25,
            width: 50,
            height: 50,
            speed: 5,
            jumpPower: 15,
            isJumping: false,
            jumpVelocity: 0,
            facing: 1, // 1 for right, -1 for left
            animationFrame: 0,
            animationSpeed: 0.2
        };
        
        // Ball
        this.ball = {
            x: 0,
            y: 0,
            width: 20,
            height: 20,
            velocityX: 0,
            velocityY: 0,
            isActive: false,
            targetX: 0,
            targetY: 0,
            spin: 0,
            trail: []
        };
        
        // AI Player
        this.aiPlayer = {
            x: 100,
            y: this.height - 100,
            width: 40,
            height: 60,
            animationFrame: 0,
            isKicking: false,
            kickTimer: 0
        };
        
        // Goal
        this.goal = {
            x: this.width / 2 - 100,
            y: 50,
            width: 200,
            height: 150
        };
        
        // Game mechanics
        this.shotCooldown = 0;
        this.shotInterval = 3000; // 3 seconds between shots
        this.lastShotTime = 0;
        
        // Particles
        this.particles = [];
        
        // Input
        this.mouseX = this.width / 2;
        this.mouseY = this.height / 2;
        this.keys = {};
        
        this.setupEventListeners();
        this.init();
    }
    
    setupEventListeners() {
        // Mouse movement
        this.canvas.addEventListener('mousemove', (e) => {
            const rect = this.canvas.getBoundingClientRect();
            this.mouseX = e.clientX - rect.left;
            this.mouseY = e.clientY - rect.top;
        });
        
        // Keyboard
        document.addEventListener('keydown', (e) => {
            this.keys[e.code] = true;
            if (e.code === 'Space') {
                e.preventDefault();
                this.jump();
            }
        });
        
        document.addEventListener('keyup', (e) => {
            this.keys[e.code] = false;
        });
        
        // Touch support for mobile
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const rect = this.canvas.getBoundingClientRect();
            const touch = e.touches[0];
            this.mouseX = touch.clientX - rect.left;
            this.mouseY = touch.clientY - rect.top;
            this.jump();
        });
        
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            const rect = this.canvas.getBoundingClientRect();
            const touch = e.touches[0];
            this.mouseX = touch.clientX - rect.left;
            this.mouseY = touch.clientY - rect.top;
        });
    }
    
    init() {
        this.isPlaying = true;
        this.gameTime = 0;
        this.difficulty = 1;
        this.shotCooldown = 0;
        this.lastShotTime = 0;
        this.particles = [];
        
        // Reset goalkeeper position
        this.goalkeeper.x = this.width / 2 - 25;
        this.goalkeeper.y = this.height / 2 - 25;
        this.goalkeeper.isJumping = false;
        this.goalkeeper.jumpVelocity = 0;
        
        // Reset ball
        this.ball.isActive = false;
        this.ball.trail = [];
        
        // Start game loop
        this.gameLoop();
    }
    
    gameLoop() {
        if (!this.isPlaying) return;
        
        this.update();
        this.render();
        
        requestAnimationFrame(() => this.gameLoop());
    }
    
    update() {
        this.gameTime += 16; // Assuming 60 FPS
        
        this.updateGoalkeeper();
        this.updateBall();
        this.updateAI();
        this.updateParticles();
        this.updateShots();
        
        // Increase difficulty every 5 saves
        this.difficulty = 1 + Math.floor(gameState.score / 50);
    }
    
    updateGoalkeeper() {
        // Move goalkeeper towards mouse
        const targetX = this.mouseX - this.goalkeeper.width / 2;
        const targetY = this.mouseY - this.goalkeeper.height / 2;
        
        // Constrain to goal area
        const goalArea = {
            x: this.goal.x - 50,
            y: this.goal.y - 50,
            width: this.goal.width + 100,
            height: this.goal.height + 100
        };
        
        this.goalkeeper.x = Math.max(goalArea.x, Math.min(goalArea.x + goalArea.width - this.goalkeeper.width, targetX));
        this.goalkeeper.y = Math.max(goalArea.y, Math.min(goalArea.y + goalArea.height - this.goalkeeper.height, targetY));
        
        // Update facing direction
        if (targetX > this.goalkeeper.x) {
            this.goalkeeper.facing = 1;
        } else if (targetX < this.goalkeeper.x) {
            this.goalkeeper.facing = -1;
        }
        
        // Update jump physics
        if (this.goalkeeper.isJumping) {
            this.goalkeeper.y += this.goalkeeper.jumpVelocity;
            this.goalkeeper.jumpVelocity += 0.8; // Gravity
            
            // Land
            if (this.goalkeeper.y >= this.height / 2 - 25) {
                this.goalkeeper.y = this.height / 2 - 25;
                this.goalkeeper.isJumping = false;
                this.goalkeeper.jumpVelocity = 0;
            }
        }
        
        // Update animation
        this.goalkeeper.animationFrame += this.goalkeeper.animationSpeed;
    }
    
    updateBall() {
        if (!this.ball.isActive) return;
        
        // Update ball position
        this.ball.x += this.ball.velocityX;
        this.ball.y += this.ball.velocityY;
        this.ball.velocityY += 0.3; // Gravity
        this.ball.spin += 0.2;
        
        // Add to trail
        this.ball.trail.push({ x: this.ball.x, y: this.ball.y });
        if (this.ball.trail.length > 10) {
            this.ball.trail.shift();
        }
        
        // Check collision with goalkeeper
        if (this.checkCollision(this.ball, this.goalkeeper)) {
            this.saveShot();
            return;
        }
        
        // Check if ball hits goal
        if (this.ball.y > this.goal.y + this.goal.height) {
            this.concedeGoal();
            return;
        }
        
        // Check if ball goes out of bounds
        if (this.ball.x < 0 || this.ball.x > this.width || this.ball.y > this.height) {
            this.ball.isActive = false;
        }
    }
    
    updateAI() {
        this.aiPlayer.animationFrame += 0.1;
        
        if (this.aiPlayer.isKicking) {
            this.aiPlayer.kickTimer--;
            if (this.aiPlayer.kickTimer <= 0) {
                this.aiPlayer.isKicking = false;
            }
        }
    }
    
    updateParticles() {
        for (let i = this.particles.length - 1; i >= 0; i--) {
            const particle = this.particles[i];
            particle.x += particle.velocityX;
            particle.y += particle.velocityY;
            particle.velocityY += 0.2;
            particle.life--;
            particle.alpha = particle.life / particle.maxLife;
            
            if (particle.life <= 0) {
                this.particles.splice(i, 1);
            }
        }
    }
    
    updateShots() {
        if (!this.ball.isActive && this.gameTime - this.lastShotTime > this.shotInterval / this.difficulty) {
            this.takeShot();
        }
    }
    
    takeShot() {
        this.lastShotTime = this.gameTime;
        this.aiPlayer.isKicking = true;
        this.aiPlayer.kickTimer = 30;
        
        // Set ball position
        this.ball.x = this.aiPlayer.x + this.aiPlayer.width / 2;
        this.ball.y = this.aiPlayer.y;
        this.ball.isActive = true;
        this.ball.trail = [];
        
        // Calculate target (random point in goal)
        const targetX = this.goal.x + Math.random() * this.goal.width;
        const targetY = this.goal.y + Math.random() * this.goal.height;
        
        // Calculate trajectory
        const distance = Math.sqrt((targetX - this.ball.x) ** 2 + (targetY - this.ball.y) ** 2);
        const time = distance / (200 + this.difficulty * 50); // Speed increases with difficulty
        
        this.ball.velocityX = (targetX - this.ball.x) / time;
        this.ball.velocityY = (targetY - this.ball.y) / time - 0.5 * 0.3 * time; // Account for gravity
        
        this.ball.targetX = targetX;
        this.ball.targetY = targetY;
    }
    
    jump() {
        if (this.goalkeeper.isJumping) return;
        
        this.goalkeeper.isJumping = true;
        this.goalkeeper.jumpVelocity = -this.goalkeeper.jumpPower;
        
        // Add jump particles
        this.addParticles(this.goalkeeper.x + this.goalkeeper.width / 2, this.goalkeeper.y + this.goalkeeper.height, '#ffd700', 5);
    }
    
    saveShot() {
        this.ball.isActive = false;
        gameState.score += 10;
        gameState.streak++;
        
        // Check for streak bonuses
        if (gameState.streak === 3) {
            gameState.score += 30;
            this.showBonus('STREAK BONUS +30!');
        } else if (gameState.streak === 6) {
            gameState.score += 50;
            this.showBonus('STREAK BONUS +50!');
        } else if (gameState.streak === 10) {
            gameState.score += 100;
            this.showBonus('STREAK BONUS +100!');
        }
        
        // Check for extra life
        if (gameState.score > 0 && gameState.score % 100 === 0 && gameState.lives < 5) {
            gameState.lives++;
            this.showBonus('EXTRA LIFE!');
        }
        
        // Add save particles
        this.addParticles(this.ball.x, this.ball.y, '#00ff00', 10);
        
        updateUI();
    }
    
    concedeGoal() {
        this.ball.isActive = false;
        gameState.lives--;
        gameState.streak = 0;
        
        // Add goal particles
        this.addParticles(this.ball.x, this.ball.y, '#ff0000', 15);
        
        updateUI();
        
        if (gameState.lives <= 0) {
            this.gameOver();
        }
    }
    
    gameOver() {
        this.isPlaying = false;
        gameOver();
    }
    
    checkCollision(rect1, rect2) {
        return rect1.x < rect2.x + rect2.width &&
               rect1.x + rect1.width > rect2.x &&
               rect1.y < rect2.y + rect2.height &&
               rect1.y + rect1.height > rect2.y;
    }
    
    addParticles(x, y, color, count) {
        for (let i = 0; i < count; i++) {
            this.particles.push({
                x: x,
                y: y,
                velocityX: (Math.random() - 0.5) * 10,
                velocityY: (Math.random() - 0.5) * 10,
                color: color,
                life: 30,
                maxLife: 30,
                alpha: 1
            });
        }
    }
    
    showBonus(text) {
        // Create bonus text element
        const bonusElement = document.createElement('div');
        bonusElement.textContent = text;
        bonusElement.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #ffd700;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            z-index: 1000;
            pointer-events: none;
            animation: bonusPop 1s ease-out forwards;
        `;
        
        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes bonusPop {
                0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0; }
                50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
                100% { transform: translate(-50%, -50%) scale(1); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(bonusElement);
        
        setTimeout(() => {
            document.body.removeChild(bonusElement);
            document.head.removeChild(style);
        }, 1000);
    }
    
    render() {
        // Clear canvas
        this.ctx.fillStyle = '#87CEEB';
        this.ctx.fillRect(0, 0, this.width, this.height);
        
        // Draw grass
        this.drawGrass();
        
        // Draw goal
        this.drawGoal();
        
        // Draw AI player
        this.drawAIPlayer();
        
        // Draw goalkeeper
        this.drawGoalkeeper();
        
        // Draw ball
        if (this.ball.isActive) {
            this.drawBall();
        }
        
        // Draw particles
        this.drawParticles();
    }
    
    drawGrass() {
        this.ctx.fillStyle = '#90EE90';
        this.ctx.fillRect(0, this.height - 100, this.width, 100);
        
        // Draw grass lines
        this.ctx.strokeStyle = '#7CCD7C';
        this.ctx.lineWidth = 2;
        for (let i = 0; i < this.width; i += 40) {
            this.ctx.beginPath();
            this.ctx.moveTo(i, this.height - 100);
            this.ctx.lineTo(i, this.height);
            this.ctx.stroke();
        }
    }
    
    drawGoal() {
        // Goal posts
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.fillRect(this.goal.x, this.goal.y, 10, this.goal.height);
        this.ctx.fillRect(this.goal.x + this.goal.width - 10, this.goal.y, 10, this.goal.height);
        this.ctx.fillRect(this.goal.x, this.goal.y, this.goal.width, 10);
        
        // Goal net
        this.ctx.strokeStyle = '#FFFFFF';
        this.ctx.lineWidth = 1;
        for (let i = 0; i < this.goal.width; i += 20) {
            this.ctx.beginPath();
            this.ctx.moveTo(this.goal.x + i, this.goal.y);
            this.ctx.lineTo(this.goal.x + i, this.goal.y + this.goal.height);
            this.ctx.stroke();
        }
        for (let i = 0; i < this.goal.height; i += 20) {
            this.ctx.beginPath();
            this.ctx.moveTo(this.goal.x, this.goal.y + i);
            this.ctx.lineTo(this.goal.x + this.goal.width, this.goal.y + i);
            this.ctx.stroke();
        }
    }
    
    drawAIPlayer() {
        const frame = Math.floor(this.aiPlayer.animationFrame) % 4;
        
        // Celtic kit (white and green)
        this.ctx.fillStyle = this.aiPlayer.isKicking ? '#FFD700' : '#FFFFFF';
        this.ctx.fillRect(this.aiPlayer.x, this.aiPlayer.y, this.aiPlayer.width, this.aiPlayer.height);
        
        // Green stripes
        this.ctx.fillStyle = '#228B22';
        for (let i = 0; i < this.aiPlayer.width; i += 8) {
            this.ctx.fillRect(this.aiPlayer.x + i, this.aiPlayer.y, 4, this.aiPlayer.height);
        }
        
        // Head
        this.ctx.fillStyle = '#FDBCB4';
        this.ctx.fillRect(this.aiPlayer.x + 10, this.aiPlayer.y - 15, 20, 20);
        
        // Legs (animation)
        this.ctx.fillStyle = '#FFFFFF';
        const legOffset = this.aiPlayer.isKicking ? 5 : Math.sin(this.aiPlayer.animationFrame) * 2;
        this.ctx.fillRect(this.aiPlayer.x + 5, this.aiPlayer.y + this.aiPlayer.height, 8, 20);
        this.ctx.fillRect(this.aiPlayer.x + 15 + legOffset, this.aiPlayer.y + this.aiPlayer.height, 8, 20);
    }
    
    drawGoalkeeper() {
        const frame = Math.floor(this.goalkeeper.animationFrame) % 4;
        
        // Kairat kit (yellow and black)
        this.ctx.fillStyle = '#FFD700';
        this.ctx.fillRect(this.goalkeeper.x, this.goalkeeper.y, this.goalkeeper.width, this.goalkeeper.height);
        
        // Black stripes
        this.ctx.fillStyle = '#000000';
        for (let i = 0; i < this.goalkeeper.width; i += 10) {
            this.ctx.fillRect(this.goalkeeper.x + i, this.goalkeeper.y, 5, this.goalkeeper.height);
        }
        
        // Head
        this.ctx.fillStyle = '#FDBCB4';
        this.ctx.fillRect(this.goalkeeper.x + 15, this.goalkeeper.y - 15, 20, 20);
        
        // Arms (jumping animation)
        this.ctx.fillStyle = '#FFD700';
        if (this.goalkeeper.isJumping) {
            this.ctx.fillRect(this.goalkeeper.x - 5, this.goalkeeper.y + 10, 10, 20);
            this.ctx.fillRect(this.goalkeeper.x + this.goalkeeper.width - 5, this.goalkeeper.y + 10, 10, 20);
        } else {
            this.ctx.fillRect(this.goalkeeper.x - 5, this.goalkeeper.y + 15, 10, 15);
            this.ctx.fillRect(this.goalkeeper.x + this.goalkeeper.width - 5, this.goalkeeper.y + 15, 10, 15);
        }
        
        // Gloves
        this.ctx.fillStyle = '#FF0000';
        this.ctx.fillRect(this.goalkeeper.x - 8, this.goalkeeper.y + 10, 12, 8);
        this.ctx.fillRect(this.goalkeeper.x + this.goalkeeper.width - 4, this.goalkeeper.y + 10, 12, 8);
    }
    
    drawBall() {
        // Draw trail
        this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        this.ctx.lineWidth = 2;
        this.ctx.beginPath();
        for (let i = 0; i < this.ball.trail.length; i++) {
            const point = this.ball.trail[i];
            if (i === 0) {
                this.ctx.moveTo(point.x + this.ball.width / 2, point.y + this.ball.height / 2);
            } else {
                this.ctx.lineTo(point.x + this.ball.width / 2, point.y + this.ball.height / 2);
            }
        }
        this.ctx.stroke();
        
        // Draw ball
        this.ctx.save();
        this.ctx.translate(this.ball.x + this.ball.width / 2, this.ball.y + this.ball.height / 2);
        this.ctx.rotate(this.ball.spin);
        
        // Ball body
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.fillRect(-this.ball.width / 2, -this.ball.height / 2, this.ball.width, this.ball.height);
        
        // Ball pattern
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(-this.ball.width / 2, -this.ball.height / 2, this.ball.width, this.ball.height);
        
        // Ball lines
        this.ctx.beginPath();
        this.ctx.moveTo(-this.ball.width / 2, 0);
        this.ctx.lineTo(this.ball.width / 2, 0);
        this.ctx.moveTo(0, -this.ball.height / 2);
        this.ctx.lineTo(0, this.ball.height / 2);
        this.ctx.stroke();
        
        this.ctx.restore();
    }
    
    drawParticles() {
        for (const particle of this.particles) {
            this.ctx.save();
            this.ctx.globalAlpha = particle.alpha;
            this.ctx.fillStyle = particle.color;
            this.ctx.fillRect(particle.x - 2, particle.y - 2, 4, 4);
            this.ctx.restore();
        }
    }
}

// Initialize game when the game screen is shown
function initGame() {
    if (typeof window.kairatGame === 'undefined') {
        window.kairatGame = new KairatGame();
    } else {
        window.kairatGame.init();
    }
}
