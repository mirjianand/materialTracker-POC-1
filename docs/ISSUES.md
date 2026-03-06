# Short issue list / backlog

These are suggested next tasks to pick up when resuming development.

- UI polish: responsive testing and badge/color adjustments for admin/QA views.
- Add E2E tests: full PO → QA → Transfer → Accept flow.
- Add RBAC unit tests: enforce role checks on controller actions.
- Harden uploads: validate file types/sizes and add virus scanning step if possible.
- Add bulk action integration tests and performance checks for large inventories.
- Review logging/audit: ensure all state transitions are logged in `item_transactions` and `mat_tracker_audit_log`.
- Review and configure deployment/hosting checklist.

Feel free to turn any of these into GitHub Issues using the repo UI.
