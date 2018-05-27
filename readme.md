# Direction Application

## Clone the repository
```
git clone https://github.com/mleung07/direction-app.git
```

```
cd direction-app
```

## Copy Environment Setting
```
cp .env.example .env
```

## Install Lumen and it's dependecies
```
composer install
```

## Run Docker Images
```
docker-compose up -d
```

You may need to wait for a few minute for building up the containers.

## Enter the lumen container
```
docker-compose exec app bash
```

## Database migrations ( Inside the container )
```
php artisan migrate
```

## Unit Tests ( Inside the container )
```
php ./vendor/phpunit/phpunit/phpunit
```

## Base URL of API: http://192.168.99.100:8080/

### Submit locations
```
POST http://192.168.99.100:8080/route
```

### Get Route
```
GET http://192.168.99.100:8080/route/<token>
```