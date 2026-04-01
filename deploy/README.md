# Deploy Automation

Automation flow for Raspberry Web Infra:

- GitHub push event -> `deploy/webhook.php`
- Webhook validates `X-Hub-Signature-256`
- Valid branch triggers `deploy/deploy.sh <branch>`
- Script creates backup, pulls latest code, and rolls back on failure

## 1) Prepare configuration

```bash
cd /var/www/html/raspberry_webinfra
cp deploy/.env.example deploy/.env
nano deploy/.env
```

Set a long random secret in `DEPLOY_WEBHOOK_SECRET`.

## 2) Permissions

```bash
cd /var/www/html/raspberry_webinfra
chmod 700 deploy/deploy.sh
chmod 600 deploy/.env
mkdir -p deploy/logs deploy/backups
```

If Apache user cannot execute Git commands, run:

```bash
sudo chown -R www-data:www-data /var/www/html/raspberry_webinfra
```

Use your preferred ownership model if different.

## 3) Apache route

Webhook URL example:

- `https://YOUR_DOMAIN/raspberry_webinfra/deploy/webhook.php`

## 4) GitHub webhook setup

In repository settings:

1. Go to `Settings -> Webhooks -> Add webhook`
2. Payload URL: `https://YOUR_DOMAIN/raspberry_webinfra/deploy/webhook.php`
3. Content type: `application/json`
4. Secret: same value as `DEPLOY_WEBHOOK_SECRET`
5. Events: `Just the push event`
6. Active: enabled

## 5) Test manually

```bash
cd /var/www/html/raspberry_webinfra
./deploy/deploy.sh production
```

Logs:

- `deploy/logs/webhook.log`
- `deploy/logs/deploy.log`

Backups:

- `deploy/backups/`

## Branch strategy

- `production`: stable code deployed to users.
- `develop`: ongoing work and integration.
- Feature branches from `develop`.
- Merge `develop -> production` when validated.
