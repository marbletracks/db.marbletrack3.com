# 🧵 Marble Track 3 Project Summary (as of June 2025)

## 🎬 Project Overview
- **Marble Track 3** is a long-term stop motion animation project.
- Started in **2017** and paused in 2022, active again in 2025.
- Filmed using **Dragonframe**, livestreamed with **OBS**.
- Characters (mostly pipe cleaner figures) build a physical marble track.
- Thousands of frames are organized into **snippets**, which will be compiled into a full-length movie.

---

## 🏛 Vision for Museum Installation
- The finished physical track will sit in a **glass display case** in the center of a museum room.
- Surrounding monitors will show staggered playback of the full animation:
  - Casual viewers can scan the room and see progress quickly.
  - Focused viewers can follow one screen, then move leftward to rewind 5 minutes at a time.
- Rob will occasionally demonstrate marbles rolling down the physical track.

---

## 🌐 Website & Data Architecture Goals
- Each **part** has one or more associated **snippets** showing its construction by specific **characters**.
- Action logs look like:

```
G Choppy: cut triple splitter: 1080 - 1308
````

- Rob wants to **store the actual date of frame capture** and **installation dates for parts**.

---

## ✅ **Planned Work for `db.marbletrack3.com` (Next Development Goals)**

### 1. **Create Subdomain and Project Base**

* ✅ **Subdomain**: `https://db.marbletrack3.com`
* **Purpose**: A database-driven mirror of `www.marbletrack3.com`, focusing on structured data access.
* ✅ **Hosting**: Dreamhost shared hosting
* **Stack**: PHP + MySQL (InnoDB engine recommended)

### 2. **Local Project Setup**

* Create a basic loginable site based on Quick, possibly with 2FA built in

** check DB exists with DBExistaroo.php
*** Look for config username
*** Look for DB TABLE users
*** If users table DNE:

## ✅ Admin Setup Flow Checklist

* A **personal starter framework**
* Intended for spinning up **many small sites**
* Not housing government secrets™
* And the setup password is **not reused anywhere**

…it's perfectly reasonable to:

### ✅ **Hardcode the bcrypt hash directly into the repo**

…in a class or config file like:

```php
class SetupPassword {
    public const HASH = '$2y$10$TtO6K3...Wc4zU3rO'; // bcrypt for 'letmein123'
}
```

Then check with:

```php
if (password_verify($_POST['setup_password'], \SetupPassword::HASH)) {
    $_SESSION['setup_verified'] = true;
}
```

---

## 🧩 Bonus Thought

If you ever do want to add a *tiny* bit of plausible deniability without making your life harder, just put the hash in a file like `config/setup-password.php` with a comment:

```php
<?php
// This is a per-site bcrypt hash for first-time admin setup.
// Safe to keep in repo. Never reused elsewhere.

return '$2y$10$TtO6K3...Wc4zU3rO';
```

Minimal indirection, but you can still include it like:

```php
$hash = require __DIR__ . '/../config/setup-password.php';
```

---

## 🧠 TL;DR

Your decision is **rational, secure enough, and aligned with your workflow**. Ignore the purists. Ship your code. Build your kingdoms. 👑

---

### 2️⃣ **Make Sure the Password Check Works**

* In `/create/index.php`, load the secret and validate user input:

```php
$secret = require __DIR__ . '/../.setup_secret.php';

if (isset($_POST['setup_password'])) {
    if (password_verify($_POST['setup_password'], $secret['setup_password_hash'])) {
        $_SESSION['setup_verified'] = true;
        // Continue to admin creation form
    } else {
        echo "❌ Incorrect setup password.";
    }
}
```

* Manually test this by entering correct and incorrect values.

---

### 3️⃣ **Add the “New Password” Field for Admin**

* After setup password is validated, show a form with:

```html
<form method="post">
  <label>Admin Username:</label>
  <input type="text" name="username" required><br>

  <label>New Admin Password:</label>
  <input type="password" name="new_password" required><br>

  <button type="submit">Create Admin</button>
</form>
```

---

### 4️⃣ **Debug: Print the Received Password**

> (Only for testing — remove this afterward.)

```php
if (isset($_POST['new_password'])) {
    echo "<pre>New password: " . htmlspecialchars($_POST['new_password']) . "</pre>";
}
```

Check:

* Password is being submitted properly
* Nothing is missing or truncated

---

### 5️⃣ **Encrypt the Password and Write to DB**

* Use `password_hash()` before inserting:

```php
$password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

$db = \Database\Base::getDB($config);
$db->insertFromRecord('users', 'ss', [
    'username' => $_POST['username'],
    'password_hash' => $password_hash
]);
```

* Catch errors if username already exists.

---

### 6️⃣ **Confirm You Cannot Visit `/create/` Again**

* Once admin exists, `prepend.php` should detect it:

```php
$testaroo = new \Database\DBTestaroo($config);
if (empty($testaroo->checkaroo())) {
    header("Location: /login.php");
    exit;
}
```

* Visiting `/create/` should now redirect you to `/login.php`

---

### 7️⃣ **Confirm You Can Log In With the New Password**

* Go to your login form
* Use `password_verify($input, $stored_hash)` to authenticate:

```php
// Login logic
$stored_hash = $row['password_hash'];
if (password_verify($_POST['password'], $stored_hash)) {
    echo "✅ Login successful";
} else {
    echo "❌ Incorrect password";
}
```

* Test with both correct and incorrect credentials

---

### 🚀 Optional Clean-Up

* Delete `.setup_secret.php` after first use
* Unset `$_SESSION['setup_verified']` once admin is created
* Log IP or time of successful setup if you want traceability


/end If users tables DNE ^^

**
** login page
** login
** 2FA ???
** admin page
** Lemur: scp_to_example.sh

* Move README.md somewhere safe
* Rewrite README.md to minimal biz
* Save that new repo next to new-DH-whatsit repo

* Keep going:
* Restore README

* Create a **local Git repository**
* Initialize directory structure:

e.g.

  ```
  /public_html/
    index.php
    /workers/
    /parts/
    /snippets/
    /admin/
  ```
* Add `.gitignore`, `.htaccess`, and setup URL routing if desired (e.g., route through `index.php`)

---

Consider a blog post about not using passwords
https://chatgpt.com/c/6847ecd2-3c70-8003-b9b2-c2a4d4cac8dc

---

### 4. **Create Migrations**

* Start with the full SQL schema we finalized
* Include:

  * `workers`, `worker_names`
  * `parts`, `part_histories`, `part_history_translations`
  * `frames`, `snippets`, `frames_2_snippets`
  * `actions`, `part_connections`
* Use raw `.sql` or a PHP-based migration tool

## 🗃️ SQL Database Schema Ideas


### 🧩 `parts`

```sql
CREATE TABLE parts (
  part_id INT AUTO_INCREMENT PRIMARY KEY,
  part_alias VARCHAR(20) NOT NULL UNIQUE,
  part_name VARCHAR(255) NOT NULL,
  part_description TEXT,
  start_frame_id INT,
  installed_frame_id INT,
  FOREIGN KEY (start_frame_id) REFERENCES frames(frame_id)
    ON DELETE SET NULL,
  FOREIGN KEY (installed_frame_id) REFERENCES frames(frame_id)
    ON DELETE SET NULL
) COMMENT='Some parts are multiple pieces, while other parts are individual pieces; not all pieces have names.';
```

---

### 🕰 `part_histories`

```sql
CREATE TABLE part_histories (
  part_history_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  event_date DATE,
  FOREIGN KEY (part_id) REFERENCES parts(part_id)
    ON DELETE CASCADE
);
```

### 🌐 `part_history_translations`

```sql
CREATE TABLE part_history_translations (
  part_history_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  history_title VARCHAR(255),
  history_description TEXT,
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id)
    ON DELETE CASCADE
);
```

---

### 🌐 `part_history_photos`

```sql
CREATE TABLE part_history_photos (
  part_history_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  photo_sort TINYINT NOT NULL,
  history_photo VARCHAR(255),
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id)
    ON DELETE CASCADE
);
```

---

### 🎞️ `frames`

```sql
CREATE TABLE frames (
  frame_id INT AUTO_INCREMENT PRIMARY KEY,
  frame_number INT NOT NULL,
  frame_timestamp DATETIME NOT NULL,
  scene_number INT,
  take_number INT
) COMMENT='These are individual images that go together to make the movie of workers building Marble Track 3.';
```

---

### 🎬 `snippets`

```sql
CREATE TABLE snippets (
  snippet_id INT AUTO_INCREMENT PRIMARY KEY,
  snippet_name VARCHAR(255),
  part_id INT,
  worker_id INT,
  snippet_notes TEXT,
  FOREIGN KEY (part_id) REFERENCES parts(part_id)
    ON DELETE SET NULL,
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id)
    ON DELETE SET NULL
);
```

---

### 🔁 `snippets_2_frames`

```sql
CREATE TABLE snippets_2_frames (
  snippet_frame_id INT AUTO_INCREMENT PRIMARY KEY,
  snippet_id INT NOT NULL,
  frame_id INT NOT NULL,
  frame_order_within_snippet INT NOT NULL,
  FOREIGN KEY (snippet_id) REFERENCES snippets(snippet_id)
    ON DELETE CASCADE,
  FOREIGN KEY (frame_id) REFERENCES frames(frame_id)
    ON DELETE CASCADE,
  UNIQUE (snippet_id, frame_order_within_snippet)
);
```

---

### 🎭 `actions`

```sql
CREATE TABLE actions (
  action_id INT AUTO_INCREMENT PRIMARY KEY,
  worker_id INT NOT NULL,
  part_id INT NOT NULL,
  action_label VARCHAR(100),
  start_frame_id INT,
  end_frame_id INT,
  action_notes TEXT,
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id)
    ON DELETE CASCADE,
  FOREIGN KEY (part_id) REFERENCES parts(part_id)
    ON DELETE CASCADE,
  FOREIGN KEY (start_frame_id) REFERENCES frames(frame_id)
    ON DELETE SET NULL,
  FOREIGN KEY (end_frame_id) REFERENCES frames(frame_id)
    ON DELETE SET NULL
) COMMENT='These are some of the things our workers did to create the track.';
```

---

### 🔗 `part_connections`

```sql
CREATE TABLE part_connections (
  connection_id INT AUTO_INCREMENT PRIMARY KEY,
  from_part_id INT NOT NULL,
  to_part_id INT NOT NULL,
  marble_sizes SET('small', 'medium', 'large') NOT NULL,
  connection_description TEXT,
  FOREIGN KEY (from_part_id) REFERENCES parts(part_id)
    ON DELETE CASCADE,
  FOREIGN KEY (to_part_id) REFERENCES parts(part_id)
    ON DELETE CASCADE
) COMMENT='from -> to flows with gravity; marble_sizes can be small, medium, large, or any combo';
```

---

### 7. **Fill in Core Tables**

* Populate `workers` and `worker_names` (done for US/English)
* Use generated SQL `INSERT` statements
* Optional: plan for `JA` translations

---

### 8. **Page Routing: Worker Profile**

* Create URLs like:

  ```
  https://db.marbletrack3.com/workers/US/g_choppy
  ```
* Page should show:

  * Worker name, alias
  * Description
  * Linked actions and snippets

---

### 9. **Friendly Redirects**

* Allow alias-based shortcuts:

  ```
  /workers/US/gc  →  /workers/US/g_choppy
  /workers/JA/cm  →  /workers/JA/candy_mama
  ```
* Implement using `.htaccess` or internal PHP mapping

---

### 10. **Admin Dashboard**

* Setup `/admin/` area
* Start with simple PHP forms:

  * Add/edit `workers`, `parts`, `actions`, etc.
* Secure access (e.g., `.htpasswd`, or login session)
* Later: transition to richer form builder or JS-based interface


===============================


---

## 🧠 Engineering: Interactive Switches

Marble size determines interaction with physical mechanisms:

* **El Lifty Lever**: Large marbles lift the lever and move "El Bandarín" flag out of the way so small marbles can pass.
* **Caret Splitter**:

  * Medium marbles must **always go left**.
  * Small marbles may go **left or right**.
  * Rudder motion is **horizontal** on a vertical spindle.

---

## 🛠 Rudder Brainstorm

### Constraints:

* Must be LEFT when medium marbles arrive
* Medium marbles trigger mechanism
* Small marbles do **not** affect it
* Motion is **horizontal**

### Options:

1. **Pre-trigger paddle** only medium marbles touch, nudging rudder left.
2. **One-way flipper** resets left if small marble chooses right.
3. **Weight-based horizontal gear** activated only by medium weight.
4. **Cam wheel** rotated by medium marbles to lock rudder left.

---

Rob is looking for ways to:

* Design reliable **size-based triggers**
* Keep all mechanisms mechanical and passive
* Document and visualize the track logic as a **connected system**

```

### 5. **S3 Frame Storage Planning**

* **Goal**: Store raw animation frames (Dragonframe `X1`) on S3
* **Naming Convention**:

  ```
  s3://marbletrack3-frames/scene_2/take_11/X1/0001.png
  ```
* Each uploaded take should:

  * Include `scene_number`, `take_number`
  * Log metadata like `frame_number`, timestamp, size

---

### 6. **Create Snippets from S3 Frames**

* Build a local tool (CLI or PHP) to:

  * Select frames from S3 based on scene/take
  * Define snippets via GUI or a `.json`/.md sidecar
  * Generate `snippets` entries and link frames via `frames_2_snippets`

