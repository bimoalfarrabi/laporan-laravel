<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About This Project

This is a reporting application built with Laravel. It provides a comprehensive system for managing users, roles, permissions, and dynamic report types.

## Key Features

*   **Dynamic Report Management:**
    *   Create custom report types with a dynamic form builder (defining fields like text, date, number, file, etc.).
    *   Users can submit reports based on the defined report types.
    *   Supervisors (Danru) can approve or reject submitted reports.
    *   Automatic compression for all uploaded images to save storage space.

*   **User Management:**
    *   Full CRUD (Create, Read, Update, Delete) functionality for users.
    *   Soft delete and restore capabilities for users.
    *   Forced password reset functionality.

*   **Role & Permission Management:**
    *   Granular permission system powered by `spatie/laravel-permission`.
    *   Superadmins can create, edit, and soft-delete roles through the UI.
    *   An archive page allows for restoring deleted roles.
    *   Easily assign permissions to roles in a grouped interface.



## Getting Started

Follow these steps to set up the project locally:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/bimoalfarrabi/laporan-laravel.git
    cd laporan
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Environment Setup:**
    -   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    -   Generate an application key:
        ```bash
        php artisan key:generate
        ```
    -   Edit the `.env` file and configure your database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

4.  **Database Migration and Seeding:**
    -   Run migrations and seed the database with initial data (including roles, permissions, and default users):
        ```bash
        php artisan migrate:fresh --seed
        ```

5.  **Install Node.js Dependencies:**
    ```bash
    npm install
    ```

6.  **Build Frontend Assets:**
    -   Compile the frontend assets (CSS, JavaScript):
        ```bash
        npm run build
        ```
    -   For development with hot reloading, use:
        ```bash
        npm run dev
        ```

7.  **Start the Local Server:**
    ```bash
    php artisan serve
    ```

8.  **Access the Application:**
    -   Open your web browser and navigate to the URL provided by `php artisan serve` (e.g., `http://127.0.0.1:8000`).

9.  **Default Login Credentials:**
    -   **Superadmin:**
        -   Email: `superadmin@example.com`
        -   Password: `password`

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
