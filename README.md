# ServiceHub

**ServiceHub** is a multi-role REST API platform built with **Laravel 12** that connects service vendors with customers. It provides a full vendor lifecycle — from registration and admin approval to service listing — backed by secure OAuth2 authentication, role-based access control, queued email notifications, and image management.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture & Roles](#architecture--roles)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)
- [Running the Project](#running-the-project)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [License](#license)

---

## Features

### 🔐 Authentication & Authorization
- User registration (customer) and vendor registration (with business profile)
- OAuth2 token-based login via **Laravel Passport** (Password Grant)
- Role-based access control via **Spatie Laravel Permission** (`admin`, `vendor`, `customer`)
- Vendor login is blocked until the account is approved by an admin

### 🏪 Vendor Management (Admin)
- List all vendors with pagination
- View vendor details
- Approve or reject vendor applications
- Soft-delete vendors
- Automated email notification sent to vendors on approval/rejection via a queued listener

### 🛠️ Service Management (Vendor)
- Full CRUD for services (create, read, update, delete)
- Each service belongs to a category and a vendor
- Supports multiple image uploads per service (JPEG, PNG, WebP; max 2 MB each)
- Services are uniquely named per vendor and auto-generate SEO-friendly slugs
- Active / inactive status management

### 📂 Service Categories (Admin)
- Admins can create, update, and delete service categories
- Vendors can view the category list to use when listing services
- Slugified, with optional description and status toggle

### 📧 Event-Driven Email Notifications
- `VendorStatusUpdatedEvent` fires when a vendor is approved or rejected
- `SendVendorStatusEmailListener` handles the event and dispatches the appropriate queued email (`VendorApprovedMail` / `VendorRejectedMail`)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Authentication | Laravel Passport (OAuth2) |
| Authorization | Spatie Laravel Permission |
| Database | MySQL |
| Queue | Database queue driver |
| Mail | Configurable (SMTP / Mailgun / log) |
| Asset Bundling | Vite + Tailwind CSS v4 |
| Testing | Pest PHP |

---

## Architecture & Roles

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│   Customer   │       │    Vendor    │       │    Admin     │
│  (register,  │       │  (register,  │       │  (manage     │
│    login)    │       │   login,     │       │  vendors &   │
│              │       │   services)  │       │  categories) │
└──────────────┘       └──────────────┘       └──────────────┘
        │                     │                      │
        └─────────────────────┴──────────────────────┘
                              │
                    Laravel REST API (Passport)
```

### Vendor Lifecycle

```
Register → Pending → Admin Reviews → Approved ✅ / Rejected ❌
                                          │               │
                                     Email sent       Email sent
                                     (queued)         (queued)
                                          │
                                   Can now login
                                   & list services
```

---

## Database Schema

| Table | Key Columns |
|---|---|
| `users` | id, name, email, password |
| `vendors` | id, user_id (FK), business_name, logo, phone, address, city, status (0=pending, 1=approved, 2=rejected, 3=suspended), deleted_at |
| `service_categories` | id, name, slug, description, status |
| `services` | id, vendor_id (FK), category_id (FK), name, slug, short_description, description, price, status (0=inactive, 1=active), deleted_at |
| `service_images` | id, service_id (FK), path, alt_text |
| `oauth_*` | Passport OAuth2 tables |
| `permissions` / `roles` | Spatie permission tables |

---

## API Endpoints

### Public Routes

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Register a new customer account |
| `POST` | `/api/vendor/register` | Register a new vendor (pending approval) |
| `POST` | `/api/login` | Login and receive an OAuth2 access token |

### Admin Routes — `auth:api` + `role:admin`

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/users` | List all users |
| `GET` | `/api/vendors` | List all vendors (paginated) |
| `GET` | `/api/vendors/{id}` | Get a specific vendor |
| `DELETE` | `/api/vendors/{id}` | Soft-delete a vendor |
| `PATCH` | `/api/vendors/{id}/approve` | Approve a vendor |
| `PATCH` | `/api/vendors/{id}/reject` | Reject a vendor |
| `POST` | `/api/service-categories` | Create a service category |
| `PUT/PATCH` | `/api/service-categories/{id}` | Update a service category |
| `DELETE` | `/api/service-categories/{id}` | Delete a service category |

### Vendor Routes — `auth:api` + `role:vendor`

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/services` | List all services for authenticated vendor |
| `POST` | `/api/services` | Create a new service with images |
| `GET` | `/api/services/{id}` | Get a specific service |
| `PUT/PATCH` | `/api/services/{id}` | Update a service |
| `DELETE` | `/api/services/{id}` | Delete a service |

### Shared Routes — `auth:api` + `role:admin|vendor`

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/service-categories` | List all service categories |

---

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 18 & npm
- MySQL
- A mail service (or use `log` driver for local development)

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/your-username/ServiceHub.git
cd ServiceHub
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Set up environment**

```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure your database** in `.env`:

```env
DB_DATABASE=servicehub
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

**5. Run migrations and seed roles**

```bash
php artisan migrate
php artisan db:seed
```

**6. Install Passport and create OAuth client**

```bash
php artisan passport:install
```

Copy the generated **Password Grant Client ID and Secret** into your `.env`:

```env
PASSPORT_CLIENT_ID=your_client_id
PASSPORT_CLIENT_SECRET=your_client_secret
PASSPORT_LOGIN_ENDPOINT=oauth/token
```

**7. Install frontend dependencies**

```bash
npm install
```

---

## Environment Variables

Key variables to configure in `.env`:

| Variable | Description |
|---|---|
| `APP_URL` | Base URL of your application |
| `DB_*` | MySQL database credentials |
| `QUEUE_CONNECTION` | Set to `database` (default) or `redis` |
| `MAIL_MAILER` | Mail driver (`smtp`, `mailgun`, `log`) |
| `MAIL_FROM_ADDRESS` | Sender email address |
| `PASSPORT_CLIENT_ID` | OAuth2 Password Grant client ID |
| `PASSPORT_CLIENT_SECRET` | OAuth2 Password Grant client secret |
| `PASSPORT_LOGIN_ENDPOINT` | OAuth token endpoint (default: `oauth/token`) |

---

## Running the Project

### One-command setup (installs, migrates, and builds everything)

```bash
composer setup
```

### Start the development server (API + Queue + Vite)

```bash
composer dev
```

This runs three concurrent processes:
- `php artisan serve` — Laravel dev server
- `php artisan queue:listen` — processes queued emails
- `npm run dev` — Vite asset bundler

---

## Testing

```bash
composer test
```

Or directly:

```bash
php artisan test
```

Tests are written with [Pest PHP](https://pestphp.com/).

---

## Project Structure

```
app/
├── Events/
│   └── VendorStatusUpdatedEvent.php   # Fired on vendor approve/reject
├── Http/
│   └── Controllers/
│       ├── Admin/
│       │   └── VendorController.php   # Admin vendor management
│       ├── Vendor/
│       │   └── ServiceController.php  # Vendor service CRUD
│       ├── AuthController.php         # Register & login
│       └── ServiceCategoryController.php
├── Listeners/
│   └── SendVendorStatusEmailListener.php  # Sends queued emails
├── Mail/
│   ├── VendorApprovedMail.php
│   └── VendorRejectedMail.php
└── Models/
    ├── User.php
    ├── Vendor.php
    ├── Service.php
    ├── ServiceCategory.php
    └── ServiceImage.php

database/
├── migrations/        # All table schemas
└── seeders/
    ├── DatabaseSeeder.php
    └── RoleSeeder.php # Seeds admin, vendor, customer roles

routes/
└── api.php            # All API route definitions
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
