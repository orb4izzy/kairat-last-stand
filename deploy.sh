#!/bin/bash

# 🚀 Скрипт автоматической установки Kairat's Last Stand на сервер ps.kz
# Автор: AI Assistant
# Версия: 1.0

set -e  # Остановить выполнение при ошибке

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для вывода сообщений
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Проверка прав root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_error "Не запускайте этот скрипт от имени root!"
        print_message "Используйте: sudo -u your_username ./deploy.sh"
        exit 1
    fi
}

# Проверка операционной системы
check_os() {
    if [[ ! -f /etc/debian_version ]]; then
        print_error "Этот скрипт предназначен для Debian/Ubuntu систем"
        exit 1
    fi
    print_message "Операционная система: $(lsb_release -d | cut -f2)"
}

# Обновление системы
update_system() {
    print_header "Обновление системы"
    sudo apt update && sudo apt upgrade -y
    print_message "Система обновлена"
}

# Установка необходимых пакетов
install_packages() {
    print_header "Установка необходимых пакетов"
    
    # Обновление списка пакетов
    sudo apt update
    
    # Установка основных пакетов
    sudo apt install -y \
        nginx \
        mysql-server \
        php8.1-fpm \
        php8.1-mysql \
        php8.1-mbstring \
        php8.1-xml \
        php8.1-curl \
        php8.1-zip \
        php8.1-bcmath \
        php8.1-cli \
        php8.1-common \
        php8.1-opcache \
        unzip \
        curl \
        git \
        ufw \
        certbot \
        python3-certbot-nginx
    
    print_message "Пакеты установлены"
}

# Установка Composer
install_composer() {
    print_header "Установка Composer"
    
    if command -v composer &> /dev/null; then
        print_message "Composer уже установлен"
        return
    fi
    
    cd /tmp
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
    
    print_message "Composer установлен"
}

# Настройка MySQL
setup_mysql() {
    print_header "Настройка MySQL"
    
    # Запуск MySQL
    sudo systemctl start mysql
    sudo systemctl enable mysql
    
    # Создание базы данных и пользователя
    read -p "Введите пароль для пользователя базы данных kairat_user: " -s DB_PASSWORD
    echo
    
    mysql -u root -p << EOF
CREATE DATABASE IF NOT EXISTS kairat_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'kairat_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON kairat_game.* TO 'kairat_user'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    print_message "База данных настроена"
}

# Создание директории приложения
create_app_directory() {
    print_header "Создание директории приложения"
    
    sudo mkdir -p /var/www/kairat-game
    sudo chown -R $USER:$USER /var/www/kairat-game
    
    print_message "Директория создана: /var/www/kairat-game"
}

# Копирование файлов приложения
copy_app_files() {
    print_header "Копирование файлов приложения"
    
    # Копирование всех файлов проекта
    cp -r . /var/www/kairat-game/
    cd /var/www/kairat-game
    
    # Удаление ненужных файлов
    rm -f .env
    rm -rf node_modules
    rm -rf .git
    
    print_message "Файлы приложения скопированы"
}

# Установка зависимостей
install_dependencies() {
    print_header "Установка зависимостей PHP"
    
    cd /var/www/kairat-game
    composer install --optimize-autoloader --no-dev
    
    print_message "Зависимости установлены"
}

# Настройка прав доступа
setup_permissions() {
    print_header "Настройка прав доступа"
    
    sudo chown -R www-data:www-data /var/www/kairat-game
    sudo chmod -R 755 /var/www/kairat-game
    sudo chmod -R 775 /var/www/kairat-game/storage
    sudo chmod -R 775 /var/www/kairat-game/bootstrap/cache
    
    print_message "Права доступа настроены"
}

# Создание .env файла
create_env_file() {
    print_header "Создание конфигурационного файла"
    
    cd /var/www/kairat-game
    
    # Запрос конфигурации
    read -p "Введите домен (например: ps.kz): " APP_DOMAIN
    read -p "Введите пароль базы данных: " -s DB_PASSWORD
    echo
    
    # Создание .env файла
    cat > .env << EOF
APP_NAME="Kairat's Last Stand"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$APP_DOMAIN

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kairat_game
DB_USERNAME=kairat_user
DB_PASSWORD=$DB_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@$APP_DOMAIN"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
EOF
    
    print_message "Конфигурационный файл создан"
}

# Настройка Laravel
setup_laravel() {
    print_header "Настройка Laravel"
    
    cd /var/www/kairat-game
    
    # Генерация ключа приложения
    php artisan key:generate
    
    # Запуск миграций
    php artisan migrate --force
    
    # Очистка и кэширование
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    print_message "Laravel настроен"
}

# Настройка Nginx
setup_nginx() {
    print_header "Настройка Nginx"
    
    # Создание конфигурации сайта
    sudo tee /etc/nginx/sites-available/kairat-game > /dev/null << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $APP_DOMAIN www.$APP_DOMAIN;
    root /var/www/kairat-game/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Активация сайта
    sudo ln -sf /etc/nginx/sites-available/kairat-game /etc/nginx/sites-enabled/
    sudo rm -f /etc/nginx/sites-enabled/default
    
    # Проверка конфигурации
    sudo nginx -t
    
    # Перезапуск Nginx
    sudo systemctl restart nginx
    sudo systemctl enable nginx
    
    print_message "Nginx настроен"
}

# Настройка PHP-FPM
setup_php_fpm() {
    print_header "Настройка PHP-FPM"
    
    # Настройка PHP
    sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/8.1/fpm/php.ini
    sudo sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/8.1/fpm/php.ini
    sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini
    sudo sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.1/fpm/php.ini
    
    # Настройка OPcache
    sudo tee /etc/php/8.1/fpm/conf.d/10-opcache.ini > /dev/null << EOF
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
EOF
    
    # Перезапуск PHP-FPM
    sudo systemctl restart php8.1-fpm
    sudo systemctl enable php8.1-fpm
    
    print_message "PHP-FPM настроен"
}

# Настройка SSL
setup_ssl() {
    print_header "Настройка SSL сертификата"
    
    print_warning "Для получения SSL сертификата домен должен указывать на этот сервер"
    read -p "Продолжить настройку SSL? (y/n): " -n 1 -r
    echo
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        sudo certbot --nginx -d $APP_DOMAIN -d www.$APP_DOMAIN --non-interactive --agree-tos --email admin@$APP_DOMAIN
        
        # Настройка автоматического обновления
        (crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -
        
        print_message "SSL сертификат настроен"
    else
        print_warning "SSL сертификат не настроен. Настройте его позже командой:"
        print_message "sudo certbot --nginx -d $APP_DOMAIN -d www.$APP_DOMAIN"
    fi
}

# Настройка файрвола
setup_firewall() {
    print_header "Настройка файрвола"
    
    sudo ufw allow 'Nginx Full'
    sudo ufw allow ssh
    sudo ufw --force enable
    
    print_message "Файрвол настроен"
}

# Настройка Cron задач
setup_cron() {
    print_header "Настройка Cron задач"
    
    # Добавление задачи Laravel
    (crontab -l 2>/dev/null; echo "* * * * * cd /var/www/kairat-game && php artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    print_message "Cron задачи настроены"
}

# Финальная проверка
final_check() {
    print_header "Финальная проверка"
    
    # Проверка статуса сервисов
    echo "Проверка статуса сервисов:"
    sudo systemctl status nginx --no-pager -l
    sudo systemctl status php8.1-fpm --no-pager -l
    sudo systemctl status mysql --no-pager -l
    
    # Проверка конфигурации Nginx
    sudo nginx -t
    
    print_message "Установка завершена!"
    print_message "Откройте браузер и перейдите на https://$APP_DOMAIN"
    print_message "Для проверки логов используйте: sudo tail -f /var/log/nginx/error.log"
}

# Основная функция
main() {
    print_header "🚀 Установка Kairat's Last Stand"
    
    check_root
    check_os
    
    print_warning "Этот скрипт установит игру на сервер. Продолжить? (y/n)"
    read -p "" -n 1 -r
    echo
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_message "Установка отменена"
        exit 0
    fi
    
    update_system
    install_packages
    install_composer
    setup_mysql
    create_app_directory
    copy_app_files
    install_dependencies
    setup_permissions
    create_env_file
    setup_laravel
    setup_nginx
    setup_php_fpm
    setup_ssl
    setup_firewall
    setup_cron
    final_check
    
    print_header "🎉 Установка завершена успешно!"
    print_message "Игра доступна по адресу: https://$APP_DOMAIN"
}

# Запуск скрипта
main "$@"
