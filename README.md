## INTRODUCTION

Full Stack application (Laravel API & ReactJS/InertiaJS) - a simple e-commerce shopping cart system. Users should be able to browse products, add them to a cart, update quantities, and remove items. Each product should have a name, price, and stock_quantity. It is built using Laravel for the backend APIs and ReactJS for the frontend client-facing side.

## PRE-REQUISITE FOR SETUP

-   Docker desktop
-   Web browser
-   Terminal (git bash)

## HOW TO SETUP

-   Make sure your docker desktop is up and running
-   Launch you terminal and navigate to your working directory

```bash
cd ./working_dir
```

-   Clone repository

```bash
git clone https://github.com/degod/tf-ecommerce.git
```

-   Move into the project directory

```bash
cd tf-ecommerce/
```

-   Copy env.example into .env

```bash
cp .env.example .env
```

-   Build app using docker

```bash
docker compose up -d --build
```

-   Log in to docker container bash

```bash
docker compose exec app bash
```

-   Install composer

```bash
composer install
```

-   Create an application key

```bash
php artisan key:generate
```

-   Run database migration and seeder

```bash
php artisan migrate:fresh --seed
```

-   Run automated unit/feature test for backend

```bash
composer test
```

-   Exit docker container bash

```bash
exit
```

-   Build frontend assets

```bash
docker compose --profile build run --rm assets
```

-   To run frontend assets in dev mode

```bash
docker compose --profile dev up -d --build
```

-   To run vite in dev mode

```bash
docker compose --profile dev up vite -d --build
```

-   To get into the frontend container bash

```bash
docker exec -it tf-ecommerce-vite sh
```

-   To access application, visit
    `http://localhost:9030`

-   To Login as "Super Admin":

    -   `U:  test@mail.com`
    -   `P:  password`

-   To Login as any user:

    -   `U:  select from the list of users (or test@mail.com)`
    -   `P:  password`

-   To access application's database, visit

    -   `http://localhost:9031`

-   To access application's mailhost, visit
    -   `http://localhost:8025`