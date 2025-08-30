# Kairat's Last Stand ⚽

A 2D pixel art minigame commemorating FC Kairat's historic penalty shootout victory over Celtic. Play as goalkeeper Danil Anarbekov and defend the goal against AI-controlled Celtic players!

## 🎮 Game Features

- **2D Pixel Art Graphics**: Beautiful retro-style soccer field with Kairat (yellow/black) and Celtic (white/green) kits
- **Intuitive Controls**: Mouse movement to control goalkeeper, spacebar to jump/save
- **Progressive Difficulty**: Ball speed increases every 5 saves
- **Scoring System**: 
  - +10 points per save
  - Streak bonuses: 3 saves (+30), 6 saves (+50), 10 saves (+100)
  - Extra life every 100 points
- **Lives System**: Start with 3 lives, lose one for each goal conceded
- **Leaderboard**: Track top 20 players with highest scores
- **Responsive Design**: Works on desktop and mobile devices

## 🚀 Quick Start (Standalone Version)

For immediate testing without Laravel setup:

1. Open `game-standalone.html` in your web browser
2. Click "Start Game" and begin playing!

## 🛠️ Full Laravel Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL or SQLite
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone/Download the project**
   ```bash
   cd kairat-last-stand
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   
   For SQLite (easiest):
   ```bash
   touch database/database.sqlite
   ```
   
   For MySQL/PostgreSQL, update `.env` with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kairat_game
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the server**
   ```bash
   php artisan serve
   ```

7. **Open your browser**
   Navigate to `http://localhost:8000`

## 🎯 How to Play

1. **Register/Login**: Create an account or login to track your scores
2. **Start Game**: Click "Start Game" from the main menu
3. **Controls**:
   - Move your mouse to control the goalkeeper
   - Press SPACEBAR to jump/save
   - Mobile: Touch and drag to move, tap to jump
4. **Objective**: Save as many penalty shots as possible
5. **Scoring**: 
   - Each save = +10 points
   - Streak bonuses for consecutive saves
   - Extra lives every 100 points
6. **Game Over**: When you run out of lives, your score is saved to the leaderboard

## 🏗️ Project Structure

```
kairat-last-stand/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php      # User authentication
│   │   ├── GameController.php      # Game API endpoints
│   │   └── LeaderboardController.php # Leaderboard display
│   ├── Models/
│   │   ├── User.php                # User model
│   │   └── GameSession.php         # Game session tracking
│   └── ...
├── database/migrations/            # Database schema
├── public/
│   ├── css/game.css               # Game styling
│   └── js/game.js                 # Main game logic
├── resources/views/
│   ├── layouts/app.blade.php      # Main layout
│   ├── auth/                      # Login/register pages
│   ├── game/index.blade.php       # Main game page
│   └── leaderboard/index.blade.php # Leaderboard page
├── routes/
│   └── web.php                    # Application routes
└── game-standalone.html           # Standalone version
```

## 🎨 Game Mechanics

### Goalkeeper (Kairat)
- **Kit**: Yellow and black stripes
- **Movement**: Constrained to goal area
- **Jump**: Spacebar for saving shots
- **Animation**: Idle, movement, and jump animations

### AI Player (Celtic)
- **Kit**: White with green stripes
- **Behavior**: Takes shots at random intervals
- **Animation**: Run-up and kick animation
- **Difficulty**: Shot speed increases with player score

### Ball Physics
- **Trajectory**: Parabolic flight path
- **Target**: Random points within goal frame
- **Collision**: Detection with goalkeeper for saves
- **Visual**: Spinning animation with trail effect

### Scoring System
- **Base Score**: 10 points per save
- **Streak Bonuses**:
  - 3 consecutive saves: +30 points
  - 6 consecutive saves: +50 points  
  - 10 consecutive saves: +100 points
- **Extra Lives**: +1 life every 100 points (max 5 lives)

## 🔧 API Endpoints

- `POST /api/game/start` - Initialize new game session
- `POST /api/game/save-result` - Save game results
- `GET /leaderboard` - View top 20 players

## 🎵 Sound Effects (Optional)

The game is designed to support sound effects. To add them:

1. Add audio files to `public/audio/`
2. Uncomment sound code in `public/js/game.js`
3. Add audio elements to game screens

## 📱 Mobile Support

The game includes touch controls for mobile devices:
- Touch and drag to move goalkeeper
- Tap to jump/save
- Responsive design adapts to screen size

## 🏆 Leaderboard

The leaderboard displays:
- Top 20 players by highest score
- Player name/email
- Highest score achieved
- Real-time updates after each game

## 🐛 Troubleshooting

### Common Issues

1. **Game not loading**: Check browser console for JavaScript errors
2. **Database errors**: Ensure database is properly configured and migrated
3. **Authentication issues**: Clear browser cache and cookies
4. **Performance issues**: Close other browser tabs, ensure stable internet

### Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 🤝 Contributing

Feel free to contribute improvements:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.

## 🎉 Credits

- **Game Concept**: Commemorating FC Kairat's victory over Celtic
- **Goalkeeper**: Danil Anarbekov (Kairat)
- **Framework**: Laravel 10
- **Graphics**: 2D Pixel Art
- **Physics**: Custom JavaScript implementation

---

**Enjoy defending Kairat's honor! ⚽🏆**
