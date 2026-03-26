# Symmetric Encryption In UniProjectManager

## Scope implemented

The app now encrypts sensitive academic feedback at rest using Laravel encrypted casts (symmetric cryptography):

- `classroom_grades.feedback`
- `deliverable_submissions.notes`
- `deliverable_submissions.grade_feedback`

Implementation points:

- Model casts:
  - `app/Models/ClassroomGrade.php`
  - `app/Models/DeliverableSubmission.php`
- Legacy data migration:
  - `database/migrations/2026_03_26_000004_encrypt_sensitive_feedback_columns.php`

## Why these fields

These fields can contain private information (teacher observations, remediation hints, subjective evaluation notes). If a DB dump leaks, plaintext feedback is directly exposed. Encrypting these columns reduces data disclosure risk.

## Cryptography model

- Type: symmetric encryption
- Engine: Laravel `Crypt` / encrypted cast
- Cipher from app config: `AES-256-CBC` (`config/app.php`)
- Key source: `APP_KEY` from environment

Read/write behavior:

1. Application writes plaintext to model attribute.
2. Eloquent encrypted cast encrypts before DB insert/update.
3. DB stores ciphertext (not human-readable plaintext).
4. On model read, value is decrypted automatically for authorized app flow.

## Legacy data hardening

Migration `2026_03_26_000004_encrypt_sensitive_feedback_columns.php` iterates existing rows and encrypts plaintext values already stored in DB.

`down()` is intentionally no-op to avoid re-writing sensitive plaintext.

## Operational notes for production

- Keep `APP_KEY` secret and never commit it.
- Rotate keys with a controlled migration plan (Laravel supports `APP_PREVIOUS_KEYS` for staged rotation).
- Backup encryption key separately from DB backups; DB backup without keys should not reveal feedback plaintext.
- If `APP_KEY` is lost, encrypted values become unrecoverable.

## Verification evidence

Feature tests validate encryption at rest:

- `tests/Feature/CatalogAndReminderTest.php` (`test_catalog_feedback_is_encrypted_at_rest`)
- `tests/Feature/DeliverableSubmissionTest.php` (`test_student_can_upload_deliverable_submission_file`, notes assertion)
