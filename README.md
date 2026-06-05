# Tariff Importer Challenge

## 📋 Requisitos previos

- Docker y Docker Compose instalados en el sistema.
- Git para clonar el repositorio.

## 🚀 Levantar el entorno

1. Clonar el repositorio:
  ```bash
  git clone https://github.com/julioenrique93/tariff-importer.git
  cd tariff-importer
  ```
2. Construir:
  ```bash
  docker compose 
  ```
3. instalar laravel:
  ```bash
  docker exec -it challenge_app bash
  mkdir -p /var/www/html/storage/tmp
  chmod -R 775 /var/www/html/storage/tmp
  composer create-project laravel/laravel tariff-importer
  composer require maatwebsite/excel:^3.1
  ```
  1. config .env:
    DB_CONNECTION=pgsql
     DB_HOST=db
     DB_PORT=5432
     DB_DATABASE=tariff-importer
     DB_USERNAME=user
     DB_PASSWORD=pass
  2. correr migraciones  e insertar datos por default:
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

