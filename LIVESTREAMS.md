# 🎥 MT3 Livestream & Episode Tracker

This module tracks YouTube livestreams from the MT3 YouTube channel, correlates them with MT3 episodes, and stores optional transcript data. Designed for flexible episode management without downloading full videos.

---

## ✅ Project Workflow (Todo List)

### 🛠 Setup & API Integration

- [x] Get Google API credentials for YouTube Data API access.
- [x] ($config->youtube_key)
- [ ] Authenticate site for reading livestream metadata and captions.

### 🔄 Livestream Polling & Viewing

- [ ] Write `admin/youtube/poll_livestreams.php` to:
  - Poll MT3 YouTube livestreams via API.
  - Store new livestreams in the database.
- [ ] Display livestreams at `/admin/livestreams/index.php` with:
  - Status (`have`, `not`, `wont`)
  - Linked episode title (if exists)
  - Button to create new episode

### 📋 Episode Management

- [ ] Design `/admin/episodes/index.php` to list all episodes
- [ ] Write `/admin/episodes/episode.php` to:
  - Create new episodes (with optional livestream association)
  - Edit existing episode metadata

### 📄 Transcript Support

- [ ] Fetch auto-generated English transcripts (if available)
- [ ] Store in separate table for on-demand access

---

## 🗃 SQL Tables

### `livestreams`

```sql
CREATE TABLE livestreams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    youtube_video_id VARCHAR(32) NOT NULL UNIQUE,
    title VARCHAR(255),
    description TEXT,
    published_at DATETIME,
    status ENUM('not', 'have', 'wont') DEFAULT 'not',
    caption_downloaded BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
````

---

### `episodes`

```sql
CREATE TABLE episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    livestream_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestream_id) REFERENCES livestreams(id) ON DELETE SET NULL
);
```

---

### `livestream_transcripts`

```sql
CREATE TABLE livestream_transcripts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livestream_id INT NOT NULL,
    transcript LONGTEXT,
    source ENUM('auto', 'manual') DEFAULT 'auto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestream_id) REFERENCES livestreams(id) ON DELETE CASCADE
);
```

---

## 🧠 Notes

* Each livestream can either:

  * Be used in an episode (`have`)
  * Not be used (`not`)
  * Be explicitly excluded (`wont`)
* Each episode can have 0 or 1 linked livestream.
* Transcripts are stored separately to avoid loading them unnecessarily.

