# Resume Checklist — Material Tracker POC

Last edited: 2026-03-06

Quick summary
- Repo: https://github.com/mirjianand/materialTracker-POC-1
- Local workspace: `C:\xampp1\htdocs\mattracker1`
- Chat transcript and development notes: `docs/copilot-chat-transcript-20260303.md`

What has been completed
- Core PO → Inventory → QA → Transfer workflows implemented (server + views).
- QA acceptance and transaction logging implemented.
- Admin/Ops transfer flows, rework and surrender implemented.
- Views: compact admin inventory table, QA list with bordered rows, item detail forms.
- Smoke scripts and PHPUnit tests added and executed locally.
- Project initialized as git repo and pushed to GitHub (`main` branch).
- CI workflow added: `.github/workflows/ci.yml` (runs composer and PHPUnit).

Next tasks to pick up when resuming
1. UI polish: responsive testing on mobile breakpoints and color/badge tweaks.
2. Add end-to-end acceptance tests for full transfer + accept flows.
3. Add stricter RBAC unit tests and server-side checks for all action paths.
4. Review and harden file upload handling and CSRF/session coverage.
5. Improve test coverage for bulk actions and long-running job scripts.
6. (Optional) Integrate a transactional SMTP/test mailbox for e2e email tests.

Quick commands to resume work
```powershell
cd C:\xampp1\htdocs\mattracker1
git pull origin main
composer install
php scripts/smoke_admin_ui.php
vendor\bin\phpunit --configuration phpunit.xml
```

Where to look first
- Controllers: `src/controllers/` (POController, QAController, AdminController, OpsController)
- Views: `src/views/admin/`, `src/views/qa/`, `src/views/items/`
- Tests: `tests/Integration/` and `scripts/` for smoke scripts

Notes / Safety
- `config/config.php` contains secrets locally — do NOT commit. Use `config/config.example.php` as template.
- If you ever find credentials in prior commits, run a history scrub (BFG or `git filter-repo`) and rotate secrets.

If you'd like, I can open the next task (UI polish) and prepare a small checklist and PR draft.
