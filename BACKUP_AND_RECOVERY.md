# PerfectITSecurity — Backup & Recovery

- Commands: `backup:run` (DB via mysqldump --single-transaction / sqlite dump
  + files, encrypted, stored, **verified before success**), `backup:verify`,
  `backup:restore` (confirmation token + pre-restore safety backup),
  `backup:prune` (never deletes sole/in-restore/held backups), `backup:monitor`.
- Scope: full-database dumps cover users, orders, invoices, payments, salaries,
  commissions, wallets, identity docs metadata, audit logs, learning records.
- File payloads (avatars, ID docs, attachments) live under `storage/app`
  (private disk) and are included in file backups; never placed in public web roots.
- RPO/RTO: set by cron frequency; verify restores with `backup:restore` into a
  staging copy and compare financial totals before trusting a backup.
- Pre-change safety: keep `.env` copy + `backup:run` output outside the repo
  before structural migrations; every migration in this project ships a `down()`.
