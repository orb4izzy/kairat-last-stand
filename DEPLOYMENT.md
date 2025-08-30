# 🚀 Руководство по установке на сервер ps.kz

## 📋 Требования к серверу

### Минимальные требования:
- **PHP**: 8.1 или выше
- **Composer**: для управления зависимостями
- **MySQL**: 5.7+ или PostgreSQL 10+
- **Веб-сервер**: Apache 2.4+ или Nginx 1.18+
- **SSL сертификат**: для HTTPS (рекомендуется)

### Рекомендуемые требования:
- **RAM**: минимум 1GB, рекомендуется 2GB+
- **CPU**: 1 ядро, рекомендуется 2+
- **Диск**: минимум 1GB свободного места
- **PHP расширения**: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath

## 🛠️ Пошаговая установка

### Шаг 1: Подготовка сервера

1. **Подключитесь к серверу ps.kz по SSH:**
   ```bash
   ssh your_username@ps.kz
   ```

2. **Обновите систему:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

3. **Установите необходимые пакеты:**
   ```bash
   sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath php8.1-cli unzip curl
   ```

4. **Установите Composer:**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

### Шаг 2: Настройка базы данных

1. **Войдите в MySQL:**
   ```bash
   sudo mysql -u root -p
   ```

2. **Создайте базу данных и пользователя:**
   ```sql
   CREATE DATABASE kairat_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'kairat_user'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT ALL PRIVILEGES ON kairat_game.* TO 'kairat_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

### Шаг 3: Загрузка и настройка приложения

1. **Создайте директорию для приложения:**
   ```bash
   sudo mkdir -p /var/www/kairat-game
   sudo chown -R $USER:$USER /var/www/kairat-game
   ```

2. **Загрузите файлы проекта:**
   ```bash
   # Если у вас есть git репозиторий:
   cd /var/www/kairat-game
   git clone https://github.com/your-username/kairat-last-stand.git .
   
   # Или загрузите файлы через SCP/SFTP:
   # scp -r /path/to/local/kairat-last-stand/* your_username@ps.kz:/var/www/kairat-game/
   ```

3. **Установите зависимости:**
   ```bash
   cd /var/www/kairat-game
   composer install --optimize-autoloader --no-dev
   ```

4. **Настройте права доступа:**
   ```bash
   sudo chown -R www-data:www-data /var/www/kairat-game
   sudo chmod -R 755 /var/www/kairat-game
   sudo chmod -R 775 /var/www/kairat-game/storage
   sudo chmod -R 775 /var/www/kairat-game/bootstrap/cache
   ```

### Шаг 4: Конфигурация приложения

1. **Создайте файл .env:**
   ```bash
   cp .env.example .env
   ```

2. **Отредактируйте .env файл:**
   ```bash
   nano .env
   ```

   Установите следующие значения:
   ```env
   APP_NAME="Kairat's Last Stand"
   APP_ENV=production
   APP_KEY=base64:your_generated_key_here
   APP_DEBUG=false
   APP_URL=https://ps.kz

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=adilusse_kairat_game
   DB_USERNAME=adilusse_kairat_user
   DB_PASSWORD=ua877739873100

   LOG_CHANNEL=stack
   LOG_LEVEL=error
   ```

3. **Сгенерируйте ключ приложения:**
   ```bash
   php artisan key:generate
   ```

4. **Запустите миграции:**
   ```bash
   php artisan migrate --force
   ```

5. **Очистите кэш:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Шаг 5: Настройка веб-сервера (Nginx)

1. **Создайте конфигурацию Nginx:**
   ```bash
   sudo nano /etc/nginx/sites-available/kairat-game
   ```

2. **Добавьте следующую конфигурацию:**
   ```nginx
   server {
       listen 80;
       listen [::]:80;
       server_name ps.kz www.ps.kz;
       root /var/www/kairat-game/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;

       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

3. **Активируйте сайт:**
   ```bash
   sudo ln -s /etc/nginx/sites-available/kairat-game /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

### Шаг 6: Настройка SSL (Let's Encrypt)

1. **Установите Certbot:**
   ```bash
   sudo apt install certbot python3-certbot-nginx
   ```

2. **Получите SSL сертификат:**
   ```bash
   sudo certbot --nginx -d ps.kz -d www.ps.kz
   ```

3. **Проверьте автоматическое обновление:**
   ```bash
   sudo certbot renew --dry-run
   ```

### Шаг 7: Настройка PHP-FPM

1. **Отредактируйте конфигурацию PHP:**
   ```bash
   sudo nano /etc/php/8.1/fpm/php.ini
   ```

2. **Установите рекомендуемые значения:**
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   max_execution_time = 300
   memory_limit = 256M
   ```

3. **Перезапустите PHP-FPM:**
   ```bash
   sudo systemctl restart php8.1-fpm
   ```

### Шаг 8: Настройка Cron задач

1. **Добавьте задачу в crontab:**
   ```bash
   sudo crontab -e
   ```

2. **Добавьте строку:**
   ```bash
   * * * * * cd /var/www/kairat-game && php artisan schedule:run >> /dev/null 2>&1
   ```

### Шаг 9: Настройка файрвола

1. **Настройте UFW:**
   ```bash
   sudo ufw allow 'Nginx Full'
   sudo ufw allow ssh
   sudo ufw enable
   ```

## 🔧 Дополнительные настройки

### Оптимизация производительности

1. **Включите OPcache:**
   ```bash
   sudo nano /etc/php/8.1/fpm/conf.d/10-opcache.ini
   ```
   
   Добавьте:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=4000
   opcache.revalidate_freq=2
   opcache.fast_shutdown=1
   ```

2. **Настройте кэширование в Nginx:**
   ```nginx
   location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
       expires 1y;
       add_header Cache-Control "public, immutable";
   }
   ```

### Мониторинг и логи

1. **Настройте логирование:**
   ```bash
   sudo nano /etc/nginx/sites-available/kairat-game
   ```
   
   Добавьте:
   ```nginx
   access_log /var/log/nginx/kairat-game.access.log;
   error_log /var/log/nginx/kairat-game.error.log;
   ```

2. **Настройте ротацию логов:**
   ```bash
   sudo nano /etc/logrotate.d/kairat-game
   ```
   
   Добавьте:
   ```
   /var/log/nginx/kairat-game.*.log {
       daily
       missingok
       rotate 52
       compress
       delaycompress
       notifempty
       create 644 www-data www-data
   }
   ```

## 🚨 Безопасность

### Рекомендации по безопасности:

1. **Измените пароли по умолчанию**
2. **Настройте регулярные бэкапы базы данных**
3. **Включите fail2ban для защиты от брутфорса**
4. **Регулярно обновляйте систему и зависимости**
5. **Настройте мониторинг ресурсов сервера**

### Бэкап базы данных:

```bash
# Создание бэкапа
mysqldump -u kairat_user -p kairat_game > backup_$(date +%Y%m%d_%H%M%S).sql

# Восстановление из бэкапа
mysql -u kairat_user -p kairat_game < backup_file.sql
```

## 🎮 Проверка установки

После завершения установки:

1. **Откройте браузер и перейдите на https://ps.kz**
2. **Проверьте регистрацию пользователя**
3. **Запустите игру и проверьте сохранение результатов**
4. **Проверьте таблицу лидеров**

## 🆘 Устранение неполадок

### Частые проблемы:

1. **Ошибка 500**: Проверьте права доступа к папкам storage и bootstrap/cache
2. **Ошибка базы данных**: Проверьте настройки в .env файле
3. **Статические файлы не загружаются**: Проверьте конфигурацию Nginx
4. **SSL не работает**: Проверьте сертификат и конфигурацию Nginx

### Полезные команды для диагностики:

```bash
# Проверка статуса сервисов
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
sudo systemctl status mysql

# Проверка логов
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.1-fpm.log
sudo tail -f /var/www/kairat-game/storage/logs/laravel.log

# Проверка конфигурации
sudo nginx -t
php artisan config:clear
php artisan cache:clear
```

## 📞 Поддержка

Если у вас возникли проблемы с установкой, проверьте:
1. Логи приложения в `/var/www/kairat-game/storage/logs/`
2. Логи веб-сервера в `/var/log/nginx/`
3. Логи PHP в `/var/log/php8.1-fpm/`

Удачи с установкой игры! ⚽🏆
