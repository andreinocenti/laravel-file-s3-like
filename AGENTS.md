# Laravel File S3 Like - AI Agents Guide

This document defines the context, patterns, and **golden rules** for any AI agent (Cursor / Windsurf / Copilot / others) working on the **laravel-file-s3-like** package.

---

## 1. Project Overview

**laravel-file-s3-like** is a Laravel package designed to simplify interactions with S3-compatible cloud storage services. Its primary goal is to abstract complex file operations (upload, overwrite, delete, CDN purge) into a fluent and easy-to-use API.

Key capabilities include:
- **Unified API:** Simple methods for file management (`upload`, `save`, `delete`).
- **S3-Compatible Support:** Primarily built for DigitalOcean Spaces, but extensible for other S3-compatible services.
- **CDN Integration:** Native support for purging CDN cache (specifically for DigitalOcean Spaces).
- **Flexible Inputs:** Handles `UploadedFile` objects, Base64 strings, and raw file URLs.
- **Presigned URLs:** Generates temporary upload URLs for secure client-side uploads.
- **DTO Responses:** Returns consistent `DiskFile` objects with file metadata.

This package serves as a wrapper around Laravel's native `Storage` facade, adding specific enhancements for S3-like providers.

---

## 2. Tech Stack

**Core**
- PHP **8.2+**
- Laravel **8.0+** (Supports 8, 9, 10, 11, 12)
- `league/flysystem-aws-s3-v3` – Underlying S3 driver
- `xantios/mimey` – MIME type detection

**Testing & Quality**
- **Pest PHP** – Primary testing framework
- PHPUnit – Underlying test runner
- Orchestra Testbench – For mocking Laravel environment in tests

---

## 3. Architecture & Organization

### 3.1. Folder Structure

- `src/`
  - `FileS3Like.php` – The main entry point/proxy service. Handles delegation to specific repositories.
  - `LaravelFileS3LikeServiceProvider.php` – Package service provider (binding and boot).
  - `Contracts/` – Interfaces defining the contract for file repositories (`FileS3LikeInterface`).
  - `DataTransferObjects/` – Data structures for responses (`DiskFile`).
  - `Facades/` – Laravel Facades for static access (`FileS3Like`, `FileS3LikeSpaces`).
  - `Repositories/` – Concrete implementations of storage providers (e.g., `FileS3LikeSpaces`).
  - `Services/` – Internal helper services (`File`, `MimeType`).
- `tests/`
  - `Feature/` – Integration tests using Orchestra Testbench.
  - `Unit/` – Isolated logic tests.
  - `files/` – Dummy files used for testing uploads.

### 3.2. Design Patterns

- **Facade & Proxy:** The `FileS3Like` class acts as a proxy that delegates calls to a specific `Repository` (like `FileS3LikeSpaces`) based on configuration.
- **Repository Pattern:** Logic for specific providers (DigitalOcean Spaces, etc.) is encapsulated in `src/Repositories/`.
- **DTO (Data Transfer Object):** File operations return a `DiskFile` object instead of raw arrays, ensuring type safety and consistent accessors (`getUrl()`, `getFilepath()`).
- **Fluent Interface:** Methods like `disk()`, `directory()`, and `repository()` are chainable.

---

## 4. Coding Style

### 4.1. PHP / Laravel

- Follow **PSR-12**:
  - 4 spaces indentation, standard brace style.
- **Type Safety:**
  - Method arguments and return types **MUST** be typed.
  - Use `void` if no return value.
  - Example: `public function upload(UploadedFile|string $file, ?string $filename = null): DiskFile`
- **Naming:**
  - Classes: PascalCase (`FileS3Like`, `DiskFile`).
  - Methods: camelCase (`upload`, `presignedUrl`).
  - Variables: camelCase (`$repository`, `$cdnEndpoint`).
- **Facades:**
  - When using the package in tests or examples, prefer the Facade syntax for readability.

### 4.2. Testing

- **Pest PHP** is the standard (`tests/Pest.php`).
- **Structure:**
  - Feature tests should verify the integration with a mocked S3 disk.
  - Unit tests should verify internal logic (MIME detection, filename generation).
- **Mocking:**
  - Use `Storage::fake('disk-name')` to prevent actual network calls during testing.
  - Mock `UploadedFile` for upload tests.

### 4.3. Commits (recommended)

Recommended commit style (**Conventional Commits**):

- `feat(spaces): add support for custom cdn endpoint`
- `fix(upload): resolve mime type detection for avif`
- `refactor(core): optimize disk configuration loading`
- `test(presigned): add test for public acl`
- `docs(readme): update installation guide`

### 4.4. Quality Assurance

- **Early Returns:** Avoid deep nesting in logic.
- **Configuration Safety:** Always validate that the repository and disk are configured before attempting operations (see `isAllSetup` method).
- **Exceptions:** Throw meaningful exceptions when configuration is missing (e.g., missing 'endpoint' in disk config).

---

## 5. Safety Rules (Critical)

- **Secrets:** Do not generate or commit secrets, API keys (AWS keys, DigitalOcean keys), or passwords. Use environment variables in `phpunit.xml` for local testing only.
- **Live Data:** When running tests locally, ensure you are using `Storage::fake()` or a dedicated **test bucket**. Never run destructive tests (`deleteDirectory`) on a production bucket.
- **Ignored Files:** Never edit files listed in `.gitignore`.
- **Dependencies:** Do not add new composer dependencies without explicit user approval.

---

## 6. How AI Agents Should Operate

- **Read First:** Always read this `AGENTS.md` and the `README.md` to understand the current capabilities.
- **Extension:** When adding support for a new provider (e.g., AWS S3, Wasabi):
  1. Create a new Repository class in `src/Repositories/`.
  2. Implement `Contracts/FileS3LikeInterface`.
  3. Update `FileS3Like::repository()` factory method.
  4. Create a Facade if necessary.
- **Testing:**
  - Always write a Pest test for new features.
  - Verify that existing tests pass before finishing.
- **Refactoring:**
  - Respect the existing Chainable API style. Do not break the fluent interface (`disk()->directory()->upload()`).
- **MCPS:**
  - Always use the MCPs available in your agent environment to improve yours quality.
  - You can test via mcp Playwright if needed to check for bugs e changes.
  - Use the context7 mcp to resolve any library references.
  - Use other available mcp tools as needed to ensure high-quality code.

### 6.1. Mandatory Planning Protocol (Human-in-the-loop)

> **CRITICAL: STOP AND WAIT**
>
> At the beginning of a new task, before writing any code, editing files, or running shell commands (except read-only investigation):
> 1. **Analyze & Plan:** Do a deep analysis of the task. Outline the exact steps, files to be modified, and logic to be implemented.
> 2. **Present:** Show this plan to the user clearly.
> 3. **HALT:** Do NOT execute the plan yet. You **MUST** stop and ask: "Do you approve this plan?"
> 4. **Execute:** Only proceed with tool usage (`write_file`, `run_shell_command`) *after* explicit user confirmation that they approve the plan.
