# Material Tracker

Material Tracker is a lightweight PHP app to manage purchase orders, inventory, QA, and transfers.

Quick start (local):

1. Copy configuration and update secrets:

   ```powershell
   copy config\config.example.php config\config.php
   # then edit config\config.php and set DB and SMTP credentials
   ```

2. Install PHP dependencies with Composer:

   ```powershell
   composer install
   ```

3. Run database migrations (see `migrations/` and `config/init.sql`) and adjust `config/config.php` DB settings.

4. Start PHP built-in server for quick testing (optional):

   ```powershell
   php -S localhost:8000 -t public
   ```

Security & publishing notes:

- Do NOT commit `config/config.php` or any files containing secrets. Use `config/config.example.php` as a template.
- Rotate any credentials that were accidentally exposed before publishing.
- Add a `.env` or secret management approach for production credentials and set them via environment variables or CI secrets.

Publishing to GitHub (summary):

1. Initialize git, add and commit files locally:

   ```powershell
   git init
   git add .
   git commit -m "Initial commit: Material Tracker"
   ```

2. Create a public GitHub repository (web UI or `gh` CLI):

   ```powershell
   # using GitHub CLI (install and authenticate first)
   gh repo create my-org/mattracker --public --source=. --remote=origin --push
   ```

3. If using the web UI, create a repo, then set remote and push:

   ```powershell
   git remote add origin https://github.com/<your>/<repo>.git
   git branch -M main
   git push -u origin main
   ```

Checklist before making the repo public:

- Remove/rotate secrets (DB, SMTP, API keys).
- Ensure `.gitignore` excludes `vendor/`, `uploads/`, and `config/config.php`.
- Consider adding `LICENSE` (e.g. MIT) and `SECURITY.md`.
- Add CI (GitHub Actions) to run PHPUnit tests and static checks.
- Review commit history for accidentally committed secrets; use `git filter-repo` or `BFG` to purge them if needed.

If you want, I can:

- Create `.gitignore`, `config/config.example.php`, `README.md`, and `LICENSE` (I already added these).
- Initialize a local git repo and make the initial commit for you.
- Provide the exact commands to create the GitHub repo and push (web or `gh` CLI).

Tell me which of the above you'd like me to run next (init & commit, push with `gh`, or just provide commands).
