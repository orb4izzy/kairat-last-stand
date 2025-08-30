# ⚡ Быстрая установка на ps.kz

## 🚀 Автоматическая установка (рекомендуется)

### Шаг 1: Подготовка
```bash
# Подключитесь к серверу
ssh your_username@ps.kz

# Загрузите файлы проекта (например, через git или scp)
git clone https://github.com/your-repo/kairat-last-stand.git
cd kairat-last-stand

# Сделайте скрипт исполняемым
chmod +x deploy.sh
```

### Шаг 2: Запуск установки
```bash
# Запустите автоматический скрипт установки
./deploy.sh
```

Скрипт автоматически:
- ✅ Установит все необходимые пакеты
- ✅ Настроит базу данных MySQL
- ✅ Настроит Nginx и PHP-FPM
- ✅ Получит SSL сертификат
- ✅ Настроит файрвол
- ✅ Оптимизирует производительность

## 🛠️ Ручная установка (если нужен контроль)

### Минимальные команды:
```bash
# 1. Установка пакетов
sudo apt update && sudo apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath composer

# 2. Настройка базы данных
sudo mysql -u root -p
CREATE DATABASE kairat_game;
CREATE USER 'kairat_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON kairat_game.* TO 'kairat_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 3. Копирование файлов
sudo mkdir -p /var/www/kairat-game
sudo cp -r . /var/www/kairat-game/
cd /var/www/kairat-game

# 4. Установка зависимостей
composer install --no-dev

# 5. Настройка прав
sudo chown -R www-data:www-data /var/www/kairat-game
sudo chmod -R 775 storage bootstrap/cache

# 6. Создание .env файла
cp .env.example .env
nano .env  # Настройте базу данных и домен

# 7. Настройка Laravel
php artisan key:generate
php artisan migrate --force
php artisan config:cache

# 8. Настройка Nginx (создайте файл /etc/nginx/sites-available/kairat-game)
# См. полную конфигурацию в DEPLOYMENT.md

# 9. Активация сайта
sudo ln -s /etc/nginx/sites-available/kairat-game /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 10. SSL сертификат
sudo certbot --nginx -d ps.kz
```

## 📋 Что нужно подготовить

### Перед установкой убедитесь, что:
- ✅ У вас есть доступ к серверу ps.kz по SSH
- ✅ Домен ps.kz указывает на IP адрес сервера
- ✅ У вас есть права sudo на сервере
- ✅ Сервер работает на Ubuntu/Debian

### Понадобится ввести:
- 🔑 Пароль для пользователя базы данных
- 🌐 Домен (ps.kz)
- 📧 Email для SSL сертификата

## 🎮 После установки

1. **Откройте браузер**: https://ps.kz
2. **Зарегистрируйтесь** в игре
3. **Играйте** и проверьте сохранение результатов
4. **Проверьте таблицу лидеров**

## 🆘 Если что-то пошло не так

### Проверьте логи:
```bash
# Логи Nginx
sudo tail -f /var/log/nginx/error.log

# Логи Laravel
tail -f /var/www/kairat-game/storage/logs/laravel.log

# Логи PHP
sudo tail -f /var/log/php8.1-fpm.log
```

### Перезапустите сервисы:
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
```

### Очистите кэш Laravel:
```bash
cd /var/www/kairat-game
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📞 Поддержка

Если возникли проблемы:
1. Проверьте [полное руководство](DEPLOYMENT.md)
2. Убедитесь, что все сервисы запущены
3. Проверьте права доступа к файлам
4. Убедитесь, что домен правильно настроен

**Удачи с установкой! ⚽🏆**
