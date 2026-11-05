<br>

<div align="center">
     <div width: 400px; height: 120px; display: flex; align-items: center; justify-content: center;">
        <img src="public/images/Logo/iBMS.png" alt="Logo" width="160">
    </div>
    
<h3>iBMS for Tensei Philippines Inc.</h3>
</div>

##  Project Overview

iBMS, short for **Intelligent Building Management System**, is a smart solution that makes buildings more comfortable, efficient, and connected. Designed for smart hotels, offices, and commercial buildings, it integrates systems like HVAC, lighting, energy, and security into a unified platform. This integration improves the user experience, streamlines operations, and supports sustainable building practices.


## Tech Stack & Tools

This project used the following technologies:

![PHP](https://img.shields.io/badge/PHP-8.4.6-blue?logo=php)
![Composer](https://img.shields.io/badge/Composer-2.8.8-533a29?logo=Composer)
![Node.js](https://img.shields.io/badge/Node.js-22.15.0-green?logo=nodedotjs)
![MySQL](https://img.shields.io/badge/MySQL-8.0.42-f29111?logo=MySQL)
![Laravel](https://img.shields.io/badge/Laravel-12.12.0-red?logo=laravel)
![Vue](https://img.shields.io/badge/Vue-3.5.13-4FC08D?logo=vue.js)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.1.1-06B6D4?logo=tailwindcss)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0.0-8E58EA?logo=inertia)
![Vite](https://img.shields.io/badge/Vite-6.2.0-yellow?logo=vite)


## Installation & Setup

Follow the steps below to set up the project locally.

### ⚠️ Prerequisites

Ensure you have **Laravel Herd** and **MySQL** properly installed and configured on your local machine.  
To ensure each project uses the correct environment settings managed by Laravel Herd, always prefix CLI commands with `herd`.

<br>

1. Clone the **repository**.

    ```sh
    git clone https://github.com/Tensei-dx/iBMS-TPI.git
    ```   
    <br>

2. Navigate to the project's **root directory**, which is typically the parked path you set in Laravel Herd.

    ```sh
    cd iBMS-TPI
    ```   
    <br>

3. Install **dependencies**.
   Laravel relies on external packages for its core functionality. Installing backend and frontend dependencies ensures all required packages are available.

    ```sh
    herd composer install && npm install
    ```
    This action will create a new folder named `vendor` and `node_modules` within the project directory.   
    <br>

4. Create a `.env` file from the `.env.example`.  
   Laravel uses a `.env` file for environment configuration. This stores configurations such as **database credentials, application settings, and third-party API keys**.

   ```sh
   cp .env.example .env
    ```   
    <br>

5. Laravel requires an **encryption key** for security purposes, used for session data encryption and password hashing.  
   Run the following command to generate an application key:

    ```sh
    herd php artisan key:generate
    ```

    This command updates the .env file’s `APP_KEY` with a randomly generated key.   
    <br>

6. Laravel applications interact with a database to store and retrieve data. The database connection settings must be configured before running migrations.

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```
    Ensure that `your_database` exists in your local MySQL server before proceeding.   
    <br>

7. Execute the command below to generate the tables. This will run all the migration files within the project, thereby creating the required tables.

    ```sh
    herd php artisan migrate --step
    ```

    Adding `--step` runs each migration in a separate branch. This makes it easier to rollback only the most recent migration instead of all migrations.   
    <br>

8. Execute the command below to populate your database with initial data.  
   This will run the seeder classes defined in the project, starting with the `DatabaseSeeder` class.

    ```sh
    herd php artisan db:seed
    ```
    You can add `--class` to specify a particular seeder class to run.

    <br>

9. Update the `APP_URL` and `VITE_HOST` in the .env file to match your project domain:

    ```sh
    # Sample
    APP_URL=https://ibms-tpi.test
    VITE_HOST=ibms-tpi.test
    ```

    Make sure **VITE_HOST** matches the domain to enable hot module reloading correctly. 

    <br>

10. By default, Laravel Herd makes your application accessible via a `.test` domain if it is located within a parked directory, and initially runs over HTTP.  
   To secure your `.test` domain with **HTTPS**, run the following command:

    ```sh
    herd secure ibms-tpi
    ```

    You can confirm SSL has been applied by running `herd secured`.   
    <br>

11. You can now access the site by running:

    ```sh
    herd open
    ```   
    <br>

12. Compile Frontend Assets.  
   Laravel applications often include frontend frameworks. Running the command below starts the **Vite development server** and builds the frontend assets in development mode.

    ```sh
    npm run dev
    ```

## Usage

Once the application is installed and running, you can begin development or testing. Here are some common commands and usage patterns:

1. If you encounter unexpected behavior or errors, try clearing the cache:

    ```sh
    herd php artisan optimize:clear
    ```

    This command clears all compiled files and cached configurations. If the issue persists, consider checking the logs in `storage/logs/laravel.log`.   
    <br>

2. If you need to reset the database and re-seed it with initial data, run:

    ```sh
    herd php artisan migrate:fresh --seed
    ```

    This will drops all existing tables, runs all migration files, and then executes the database seeders defined in the project.   
    <br>

3. To execute the application's test suite using the environment defined in `phpunit.xml`, run:

    ```sh
    herd php artisan test
    ```

    This ensures the tests run with the correct configuration, often including an isolated testing database.   
    <br>


[Back to Top](#project-overview)
