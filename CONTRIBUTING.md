# Contributing to db.marbletrack3.com

This document outlines the development conventions and architectural patterns for this project. It should be used as a reference when adding new features or modifying existing ones.

## 1. Guiding Principle

When in doubt, **use existing files as templates and guides.** The patterns below should be followed, but the existing code is the ultimate source of truth. If any instruction seems unclear or contradicts an established pattern, please ask for clarity.

## 2. Core Architecture

The application is a hybrid PHP system:
1.  A **dynamic admin panel** for content management (`/admin`).
2.  A **statically generated frontend** for public, read-only viewing.

All new feature development should respect this separation.

## 3. Adding a New Entity (e.g., "History")

Adding a new data entity to the project involves the following steps:

### Step 1: Database Schema

-   **Location:** New `.sql` files for `CREATE TABLE` statements go into an appropriate subdirectory within `db_schemas/`.
-   **Primary Keys:** Must be named `table_name_id` (e.g., `history_id`). They must be `INT UNSIGNED NOT NULL AUTO_INCREMENT`.
-   **Timestamps:** All tables must have a `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` column.
-   **Example:**
    ```sql
    CREATE TABLE `history` (
      `history_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `worker_id` INT UNSIGNED NOT NULL,
      `action_description` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`history_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```

### Step 2: PHP Model Class

-   **Purpose:** A simple data object representing a single database row.
-   **Namespace:** Core physical objects belong in the `\Physical` namespace.
-   **Location:** `classes/Physical/`.
-   **Structure:** Use constructor property promotion.
-   **Example (`classes/Physical/History.php`):**
    ```php
    <?php
    namespace Physical;

    class History {
        public function __construct(
            public int $history_id,
            public int $worker_id,
            public string $action_description,
            public string $created_at
        ) {}
    }
    ```

### Step 3: PHP Repository Class

-   **Purpose:** Handles all database interactions for an entity.
-   **Namespace:** `\Database`.
-   **Location:** `classes/Database/`.
-   **Database Interaction:** The repository's constructor must accept an object that implements `\Database\DbInterface`. It **must not** connect to the database directly. All queries must be performed through the `DbInterface` object.
-   **Hydration:** Repositories should include a private `hydrate()` method that converts a database row array into a Model object.
-   **Example (`classes/Database/HistoryRepository.php`):**
    ```php
    <?php
    namespace Database;

    use Physical\History;

    class HistoryRepository {
        private DbInterface $db;

        public function __construct(DbInterface $db) {
            $this->db = $db;
        }

        public function findByWorker(int $worker_id): array {
            $results = $this->db->fetchResults(
                "SELECT * FROM history WHERE worker_id = ? ORDER BY created_at DESC",
                'i',
                [$worker_id]
            );
            // ... loop and hydrate ...
        }

        private function hydrate(array $row): History {
            return new History(/* ... */);
        }
    }
    ```

### Step 4: Admin Panel Integration

-   **Controller (`wwwroot/admin/`):**
    1.  All PHP files inside `/wwwroot/admin/` must begin with the following boilerplate to ensure security and proper environment setup:
        ```php
        <?php
        declare(strict_types=1);
        include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

        if (!$is_logged_in->isLoggedIn()) {
            header("Location: /login/");
            exit;
        }
        ```
    2.  Instantiate the new Repository.
    3.  Fetch data using the Repository.
    4.  Instantiate the `\Template` class for the view.
    5.  Pass data to the template using `$tpl->set()`.
    6.  Render the inner view and pass it to the `layout/admin_base.tpl.php`.
-   **View (`templates/admin/`):**
    1.  Create a template file to display the data.
    2.  **Consider** escaping output with `htmlspecialchars()` for security.
    3.  Follow the structure of existing templates like `templates/admin/workers/index.tpl.php`.
