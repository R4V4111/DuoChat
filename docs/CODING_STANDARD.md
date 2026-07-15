# DuoChat Coding Standard

## General

- Follow PSR-12.
- Use strict typing (`declare(strict_types=1);`) where appropriate.
- Prefer constructor dependency injection.
- Keep methods small and focused.
- Use meaningful variable names.
- Avoid abbreviations.

---

## Architecture

- Controllers must remain thin.
- Business logic belongs in Services.
- Models should only contain relationships, scopes, casts, and simple helpers.
- Avoid duplicated logic.
- Update documentation when architecture changes.

---

## Database

- Use foreign keys.
- Use cascade delete only when appropriate.
- Index frequently queried columns.
- Prefer normalized data.

---

## Testing

- Every feature must include tests.
- Existing tests must continue to pass.

---

## Git

Commit format:

feat:
fix:
docs:
refactor:
test:
style:
chore:

---

## AI Rules

Before writing code:

1. Read the entire docs folder.
2. Follow PRD.
3. Follow DATABASE design.
4. Follow ARCHITECTURE.
5. Follow CODING_STANDARD.
6. Explain every design decision after implementation.

Never:

- Put business logic inside controllers.
- Ignore existing documentation.
- Create duplicated code.
- Modify unrelated files.
