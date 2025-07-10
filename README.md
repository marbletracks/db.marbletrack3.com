# 🧵 Marble Track 3 Project Summary (as of June 2025)

## 🎬 Project Overview
- **Marble Track 3** is a long-term stop motion animation project.
- Started in **2017** and paused in 2022, active again in 2025.
- Filmed using **Dragonframe**, livestreamed with **OBS**.
- Workers (mostly pipe cleaner figures) build a physical marble track.
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
- Each **part** has one or more associated **phrases** showing its construction by specific **Workers**.
- Action logs look like:

```
G Choppy: cut triple splitter: 1080 - 1308
````

- Rob wants to **store the actual date of frame capture** and **installation dates for parts**.

---

## ✅ **Planned Work for `db.marbletrack3.com` (Next Development Goals)**


Consider a blog post about not using passwords
https://chatgpt.com/c/6847ecd2-3c70-8003-b9b2-c2a4d4cac8dc

---


### 🧩 `parts`

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


```sql
-- Links between parts and the snippets they appear in
CREATE TABLE parts_2_snippets (
  part_id INT NOT NULL,
  snippet_id INT NOT NULL,
  relationship ENUM('installed', 'tested', 'modified', 'removed') NOT NULL,
  PRIMARY KEY (part_id, snippet_id, relationship),
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
  FOREIGN KEY (snippet_id) REFERENCES snippets(snippet_id) ON DELETE CASCADE
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

---

### 7. **Fill in Core Tables**

* Populate `workers` and `worker_names` (done for US/English)
* Use generated SQL `INSERT` statements
* Optional: plan for `JA` translations

---

### 8. **Page Routing: Worker Profile**

* Create URLs like:

  ```
  https://db.marbletrack3.com/workers/en/g_choppy
  ```
* Page should show:

  * Worker name, alias
  * Description
  * Linked actions and snippets

---

### 9. **Friendly Redirects**

* Allow alias-based shortcuts:

  ```
  /workers/en/gc  →  /workers/en/g_choppy
  /workers/ja/cm  →  /workers/ja/candy_mama
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

