# PerfectITSecurity — Global IT Support & Cybersecurity Platform

> Enterprise-grade international IT support, ITSM, and cybersecurity operations platform built with Laravel, Blade, Alpine.js, Tailwind CSS, and Vite.
>
> 🌐 **Live Website Design Demo**: **[https://muhibchy.github.io/perfectitsecurity/](https://muhibchy.github.io/perfectitsecurity/)**

---

> [!IMPORTANT]
> **Production Deployment Status**: This repository represents source code and repository preparation only. The application is **NOT approved for production deployment** in its current state. The current Laravel (9.52.x) and PHP (8.0.x) versions and dependency tree require a separate security-maintenance and upgrade review before any production release.
>
> **Credential Safety Notice**: Production credentials, live API keys, and customer data must **NEVER** be committed to version control. Always use local `.env` files and secure vault/secret stores for sensitive keys.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Key Platform Capabilities](#key-platform-capabilities)
  - [Client Self-Service Portal](#client-self-service-portal)
  - [Staff & Administration Workspace](#staff--administration-workspace)
  - [ITSM, Remote Support & Site Visits](#itsm-remote-support--site-visits)
  - [Dynamic Service Catalog & Quoting](#dynamic-service-catalog--quoting)
  - [IT Ticketing & Automated SLA Escalations](#it-ticketing--automated-sla-escalations)
  - [Multi-Gateway Billing & Payments](#multi-gateway-billing--payments)
  - [AI Support & Automation Gateway](#ai-support--automation-gateway)
  - [Backup Orchestration & Disaster Recovery](#backup-orchestration--disaster-recovery)
  - [Enterprise Security & RBAC](#enterprise-security--rbac)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Quick Start / Local Setup](#quick-start--local-setup)
  - [1. Clone Repository](#1-clone-repository)
  - [2. Install Backend Dependencies](#2-install-backend-dependencies)
  - [3. Configure Environment Variables](#3-configure-environment-variables)
  - [4. Generate Application Key](#4-generate-application-key)
  - [5. Setup Database & Migrations](#5-setup-database--migrations)
  - [6. Seed Demo Data](#6-seed-demo-data)
  - [7. Install & Build Frontend Assets](#7-install--build-frontend-assets)
  - [8. Link Public Storage](#8-link-public-storage)
  - [9. Start Development Server](#9-start-development-server)
- [Windows One-Click Launcher](#windows-one-click-launcher)
- [Background Workers & Task Scheduling](#background-workers--task-scheduling)
- [Testing & Quality Assurance](#testing--quality-assurance)
- [Configuration & External Integrations](#configuration--external-integrations)
  - [Payment Gateways](#payment-gateways)
  - [AI Assistant & Agent Gateway](#ai-assistant--agent-gateway)
  - [Email & SMS Notifications](#email--sms-notifications)
- [Security Reporting & Contribution](#security-reporting--contribution)
- [License](#license)

---

## Project Overview

**PerfectITSecurity** is a comprehensive, multi-role web platform designed for managed service providers (MSPs), cybersecurity consultancies, and IT support agencies.

The system combines a modern, high-aesthetic public website with interactive 3D WebGL scenes, an authenticated multi-tenant client portal, and an administrative operational back-office managing tickets, SLA monitoring, multi-currency accounting, technician dispatch, ITSM workflows, and encrypted backups.

---

## Key Platform Capabilities

### Client Self-Service Portal
- **Operational Dashboard**: Real-time project tracking, active service retainers, open support requests, and unpaid invoices.
- **Quotation Workflow**: Interactive review of formal technical proposals with digital acceptance/rejection and automatic work order creation.
- **Document & Attachment Vault**: Isolated document center with tenant access control for contracts, audit reports, and service agreements.
- **Multi-Factor Verification**: Email OTP and SMS phone verification with rate limiting, attempt throttling, and anti-tamper validation.

### Staff & Administration Workspace
- **Role-Based Access Control (RBAC)**: Distinct permissions for Super Admins, Support Agents, Field Technicians, Sales Agents, and Finance Managers.
- **Visual Sales Pipeline**: Lead tracking from initial service request inquiry through estimation, quotation, customer signoff, and job completion.
- **Manual Work Orders & Custom Billing**: Ability for authorized staff to create customized work orders, manual invoice line items, and ad-hoc service sessions.
- **Operational Health Center**: Real-time monitoring of database health, background worker status, cache hit ratios, and exception logs.

### ITSM, Remote Support & Site Visits
- **ITSM Module**: Incident, problem, asset lifecycle, and change management modules following ITIL principles.
- **Remote Support Sessions**: Customer consent-gated remote assistance sessions with auditing and session logs.
- **Site Visit Scheduling**: Field engineer dispatch management, customer visit confirmation, and completion tracking.

### Dynamic Service Catalog & Quoting
- **Categorized Offerings**: Cybersecurity auditing, managed firewalls, cloud migrations, emergency response, and IT compliance.
- **Automated PDF Engine**: Server-side DomPDF compilation for quotes, invoices, and payment receipts with itemized tax and multi-currency pricing.

### IT Ticketing & Automated SLA Escalations
- **Ticketing Lifecycle**: Threaded support communication, internal private staff notes, urgency levels, and file attachment inspection.
- **Automated SLA Engine**: Scheduled background checks for first-response and resolution deadlines with automatic notification escalation.

### Multi-Gateway Billing & Payments
- **Multi-Currency Support**: Support for BDT, USD, GBP, and EUR with automatic or manual exchange rate synchronization.
- **Flexible Gateway Integrations**: Stripe Checkout integration, bKash tokenized payment checkout, Nagad, Rocket, and PayPal payment workflows.
- **Customer Wallets**: In-app wallet management, balance top-ups, transaction ledger, and wallet-based invoice settlement.

### AI Support & Automation Gateway
- **Local-First Architecture**: Default integration with local Ollama instances (e.g. `llama3.2`, `qwen2.5-coder`), operating fully air-gapped without external cloud API requirements.
- **Opt-in Cloud Fallback**: Guarded fallback to cloud LLM APIs (OpenAI, OpenRouter) only when explicitly authorized in configuration.
- **Prompt Safety & Privacy**: Strict input sanitization, zero logging of raw tokens or completion prompts, and prompt size bounding.

### Backup Orchestration & Disaster Recovery
- **Multi-Level Automated Backups**: Scheduled full-system, database-only, and files-only backups managed via artisan commands.
- **Client-Side AES-256 Encryption**: Encrypted before writing to disk with dedicated isolated encryption keys.
- **Tamper Verification**: Automated checksum validation and test restoration routines.

### Enterprise Security & RBAC
- **TOTP Multi-Factor Authentication**: Google Authenticator-compatible QR code enrollment and challenge verification for administrative staff.
- **Hardened HTTP Headers**: Strict Content Security Policy (CSP), frame denial, XSS protection, and MIME sniffing protection.
- **Tenant Isolation**: Policy-enforced query scopes preventing Insecure Direct Object Reference (IDOR) attacks across accounts.

---

## Technology Stack

| Layer | Component | Version / Library |
|---|---|---|
| **Backend Framework** | Laravel | 9.52.22 |
| **Runtime** | PHP | ^8.0.2 (PHP 8.0.30 tested) |
| **Frontend Runtime** | Alpine.js | 3.16.3 |
| **Styling** | Tailwind CSS | 3.4.19 with Forms & Typography plugins |
| **Visuals & 3D** | Three.js | 0.162.0 (WebGL background shaders & interactive hud) |
| **Asset Bundler** | Vite | 4.5.x |
| **Database** | SQLite (Local/Testing) / MySQL 8.0+ (Production) | PDO SQLite / PDO MySQL |
| **PDF Generation** | DomPDF | `barryvdh/laravel-dompdf` ^2.2 |
| **Payment SDKs** | Stripe PHP | `stripe/stripe-php` ^21.3 |
| **Communication** | Twilio SDK | `twilio/sdk` ^8.12 |
| **Testing** | PHPUnit & Playwright | PHPUnit 9.5.x & Playwright 1.63.x |

---

## System Requirements

- **PHP**: `^8.0.2` (Required extensions: `pdo_sqlite`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `tokenizer`, `bcmath`, `curl`)
- **Composer**: `2.x`
- **Node.js**: `18.x` or `20.x` LTS (or `24.x`)
- **npm**: `9.x`+
- **Database**: SQLite 3 (local development) or MySQL 8.0+ (staging/production)

---

## Quick Start / Local Setup

Follow these steps to set up the project locally in your development environment:

### 1. Clone Repository
```bash
git clone https://github.com/MuhibChy/perfectitsecurity.git
cd perfectitsecurity
```

### 2. Install Backend Dependencies
```bash
composer install
```
*(Preserves locked dependency versions in `composer.lock`)*

### 3. Configure Environment Variables
Create your local environment file from the sanitized template:
```bash
# Linux / macOS:
cp .env.example .env

# Windows (PowerShell):
Copy-Item .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Setup Database & Migrations
For local SQLite development:
```bash
# Ensure local database file exists
touch database/database.sqlite

# Run all schema migrations
php artisan migrate
```

### 6. Seed Demo Data
To populate the database with demonstration data, test roles, services, and categories:
```bash
php artisan db:seed
```

> **Demo User Credentials (Local Dev Only)**:
> - **Super Admin**: `admin@techsupport.com` / `password`
> - **Customer**: `alice@example.com` / `password`

### 7. Install & Build Frontend Assets
```bash
npm install
npm run build
```

For hot-reloading during development:
```bash
npm run dev
```

### 8. Link Public Storage
Ensure public assets and uploads are linked:
```bash
php artisan storage:link
```

### 9. Start Development Server
```bash
php artisan serve
```
Open **`http://127.0.0.1:8000`** in your browser.

---

## Windows One-Click Launcher

On Windows workstations with XAMPP or native PHP/Composer installed:
```bat
run-local.bat
```
This utility script checks prerequisites, runs pending migrations, builds frontend assets, and launches the development server automatically.

---

## Background Workers & Task Scheduling

The platform relies on scheduled tasks for SLA deadline checking, recurring billing, exchange rate updates, and automated backups.

### Scheduler Setup
Add the Laravel scheduler to your system crontab:
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually for local testing:
```bash
php artisan schedule:run
```

### Queue Worker
For asynchronous email dispatch and background processing:
```bash
php artisan queue:work --tries=3
```

---

## Testing & Quality Assurance

The application includes an extensive automated test suite covering feature workflows, financial integrity, RBAC isolation, and security controls:

```bash
# Run all PHPUnit tests
php artisan test

# Run tests in parallel
php artisan test --parallel

# Run a specific test suite
php artisan test tests/Feature/AuthenticationTest.php
php artisan test tests/Feature/FinancialIntegrityTest.php
php artisan test tests/Feature/CustomerIsolationTest.php

# Run end-to-end browser tests (requires Playwright)
npm run test:browser
```

---

## Configuration & External Integrations

### Payment Gateways
- **Stripe**: Configure `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET` in `.env`.
- **bKash**: Set `BKASH_APP_KEY`, `BKASH_APP_SECRET`, `BKASH_USERNAME`, `BKASH_PASSWORD`, and `BKASH_BASE_URL`.
- **Nagad & Rocket**: Configure merchant credentials in `.env` as supplied by gateway providers.
- **Offline / Cash**: Supported out of the box without external API keys.

### AI Assistant & Agent Gateway
- Operates in **local-first mode** using Ollama (`OLLAMA_BASE_URL=http://127.0.0.1:11434`, `OLLAMA_MODEL=llama3.2:latest`).
- Optional cloud fallback to OpenAI or OpenRouter can be enabled via `AI_FALLBACK_ENABLED=true` and `AI_API_KEY=...`.
- If no AI provider is configured, the platform safely degrades to standard human ticket routing.

### Email & SMS Notifications
- **Email**: Set `MAIL_MAILER=smtp` along with `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS`. For local development, `MAIL_MAILER=log` captures emails in `storage/logs/laravel.log`.
- **SMS / OTP**: Twilio Verify integration is configured via `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, and `TWILIO_VERIFY_SERVICE_SID`. Local fallback OTP is available for dev testing.

---

## Security Reporting & Contribution

### Security Vulnerability Reporting
If you discover a security vulnerability within PerfectITSecurity, please do **NOT** open a public issue. Send a detailed report to the security team at **security@perfectitsecurity.local** or contact the repository maintainers privately.

### Credential Protection Guidelines
- **Never commit `.env`** or any file containing private keys, access tokens, or live passwords.
- Always verify `.env.example` before committing to ensure it contains only sanitized placeholder values.
- Verify that local database files (`*.sqlite`) and backup archives (`backups/`) remain ignored by Git.

---

## License

This software is proprietary and confidential. All rights reserved.
