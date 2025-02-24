# prodia-app

## Project requirement
```
PHP v7.4.32
```

## Project setup
```
composer install
```

```
Buat file .env dan sesuaikan konfigurasi berikut
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prodia-app
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=prodiatest8@gmail.com
MAIL_PASSWORD=wxuodcdjstzuoetr
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=prodiatest8@gmail.com
MAIL_FROM_NAME="Test Prodia"

JWT_SECRET=ROXtAz1Kcu08W6mJCGT0zY8Vy6QXJYSpKRUzC1r5UF9jsJS9i14WFfSQulfzks8X
```

DB_DATABASE  -> Sesuaikan dengan konfigurasi database anda

### Migration
```
php artisan migrate
```

### Running for developments
```
php artisan serve
```

