# POS Update Software

A modern Point of Sale (POS) and Inventory Management Application built with Laravel.

## Features
- Complete POS System & Fast Checkout
- Product, Stock, and Inventory Management
- Customer & Supplier Ledgers
- Purchase and Sales Management
- Financial Accounts & Voucher System
- Comprehensive Sales and Inventory Reports

## Requirements
- PHP >= 8.3
- MySQL >= 8.0 / MariaDB
- Composer

## Installation & Setup

1. Copy environment configuration:
   ```bash
   cp .env.example .env
   ```

2. Install Composer dependencies:
   ```bash
   composer install
   ```

3. Generate application key:
   ```bash
   php artisan key:generate
   ```

4. Run migrations:
   ```bash
   php artisan migrate
   ```

5. Start the local development server:
   ```bash
   php artisan serve
   ```