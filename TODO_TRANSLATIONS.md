Here’s a `README` you can drop into your project—maybe in `docs/multilingual-support.md` or similar:

---

## 🗣 Multilingual Support for Part Names & Descriptions

This system allows each **part** to have one or more **translations**, stored in the `part_translations` table.

### 🧱 Table Schema

```sql
CREATE TABLE part_translations (
  part_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  part_name VARCHAR(255),
  part_description TEXT,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
  UNIQUE KEY part_lang_unique (part_id, language_code)
);
```

> ✅ Ensure the `UNIQUE (part_id, language_code)` constraint exists—this allows proper REPLACE behavior.

---

### 📝 Editing or Adding a Translation

The edit page `/admin/parts/part.php` currently only edits **one language at a time**.

To support multi-language editing:

* Pass `&lang=xx` in the query string (e.g. `?id=5&lang=es`)
* Default language is `'en'`

In the code:

```php
$langCode = $_GET['lang'] ?? 'en';
$repo = new PartsRepository($mla_database, $langCode);
```

Use `REPLACE INTO` to update or create translations:

```php
$mla_database->executeSQL(
    "REPLACE INTO part_translations (part_id, language_code, part_name, part_description)
     VALUES (?, ?, ?, ?)",
    'isss',
    [$part->part_id, $langCode, $name, $description]
);
```

---

### 💡 Future Enhancements

* Show language-specific edit/add buttons in the `/admin/parts/index.php` listing
* Support adding new languages via config or dropdown
* Create `LanguageRepository` to centralize supported languages

---

Let me know if you'd like this saved directly in your `docs/` or added to your CMS's backend notes.
