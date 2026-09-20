# Jenga Web School OS — Marell Academy

Full school operating system for Kenyan private schools.
Laravel 13 · PHP 8.5 · MySQL · Tailwind · spatie/permission · DomPNF · Africa's Talking · Safaricom Daraja.

## Modules
- Public website (8 pages)
- Parent Portal (OTP login + �n-Pesa pay + receipts)
- Principal Admin (students, finance, SMS, users, master search)
- DOS Academic (classes, timetable, exams, marks, report cards, promotion)
- Bursar Finance (record, statements, balances, daily, bulk upload)
- Teacher Portal (classes, timetable, marks, homework)

## Roles
principal · dos · bursar ÷ teacher · parent

## Default Users (dev)
- Principal: admin@marell.ac.ke / 123456
- DOS: dos@marell.ac.ke / 123456
- Bursar: yuriabida3@gmail.com / 123456
- Teacher: teacher@marell.ac.ke / 123456

**Change all passwords before production.**

## Termux Setup

pkg install php php-fpm composer mariadb openssl unzip curl nano
pkg install php-mysql php-mbstring php-xml php-curl php-zip php-gd php-bcmath php-tokenizer php-dom

mkdir -p $PREFIX/var/run/mysqld
mariadb-install-db --user=$(whoami) --datadir=$PREFIX/var/lib/mysql
mysqld_safe --datadir=$PREFIX/var/lib/mysql &

mariadb -u root -e "CREATE DATABASE marell CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'marell'@localhost' IDENTIFIED BY 'marell123'; GRANT ALL ON marell.* TO 'marell'@localhost'; FLUSH PRIVILEGES;"

git clone <repo> marell && cd marell
COMPOSER_MEMORY_LIMIT=-1 composer install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
ln -sf $(pwd)/storage/app/public $(pwd)/public/storage
php artisan serve --host=0.0.0.0 --port=8000
# In another session: php artisan queue:work --queue=mpesa,default

## VPS Deploy (Hostinger KVM 2)

apt update && apt upgrade -y
apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath composer unzip git supervisor

mkdir -p /var/www && cd /var/www
git clone <repo> marell && cd marell
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
nano .env  # set production values
php artisan migrate --force && php artisan dbseed --force && php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data /var/www/marell
chmod -R 755 /var/www/marell
chmod -R 775 /var/www/marell/storage /var/www/marell/bootstrap/cache

cp deploy/jenga-worker.conf /etc/supervisor/conf.d/
supervisorctl reread && supervisorctl update && supervisorctl start jenga-worker:*

bash deploy/ufw-rules.sh

apt install -y certbot python3-certbot-nginx
certbot --nginx -d marell.ac.ke -d www.marell.ac.ke

## Nginx site

server {
    listen 80;
    server_name marell.ac.ke www.marell.ac.ke;
    root /var/www/marell/public;
    index index.php;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
    client_max_body_size 20M;
}

## Security
- Payment routes throttled (10/min STK, 60/min general)
- M-Pesa callback CSRF-exempt, idempotent, queued
- Receipt URLs use Laravel signed URLs
- Atomic lockForUpdate on student rows
- Unique indexes: adm_no, transaction_code, checkout_request_id, receipt_no
- bcrypt password hashing (12 rounds)

## Support
Marell Academy · Kanduyi Road, Bungoma, Kenya · info@marell.ac.ke
