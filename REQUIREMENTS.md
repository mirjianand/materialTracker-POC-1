# Material Tracker - Combined Requirements

## Purpose
Provide a concise, implementable specification for the Material Tracker application based on supplied materials and screenshots. This document guides development using PHP 8.x (no Laravel), Bootstrap 5, and jQuery-ajax. XAMPP (MySQL/MariaDB) will be the DB engine.

## Scope
- Web application for tracking materials, approvals, and history.
- Role-based access to pages and operations.
- LDAP-based authentication integration (OpenLDAP).
- Session management, responsive UI, file upload limits, and historical data import.

## Users & Roles
- System Administrator: full access, manage users, roles, settings, data import.
- Approver / Manager: review and approve material records, view audit logs.
- Data Entry / User: create and edit material records (subject to RBAC), upload files.
- Auditor / ReadOnly: view-only access to records and history.

Role mapping and granular permissions will be configurable by administrators.

## Authentication & Authorization
- Use PHP 8.x with the PHP LDAP extension to authenticate users against an LDAP server (OpenLDAP, Active Directory optional).
- Fallback: local DB user store for emergency/local accounts (optional, admin-only).
- Authorization: role-based access control (RBAC) with permissions assigned to roles.
- No digital signatures planned.

## Sessions & Session Management
- Use server-side PHP sessions with secure settings.
- Session lifetime: configurable (default 30 minutes inactivity).
- Support session invalidation on logout and admin-forced logout.
- Protect sessions with secure cookies, HttpOnly, and SameSite settings.

## UI & Frontend
- Responsive UI built with Bootstrap 5.
- Use jQuery + jQuery-ajax for frontend data fetching and form submissions.
- Progressive enhancement: pages work without JS for basic viewing, but actions (search, filters, inline operations) use AJAX.
- Screenshots from previous app used as UX references only; design may differ.

## File Uploads
- Allowed file types: JPEG / JPG images and PDF only.
- Max size per file: 2 MB.
- Server-side validation (MIME type and extension) and client-side pre-checks.
- Store files in a dedicated uploads directory outside webroot if possible, or protect access via script that checks permissions before serving.
- Filenames: normalize and store unique names; store original name, uploader, timestamp in DB.

## Database & Historical Data Migration
- DB engine: MySQL / MariaDB (XAMPP default) compatible with PHP 8.x PDO.
- Provide a migration/import tool for historical data:
  - Accept CSV and SQL dumps.
  - Provide mapping UI or config for column->field mapping.
  - Validate data and show import summary with errors/warnings.
  - Log import operations and allow rollback where feasible.

## APIs & Endpoints
- RESTful-ish endpoints (JSON) for AJAX operations: list, view, create, update, delete, approve, upload.
- Endpoints require CSRF protection for state-changing requests.

## Audit Logging
- Record create/update/delete/approve events with user, timestamp, IP, and change metadata.
- Provide an audit view accessible to admins and auditors.

## Security & Compliance
- Use prepared statements / parameterized queries (PDO) to prevent SQL injection.
- CSRF tokens for forms and AJAX POSTs.
- Input validation server-side and client-side.
- Enforce file type and size restrictions; scan for common threats if possible.

## Non-functional Requirements
- PHP 8.x compatible; avoid frameworks (no Laravel) per requirement.
- Compatible with XAMPP on Windows (development) and Linux (production) environments.
- Reasonable performance for datasets up to tens of thousands of records; provide pagination and indexed searches.

## Deliverables & Milestones (high-level)
1. Combined requirements and DB schema (this document + ERD).
2. Project scaffold (PHP routing, Bootstrap, assets, basic auth/LDAP integration).
3. Core CRUD pages with RBAC and session management.
4. File upload feature and storage.
5. Historical data import tool.
6. QA, security review, documentation and deployment instructions for XAMPP.

## Acceptance Criteria
- All pages render responsively with Bootstrap 5.
- Users authenticate via LDAP (or local fallback for admins).
- RBAC enforces access to pages and operations.
- File uploads limited to jpeg/jpg and pdf under 2 MB, validated server-side.
- Historical data can be imported and verified.

## Implementation Notes / Recommendations
- Use PDO for DB access and a small router or front controller pattern for PHP routing.
- Use the PHP LDAP extension (ldap_connect, ldap_bind) to validate credentials and map LDAP groups to roles.
- Store sessions using PHP default handler; consider database-backed sessions if you need distributed sessions.
- Use AJAX endpoints that return JSON `{ success: true|false, data: ..., errors: [...] }`.
- Provide a `migrations/` folder with SQL to create the initial schema and constraints.

## Next Steps
- Produce an ER diagram and initial SQL schema.
- Scaffold the project structure and create initial auth+LDAP connector.

---
Generated: 2026-03-01
