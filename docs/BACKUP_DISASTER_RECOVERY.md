# Backup & Disaster Recovery Runbook

> No real secrets in this document. Credentials live only in server env / secret store.

## 1. What is protected

- **Database (all tables):** users + verification, roles, services/pricing, orders,
  quotations + negotiation history, final prices, invoices, payments + references,
  receipts, refunds, financial transactions, revenue/expenditure, tickets + messages,
  tasks + assignments + time tracking, expenses, commissions, notifications, KB/AI,
  audit logs, settings.
- **Files (`storage/app` allowlist):** `ticket-attachments/`, `expense-receipts/`,
  `public/blog/`. Directory structure + private visibility preserved on restore.
- **Config (code, not secrets):** `config/backup.php`, migrations, scheduler, storage
  config. Secrets are never written into backups.

## 2. Schedules & retention (defaults, env-configurable)

| Job | Default | Command |
|---|---|---|
| Full backup | Daily 02:00 | `backup:run --type=full` |
| DB backup | Every 6h | `backup:run --type=db` |
| Retention prune | Daily 03:30 | `backup:prune` |
| Health monitor | Hourly | `backup:monitor` (alerts on overdue/failed/unverified) |

Retention: keep 7 daily / 4 weekly / 12 monthly verified backups; pre-deploy held
until reviewed; sole backup, in-restore, and `retention_hold` backups are never deleted.

## 3. Encryption & key management

- AES-256-CBC envelope encryption, random IV per artifact, key from
  `BACKUP_ENCRYPTION_KEY` (base64, 32 bytes). Generate:
  `php -r "echo base64_encode(random_bytes(32));"`
- Only a SHA-256 **fingerprint** of the key is stored with backups — never the key.
- **Key loss = unrecoverable backups.** Store the key in the server secret store
  AND an offline escrow (sealed envelope with the DR contact). Rotate by
  generating a new key; old archives remain readable only with the old key —
  keep retired keys labelled in escrow until their archives expire.

## 4. Storage

- Primary: private `storage/app/backups` (`local` disk, no public symlink).
- External: S3-compatible mirror when `BACKUP_S3_ENABLED=true` + bucket/region set.
  A backup is marked `failed` if the S3 upload fails — local success alone is not enough.
- Connection test: `POST /admin/backups/connection-test` or `backup:monitor`.

## 5. Recovery scenarios

### 5.1 Accidental deletion / corrupt data
1. Open Backup Center (`/admin/backups`), pick the newest **verified** backup
   predating the incident. 2. Run **Restore test (isolated)** first.
3. Restore with scope `db` (or `full`), typing the backup ID to confirm.
   A pre-restore safety backup is taken automatically.
4. Validate: login, portal dashboard, spot-check order→invoice→payment→receipt→ticket→task links.

### 5.2 Database corruption / server failure
1. Provision replacement host (PHP/MySQL/Redis per `PRODUCTION_DEPLOYMENT.md`).
2. Deploy code, configure `.env` (including `BACKUP_ENCRYPTION_KEY` from escrow).
3. Copy the newest verified encrypted artifacts from S3/offsite to
   `storage/app/backups/<id>/`, or use `backup:restore` if the disk survived.
4. `php artisan migrate --force` to baseline schema, then restore, then
   `php artisan config:cache && route:cache`.
5. Run health checks: `/healthz`, `/admin/health`, queue, scheduler, login, payments read-only.

### 5.3 Failed deployment
1. `backup:run --type=pre_deploy` runs before every production deploy (CI gate).
2. On failure: restore the pre-deploy backup (scope `full`), redeploy previous release tag.
3. Record incident in audit log + post-mortem.

### 5.4 Ransomware / storage loss
1. Isolate host. Rebuild clean OS + stack from known-good images.
2. Restore code from Git tag, secrets from secret store (never from backups).
3. Restore newest verified offsite backup; verify checksums before import.
4. Rotate ALL credentials (DB, mail, S3, API keys) — assume exfiltration.

## 6. RTO / RPO targets (to confirm with business)

- RPO ≤ 6h (DB cadence) for transactional data; ≤ 24h for files.
- RTO ≤ 4h for full rebuild given tested runbook + escrowed keys.
- Measure actuals during each staging drill and record below.

## 7. Drill log

| Date | Backup used | Scope | Duration | Result | Issues / fixes |
|---|---|---|---|---|---|
| 2026-09-03 | local sqlite full (dev) | full verify + isolated restore test | — | passed | initial implementation drill |
| | | | | | |
