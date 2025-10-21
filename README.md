# wi-Q PHP Backend Developer Test

This is my submission for the **wi-Q Backend Developer (PHP)** test.
The task was to build a small, framework-agnostic PHP library that consumes a fictitious REST API — **Great Food Ltd** — and demonstrates the ability to authenticate, fetch, parse, and update API data.

The project is designed to run **locally via Laragon** (or any PHP 8.2+ environment) without needing a real API.
It uses fixture JSON responses to simulate endpoints, but the architecture is ready to switch to live HTTP requests when required.

---

## 🧱 Tech stack

- **PHP 8.2+**
- **Composer autoloading (PSR-4)**
- **PHPUnit 11** for testing
- **No frameworks** – just a small custom library (`src/`)
- **FixtureTransport** – simulates REST endpoints using local JSON files
- **CurlTransport** – ready for real HTTP if the API existed

---

## 🧩 Project structure

```
src/
  ApiClient.php
  Model/
    Menu.php
    Product.php
  Transport/
    TransportInterface.php
    FixtureTransport.php
    CurlTransport.php
bin/
  run_scenario1.php
  run_scenario2.php
responses/
  token.json
  menus.json
  menu-products.json
tests/
  ApiClientTest.php
composer.json
phpunit.xml
```

---

## ⚙️ Setup

1. **Clone or extract** into your Laragon `www` folder.
2. Open a terminal in the project directory.
3. Run dependencies:

   ```bash
   composer install
   ```

4. Run the scenarios or tests as shown below.

---

## ▶️ Usage

### Scenario 1 – Fetch “Takeaway” menu products

```bash
composer scenario1
```

**Output (using fixture data):**

```
| ID | Name         |
| -- | ------------ |
| 1  | Large Pizza  |
| 2  | Medium Pizza |
| 3  | Burger       |
| 4  | Chips        |
| 5  | Soup         |
| 6  | Salad        |
```

**What happens:**
- The client authenticates using `POST /auth_token`
- Requests `/menus` to find the “Takeaway” menu
- Requests `/menu/{id}/products` and formats the results into a table

---

### Scenario 2 – Update a product (fix typo)

```bash
composer scenario2
```

**Output (simulated update):**

```
Before: Product #84 name = N/A
After:  Product #84 name = Chips
PUT /menu/7/product/84 => 200 OK (simulated)
```

**What happens:**
- Authenticates as before
- Sends a `PUT /menu/7/product/84` request with the corrected product model
- FixtureTransport writes to `responses/state.json` to simulate persistence
- The next GET request reflects the corrected name

---

### Run the tests

```bash
composer test
```

PHPUnit runs through both scenarios automatically, verifying that:
- The Takeaway menu and expected products exist.
- Product 84 can be updated successfully.

---

## 🧠 Design notes

- **Offline-first design:** Everything runs from static fixtures so it’s portable and deterministic.
- **Swappable transport layer:** Real HTTP requests can be enabled simply by replacing:
  ```php
  $transport = new FixtureTransport(__DIR__ . '/../responses');
  // with
  $transport = new CurlTransport();
  ```
- **Minimal abstractions:** No frameworks, no heavy DI. Just clean OOP with PSR-4 and typed DTOs.
- **State simulation:** `responses/state.json` acts as a fake persistence layer for updates.

---

## 🧪 Example real-world extension

If this were a production library, I’d expand it to include:

| Area | What I’d add |
|------|---------------|
| **Error handling** | Proper exception types and response parsing (401/404/500). |
| **Logging** | PSR-3 compliant logging to file or stdout. |
| **Environment config** | Use `vlucas/phpdotenv` for `.env` instead of raw `getenv()`. |
| **Real HTTP transport** | Extend `CurlTransport` to return headers and handle JSON automatically. |
| **Data validation** | Lightweight schema validation before serializing product updates. |
| **Continuous testing** | GitHub Actions CI running `composer test` on push. |
| **Containerization** | Simple Dockerfile for quick spin-up with PHP + Composer preinstalled. |

---

## ✅ Summary

- Built an **offline-capable, framework-agnostic PHP library** for REST API consumption.
- Demonstrated authentication, data retrieval, and item update workflows.
- Produced **clean, testable, PSR-4 code** with extensible transport layers.
- Entire project runs under Laragon or any local PHP 8.2+ setup, no external API required.

---

**Author:** James Timms
**Test:** wi-Q PHP Backend Developer Assessment
**Time spent:** ~1.5 hours (including setup, tests, and documentation)
