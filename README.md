# PostSmith

PostSmith is a Laravel application for AI-assisted social post generation, rewriting, performance tracking, billing, and admin management.

## Railway Deployment

This repo is configured for Railway's non-Docker Laravel deployment. Railway detects the Laravel app and serves it with PHP-FPM/Caddy through Railpack.

### App service

1. Create a new Railway project.
2. Add a PostgreSQL database service.
3. Add a new service from GitHub and select `postsmith75-ctrl/postsmith`.
4. In the app service variables, set the production environment variables from `.env.example`.
5. Generate a Railway domain from the app service Networking tab.
6. Set `APP_URL`, `APP_DOMAIN`, and Google OAuth redirect values to the production domain.

The committed `railway.toml` sets:

- Builder: `RAILPACK`
- Build command: `npm run build`
- Pre-deploy command: `php artisan migrate --force`
- Healthcheck path: `/`

### Required variables

At minimum, configure:

```env
APP_NAME=PostSmith
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-railway-domain.up.railway.app
APP_DOMAIN=https://your-railway-domain.up.railway.app

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}
DATABASE_URL=${{Postgres.DATABASE_URL}}
DB_SSLMODE=require

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stderr

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://your-railway-domain.up.railway.app/auth/google/callback

AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=

FLW_PUBLIC_KEY=
FLW_SECRET_KEY=
FLW_CURRENCY=USD
FLW_MONTHLY_PLAN_ID=
FLW_ANNUAL_PLAN_ID=

RESEND_API_KEY=
SENDER_EMAIL=
ADMIN_EMAIL=
ADMIN_EMAILS=
```

Generate `APP_KEY` locally with:

```bash
php artisan key:generate --show
```

### Optional services

For heavier production use, add separate Railway services from the same repo:

- Worker start command: `php artisan queue:work --sleep=3 --tries=3 --timeout=90`
- Scheduler start command: `php artisan schedule:work`

Use the same environment variables as the app service.
