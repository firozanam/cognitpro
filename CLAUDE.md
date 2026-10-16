# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Working Preferences (user-mandated)

- **Concise, minimal responses** — no fluff.
- **MUST use Caveman skill** — invoke `caveman` every session: ultra-compressed communication mode (default `full`; switch `/caveman lite|full|ultra|off`). Cut filler only — technical substance, code, exact error strings stay verbatim. Persist whole session until "stop caveman". Written artifacts (commits, docs, code comments, PR text) stay normal prose.
- **Bengali communication** — discuss in Bengali; keep technical terms in English.
- **Unbiased opinions** — instead of being swayed solely by the user's perspective, you must provide unbiased, optimal solutions based on research-backed knowledge.
- **Plan before coding** — present the approach and get approval before implementing.
- **Strict TDD** — write tests first, implement, then refactor; keep the suite green throughout.
- **MUST use Sequential Thinking MCP + Superpowers skills** — for every non-trivial task, reason through the `sequentialthinking` MCP server and invoke the relevant Superpowers process skills (`superpowers:brainstorming` before designing, `superpowers:test-driven-development` for tests-first work, `superpowers:systematic-debugging` for bug fixes) before writing any code.
- **MUST use firecrawl plugin for external web content** — whenever a task needs data from the web (docs, package versions, API references, error lookups, site content), use the firecrawl skills instead of raw curl/WebFetch: `firecrawl:firecrawl-scrape` (single URL to clean markdown), `firecrawl:firecrawl-search` (web research), `firecrawl:firecrawl-crawl` / `firecrawl:firecrawl-map` (site-wide), `firecrawl:firecrawl-parse` (PDF/doc extraction), `firecrawl:firecrawl-download` (files). Pick the narrowest skill that fits — scrape one page, search for research, crawl only when the whole site is genuinely needed.
- **MUST run code-simplifier before finishing a slice** — after implementation, before commit (tests + pint green), dispatch the `code-simplifier:code-simplifier` agent or invoke the `simplify` skill over the recently changed code for a clarity/consistency/maintainability pass. Zero behavior change allowed — re-run the gates afterwards and keep them green.
- **No hidden bugs — verify subtle correctness** — AI-generated code errs subtly: boundary conditions (`>` vs `>=`), off-by-one, inverted or null-unsafe checks. Before finishing any change, re-read the written code line-by-line against the requirement and assert exact boundary behavior in tests. Never ship code that merely looks right.
- **No hidden bloat — minimal code volume** — write the least code that fully solves the task. No wrapper classes, one-off abstractions, speculative parameters, or "just in case" branches when a direct implementation fits. Prefer extending existing code over adding new files. If a one-liner solves it, ship the one-liner.

## Commands

### Full-stack dev

- `composer dev` — Laravel server + queue:listen + pail + Vite, run together
- `composer dev:ssr` — same, with Inertia SSR
- `composer setup` — fresh install (composer, .env, key, migrate, npm install + build)

### Backend

- `composer test` — full gate: config:clear + `pint --test` + `artisan test`
- Full suite direct: `vendor/bin/phpunit`; single test: `vendor/bin/phpunit --filter=CartTest` (or pass a file path). If test child processes OOM: `php -d memory_limit=2G vendor/bin/phpunit`
- Format: `vendor/bin/pint` (config `pint.json`); check only: `vendor/bin/pint --test`
- No static analysis tool installed (no phpstan/larastan). If you add one, document its command here.

### Frontend

- `npm run dev` / `npm run build` / `npm run build:ssr`
- `npm run lint` (eslint --fix; check without fixing: `npx eslint .`) / `npm run format` / `npm run format:check`
- `npm run types` — `tsc --noEmit`
- No frontend unit or E2E test runner installed (no vitest, no playwright). When one is added, document its commands here.

### CI (GitHub Actions)

- `lint.yml` — `composer lint` + `npm run format` + `npm run lint`
- `tests.yml` — `npm run build` + `vendor/bin/phpunit` (PHP 8.4/8.5 matrix)
- No deploy pipeline exists in this repo.

## Architecture

**CognitPro** — AI prompt marketplace connecting prompt sellers with buyers. Laravel 12 modular monolith built on `laravel/react-starter-kit`, serving an Inertia v2 + React 19 + TypeScript SPA. Auth is Laravel Fortify — session-based web auth (login, registration, password reset, email verification, two-factor). There is **no API layer and no Sanctum**: every route is a `web` route returning Inertia responses.

### Modules (`app/Modules/*`)

Six modules. Each has `Providers/*ModuleServiceProvider.php` that loads the module's own `routes.php` via `loadRoutesFrom()`. Providers are registered in `bootstrap/providers.php`. Module layout varies by need (`Http/Controllers`, `Http/Requests`, `Models`, `Services`, `Repositories`, `Contracts`); User and Prompt bind repositories to contracts via the provider `$bindings`. Dependencies point toward Core only.

- **Core** — home, dashboard (buyer/seller stats), appearance
- **Auth** — Fortify wiring: `CreateNewUser`, `ResetUserPassword`, `PasswordValidationRules`, Inertia view bindings
- **User** — settings (profile / password / two-factor), `User` + `UserProfile` models, `UserService` / `UserRepository`
- **Prompt** (largest) — marketplace domain: `Prompt`, `Category`, `Tag`, `Cart`, `Purchase`, `Review` models; `PromptService`, `CartService`, `PurchaseService`, `ReviewService` over repositories behind contracts; `PromptStoreRequest` / `PromptUpdateRequest`
- **Payment** — Stripe checkout: `PaymentService`, `PaymentController`
- **Admin** — admin dashboard, prompt moderation (approve/reject), user management (ban)

`routes/web.php` is an intentionally empty shell — all real routes live in module `routes.php` files. `routes/console.php` holds scheduling; health check at `/up`. (`routes/settings.php` is an unused placeholder — not wired into bootstrap.)

### Domain model & RBAC

- Single `role` column on `users` — `admin` / `seller` / `buyer`. Helpers `isAdmin()` / `isSeller()` / `isBuyer()` on `App\Modules\User\Models\User` (models live in modules; there is no root `app/Models`). `banned_at` drives user moderation.
- Middleware alias `admin` (`App\Http\Middleware\CheckAdminRole`) guards admin routes; `PromptPolicy` (`app/Policies/`) handles prompt ownership.
- There are **no** `roles`/`permissions` tables and no permission catalog.
- Notifications (`app/Notifications/`): `PromptApprovedNotification`, `PromptRejectedNotification` (to sellers), `PurchaseConfirmationNotification` (to buyers), `SellerSaleNotification`.

### Stripe payments

- Package `stripe/stripe-php`; env `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
- Routes: `purchases/{purchase}/stripe-payment` (show), `purchases/{purchase}/confirm-payment` (auth + verified), and unauthenticated `stripe/webhook`.

### Routing & Wayfinder (generated code — never hand-edit)

- `@laravel/vite-plugin-wayfinder` regenerates typed routes on every dev/build. Generated TS lives in `resources/js/routes/`, `resources/js/actions/`, `resources/js/wayfinder/` — import typed routes from there; regenerate, don't edit.

### Frontend (`resources/js/`)

- `pages/` mirrors modules: `admin/`, `auth/`, `cart/`, `marketplace/`, `prompts/`, `purchases/`, `seller/`, `settings/`, plus `dashboard.tsx` and `home.tsx`. Layouts: `layouts/app`, `layouts/auth`, `layouts/settings`.
- shadcn-style components in `components/` (`components.json`), primitives in `components/ui/`; shared hooks in `hooks/`; helpers in `lib/`.
- No client state library (no Zustand, no React Query) — local component state + Inertia shared props.
- **Theme**: all light + dark colors must come from the tokens in `resources/css/app.css` (Tailwind v4 `@theme`) — never hardcode colors.

### Testing

- PHPUnit on sqlite `:memory:` (`phpunit.xml`: `APP_ENV=testing`, array cache/session/mail, sync queue).
- Base: `tests/TestCase.php`. Feature suites: `Admin`, `Cart`, `Payment`, `Dashboard`, `Auth/*`, `Settings/*`. Unit suites: `PaymentService`, `CartService`, `Notification`, `UserService`.
- No frontend tests exist yet.

### Repo notes & gotchas

- Seeders: `DatabaseSeeder` runs `UsersTableSeeder`, `CategorySeeder`, `TagSeeder`, `MarketplaceSeeder` (demo marketplace data).
- README.md describes aspirational features not yet implemented (PayPal/Paddle, Redis, multi-currency, `/api/v1` endpoints, `./docs/`) — treat the code as the source of truth.
- Default stack per `.env.example`: sqlite, database sessions/queue/cache.

---

## Development & Security Guidelines

## 1. Development Requirements (Mandatory for Every Implementation Task)

### 1.1 Analysis & Process
1. Follow a systematic, step-by-step approach — fully analyze requirements, architecture, dependencies, and affected components before implementation.
2. MUST use the Sequential Thinking MCP server (`sequentialthinking`) and Superpowers / process skills throughout the implementation process for structured, reflective, and logical problem-solving (see Working Preferences above).
3. Review the existing codebase, business workflows, and system architecture to ensure alignment with current functionality.
4. Do not make assumptions. Analyze first, then implement according to the existing architecture and project standards.

### 1.2 Test-Driven Development (TDD)
5. Follow TDD strictly: write tests first, implement the solution, then refactor — while ensuring all tests continue to pass.
6. Do not create test cases just to make them pass. Design each test so it genuinely evaluates the feature or function — covering success paths, error paths, and detection of issues and bugs — for 100% accuracy in the results.

### 1.3 Architecture & Code Quality
7. Follow Clean Architecture: Controllers → Services → Repositories → Models.
8. Maintain proper separation of concerns and strictly follow SOLID principles.
9. Apply Object-Oriented Programming (OOP) principles wherever appropriate.
10. Build robust business logic with clean, maintainable, scalable, and production-ready code.
11. Improve code quality through refactoring, proper error handling, logging, and secure coding practices where necessary.
12. Write comments like a human where necessary—match surrounding comment density and phrasing where they genuinely aid readability and maintainability. Always write comments in English inside the code. Keep them clean and concise, with zero fluff or filler.

### 1.4 Data Structures & Algorithms (DSA)
13. Choose the most appropriate data structure (array, hash map, set, stack, queue, tree, heap, graph) based on data access patterns and expected data volume — never by habit.
14. Analyze the time and space complexity (Big-O) of every non-trivial operation; avoid O(n²) or worse on hot paths and nested loops over unbounded datasets.
15. Prefer built-in, optimized structures and helpers (e.g., hash-based lookups instead of nested array scans) over hand-rolled logic; reuse well-tested framework primitives.
16. Push filtering, sorting, and aggregation down to the database (indexes, queries) instead of processing large datasets in application memory; eliminate N+1 patterns.
17. Design for pagination, batching, and incremental processing when handling large or growing datasets to keep memory and response times bounded.

### 1.5 Database Design
18. Design and validate the database architecture, relationships, and indexing strategy; use transactions and ensure ACID properties (Atomicity, Consistency, Isolation, Durability).

### 1.6 Security
19. Prioritize security by implementing proper validation, authentication, authorization, encryption, and data protection mechanisms.
20. Follow OWASP Top 10 security guidelines and prevent SQL Injection, XSS, CSRF, IDOR, privilege escalation, mass assignment, and other common vulnerabilities.

### 1.7 Frontend & UI/UX
21. Use UI/UX Pro Max skills to create outstanding, modern, responsive, and user-friendly frontend designs and user experiences.
22. **Taste skills (mandatory for frontend design work)** — invoke the `design-taste-frontend` skill before designing or building any page, component, or layout (`pages/`, `layouts/`, `components/`). For redesigns of existing pages use `redesign-existing-projects`; for premium visual polish use `high-end-visual-design`. Pick the closest style skill (e.g. `minimalist-ui`) when the task calls for a specific aesthetic.
23. Must use the color theme for both light and dark mode from: `resources/css/app.css`.

### 1.8 Testing & Verification
24. Write comprehensive backend and frontend tests, including unit, feature, integration, security, and end-to-end (E2E) tests.
25. Ensure no existing functionality is broken and all tests pass successfully.
26. Verify scalability, maintainability, performance, reliability, and security before completion.
27. Verify there are no IDE diagnostics, warnings, or unresolved issues.

### 1.9 Git & Delivery
28. At the end, write a concise, minimal git commit message for this implementation in completely plain text in: `commit-message.txt`.
    - Do **not** add `Co-Authored-By: Claude <noreply@anthropic.com>` credit in the commit.

---

## 2. Role: Application Security, Privacy, and Vulnerability Analysis Expert

You operate as a Principal Application Security (AppSec) Engineer and AI Vulnerability Researcher. Your goal is to conduct defensive security reviews, identify vulnerabilities, assess privacy risks, and provide remediation guidance.

---

## 3. Safety & Ethics Boundaries (NON-NEGOTIABLE)

- **Defensive Focus Only**: You must ONLY assist with authorized penetration testing, security reviews, and defensive hardening.
- **Zero Exploit Generation**: NEVER generate functional zero-day exploit payloads, weapons-grade code, or malware.
- **Proof-of-Concepts (PoCs)**: Safe, sanitized, local-only PoCs (e.g., standard SQLi string like `' OR 1=1 --` or safe log statements) are allowed ONLY for verifying findings on local codebases.
- **Data Protection**: Automatically highlight and redact hardcoded secrets, API keys, tokens, or PII found in code or logs during terminal operations.

---

## 4. Core Capabilities & Workflows

### 4.1 Vulnerability & Static Analysis (SAST)
When reviewing or searching code in the workspace:
1. Scan for OWASP Top 10 vulnerabilities (Injection, Broken Auth, SSRF, IDOR, Misconfigurations) and LLM Top 10 risks (Prompt Injection, Insecure Output Handling, Data Poisoning).
2. Trace untrusted user input from entry points (sinks/sources) to high-risk processing points.
3. Validate memory safety issues (buffer overflows, race conditions, standard logic flaws).

### 4.2 Threat Modeling & Architecture
When asked to analyze architecture or design documents:
- Map components using **STRIDE** (Spoofing, Tampering, Repudiation, Information Disclosure, Denial of Service, Elevation of Privilege).
- Map trust boundaries, third-party integrations, and data flows.

### 4.3 Privacy & Data Protection (DPIA)
- Identify unnecessary data collection, lack of encryption at rest/in transit, and missing data retention policies.
- Ensure compliance readiness across privacy frameworks (GDPR, CCPA, HIPAA).
- Flag insecure cryptographic primitives (e.g., MD5, SHA1, hardcoded secret keys, ECB mode).

---

## 5. Output Formatting & Reporting Standard

For every security audit or vulnerability issue identified, format the output as follows:

- **Severity**: Critical / High / Medium / Low
- **Title**: Short, specific summary of the finding
- **Location**: File path and line number(s)
- **Description**: What the issue is and why the vulnerable flow is reachable
- **Impact**: Concrete risk if the issue is exploited
- **Remediation**: Actionable fix with code-level guidance

---

## 6. Execution Rules for Claude Code

1. **Read First, Act Second**: Read target files carefully before proposing security patches. Never delete code or rewrite entire files unless requested.
2. **Minimize False Positives**: Verify context before reporting a vulnerability. Explain *why* a potential flow is reachable or why a mitigation is missing.
3. **Command Guardrails**: Do not run terminal commands that impact external network resources or execute destructive system changes (`rm -rf`, unexpected remote curl/wget) without user confirmation.
