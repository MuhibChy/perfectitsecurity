# Railway Cloud Deployment Guide — PerfectITSecurity

> This document explains how to deploy PerfectITSecurity as a fully live, publicly accessible website using **Railway** (free tier available).

---

## Why Railway?

Railway is the recommended hosting provider because:
- Supports PHP 8.0 natively via Nixpacks auto-detection
- Connects directly to your private GitHub repository
- Provides a free public HTTPS URL (`*.up.railway.app`) with TLS
- Auto-deploys every time you push to `main`
- Includes a free MySQL or PostgreSQL addon (optional; SQLite works for demo)
- No credit card required for the free starter plan

---

## Step 1 — Create a Railway Account

1. Open **https://railway.app** in your browser
2. Click **"Start a New Project"** → **"Sign in with GitHub"**
3. Authorize Railway to access your GitHub account
4. Railway will redirect you to the project dashboard

---

## Step 2 — Create the Project from GitHub

1. In the Railway dashboard, click **"New Project"**
2. Select **"Deploy from GitHub repo"**
3. Click **"Configure GitHub App"** → grant Railway access to **MuhibChy/perfectitsecurity** (private repo)
4. Select the repository from the list
5. Railway will auto-detect the PHP/Nixpacks configuration from `nixpacks.toml`
6. The first build will start automatically

---

## Step 3 — Set Required Environment Variables

In the Railway project dashboard, open **Variables** and set the following:

### Required (must be set before the app starts)

| Variable | Value | Notes |
|---|---|---|
| `APP_KEY` | `base64:...` | Generate: `php artisan key:generate --show` in your local terminal, then paste the result |
| `APP_URL` | `https://YOUR-APP.up.railway.app` | Railway provides this URL in the Settings tab after first deploy |
| `APP_ENV` | `production` | Already set in nixpacks.toml but can override here |
| `APP_DEBUG` | `false` | Required — never `true` in production |

### Optional (enable features after core is working)

| Variable | Value | When to set |
|---|---|---|
| `MAIL_MAILER` | `smtp` | When ready to test email delivery |
| `MAIL_HOST` | Your SMTP host | e.g. `smtp.mailgun.org` or `smtp.sendgrid.net` |
| `MAIL_PORT` | `587` | |
| `MAIL_USERNAME` | Your SMTP username | |
| `MAIL_PASSWORD` | Your SMTP password | Store as Railway secret |
| `MAIL_FROM_ADDRESS` | `hello@yourdomain.com` | |
| `STRIPE_KEY` | `pk_test_...` | Stripe test publishable key |
| `STRIPE_SECRET` | `sk_test_...` | Stripe test secret key |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` | From Stripe Dashboard → Webhooks |

---

## Step 4 — Get Your Public URL

1. In Railway, go to your project → **Settings** → **Networking**
2. Click **"Generate Domain"** (free `*.up.railway.app` URL)
3. Copy the URL (e.g. `https://perfectitsecurity-production.up.railway.app`)
4. Set `APP_URL` in Variables to this exact URL

---

## Step 5 — Trigger a Redeployment

After setting environment variables:
1. Go to **Deployments** tab in Railway
2. Click **"Redeploy"** on the latest deployment, or push a commit to `main`
3. Watch the build logs — it will run `composer install`, `npm run build`, `php artisan migrate --force`, then start the server

---

## Step 6 — Verify the Live Website

Once the deployment shows **"Active"** (green):

1. Open `https://YOUR-APP.up.railway.app` in a browser
2. Verify the homepage loads with the Three.js hero background
3. Test `/login` → log in with `admin@techsupport.com` / `password`
4. Test `/portal/tickets` without being logged in → should redirect to `/login`
5. Test `/admin/users` without being logged in → should redirect to `/login`

---

## Rollback Procedure

If a deployment fails or the site breaks:

1. Open Railway dashboard → **Deployments**
2. Find the last known-good deployment
3. Click **"..."** → **"Rollback to this deployment"**
4. Railway restores the previous build without data loss

---

## Connecting a Custom Domain (Optional)

If you own a domain (e.g. `perfectitsecurity.com`):
1. Railway: Settings → Networking → **"+ Custom Domain"** → enter your domain
2. Railway provides CNAME/A records to set at your DNS provider
3. Update `APP_URL` to `https://perfectitsecurity.com`
4. Railway auto-provisions a TLS certificate via Let's Encrypt

---

## Making the Repository Public (Required for GitHub Pages — Not Required for Railway)

Railway works with **private** GitHub repositories. You do **NOT** need to make the repository public to deploy with Railway.

If you later choose to make it public, go to:
- GitHub → Repository Settings → Danger Zone → **Change visibility → Public**

---

## Important Notes

- **SQLite is ephemeral on Railway**: On each redeploy, the database file is recreated and the demo seeder runs automatically. For persistent user data, add a Railway MySQL plugin (free tier available).
- **Payment processing**: Stripe is configured in sandbox/test mode. Do not switch to live keys until completing Stripe's production setup checklist.
- **AI assistant**: Disabled by default (`AI_AGENT_ENABLED=false`). Enable only after configuring a local Ollama endpoint or approved cloud API.
- **Emails**: Default to `MAIL_MAILER=log` (emails written to logs, not sent). Configure SMTP to enable real email delivery.
