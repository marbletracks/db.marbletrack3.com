# Analysis: Dynamic PHP vs. Static Site Generation

This document explores the trade-offs between a traditional dynamic PHP application (where every request queries the database) and a Static Site Generation (SSG) approach for the read-only frontend of your project.

## 1. The Core Concepts

### Dynamic PHP (Current Model)

-   **How it works:** A user requests a page. The web server (Apache) executes a PHP script. The script connects to the database, fetches the necessary data, renders it into an HTML template, and returns the result to the user.
-   **Pros:**
    -   **Real-time:** Content is always 100% up-to-date. A change in the database is reflected instantly on the next page load.
    -   **Flexibility:** Easily handles user-specific content, forms, and complex interactions.
-   **Cons:**
    -   **Performance:** Slower. Each page view incurs the overhead of a database connection, query execution, and server-side rendering. This is often referred to as "Time to First Byte" (TTFB).
    -   **Scalability:** Requires more server resources (CPU, RAM) to handle high traffic, as every visitor puts a load on the database and PHP interpreter.
    -   **Security:** The database is directly exposed to the web-facing application, increasing the potential attack surface.

### Static Site Generation (SSG)

-   **How it works:** You run a "build process" or a "generator script." This script connects to the database *once*, reads all the data needed for the site (e.g., all workers, notebooks, pages, tokens), and renders *every possible page* as a static `.html` file. Your web server is then configured to just serve these pre-built files.
-   **Pros:**
    -   **Lightning Speed:** Serving a static HTML file is the fastest possible way to deliver a web page. TTFB is minimal.
    -   **Massive Scalability:** A simple web server can handle enormous amounts of traffic because it's just sending files. Caching via a Content Delivery Network (CDN) becomes trivial and extremely effective.
    -   **Enhanced Security:** The public-facing site has no live connection to your database, drastically reducing the attack surface. The dynamic, database-connected part of your application (the admin panel) can be firewalled off or protected separately.
    -   **Lower Cost:** Hosting static files is typically cheaper than hosting a dynamic application.
-   **Cons:**
    -   **Build Step Required:** Content is not updated automatically. You must run the generator to see changes.
    -   **Potential for Stale Data:** The site is only as fresh as the last build. This is the primary trade-off.

## 2. Answering Your Key Questions

### "How often do we update the site?"

This is the central question for an SSG architecture. You decide when to trigger the build process. Common strategies include:

1.  **On-Demand (Webhook - Recommended):** This is the most effective method. Your admin panel (where you edit, create, or delete data) would be modified. After any successful database write (e.g., saving a token, updating a page name), the PHP code would trigger the generator script to run in the background. This keeps the site fresh without manual intervention.
2.  **Scheduled (Cron Job):** A script is scheduled to run automatically at a regular interval (e.g., every 15 minutes, once an hour). This is simpler to implement but means there can be a delay between a content update and it appearing on the live site.
3.  **Manual:** A developer or administrator manually runs the build command whenever they deem it necessary. This is the simplest approach but the most hands-on.

### "If a single new row is edited or added, how much of the site needs to be recreated?"

1.  **The Simple Answer: Rebuild Everything.**
    For most small-to-medium sized sites, the easiest and most reliable approach is to regenerate the entire site on every change. A well-written generator can create thousands of pages in seconds or a few minutes. The simplicity and reliability of this approach often outweigh the benefits of a more complex, incremental system.

2.  **The Advanced Answer: Incremental Builds.**
    It is possible to build a "smart" generator that understands the relationships in your data. For example:
    -   **Editing a Token:** Only the HTML file for the specific `column` page that token belongs to needs to be regenerated.
    -   **Editing a Page's Name:** The page's own HTML file needs to be regenerated, as well as the parent `notebook`'s index page where the page name might be listed.
    -   **Adding a new Worker:** The main workers index page and the new worker's individual page would need to be created/updated.

    This approach is much faster for large sites but adds significant complexity to the generator script, which now has to track dependencies.

## 3. How Would This Work For `db.marbletrack3.com`?

A hybrid approach would be ideal:

-   **Frontend (`/`, `/workers/`, `/notebooks/...`):** Becomes a static site.
-   **Backend (`/admin/...`):** Remains the exact same dynamic PHP application it is today.

The generator would be a new PHP script (`generate_site.php`) that:
1.  Is executed by a webhook from the admin panel.
2.  Reads from the `Database` and `Repository` classes you've already built.
3.  Uses the `Template.php` engine (or a similar one) to render the output.
4.  Instead of echoing the HTML, it uses `file_put_contents()` to save the rendered HTML to the correct file path (e.g., `wwwroot/notebooks/pages/42.html`).
5.  Apache would be configured (using `mod_rewrite`) to serve `notebooks/pages/42.html` if it exists when a user requests `/notebooks/pages/page.php?id=42`.

### Handling Complex Database Relationships

The core of the generator is to "pre-calculate" all the relationships and build a fully interlinked site. You move from a "query-on-demand" model to a "pre-build-all-paths" model.

The process is hierarchical:

1.  **Generate "Leaf" Pages First:** Start with the most granular items (the individual `Worker`, `Part`, `Token`, etc.).
    -   The script queries the database for all rows in a table (e.g., `SELECT * FROM workers`).
    -   For each row, it generates a single detail page (e.g., `wwwroot/workers/alice.html`).
    -   **Crucially, while generating this page, it also fetches all related data.** For a worker, it would fetch all parts they assembled. For a part, it would fetch the worker who made it and the tracks it belongs to.
    -   These relationships are rendered as simple hyperlinks pointing to the *static paths* of the other pages (e.g., `<a href="/parts/p-001.html">`).

2.  **Generate "Index" Pages Last:** After all the individual "leaf" pages exist, the script creates the index pages (`/workers/`, `/parts/`, etc.).
    -   These pages simply query a table (e.g., `SELECT * FROM workers`) and create a list of links to the static detail pages that were just generated in the previous step.

This approach creates a "map" of your database in the form of a static file structure. The generator script itself defines this map by the order it queries the database and how it constructs the hyperlinks in the templates.

A conceptual generator script might look like this:

```php
<?php
// pseudo-code for generate_site.php

// 1. Setup (Autoloader, DB Connection)
// 2. Clean old static files from /wwwroot/workers, etc.

// 3. Generate Leaf Pages
$allWorkers = $workersRepo->findAll();
foreach ($allWorkers as $worker) {
    $partsByWorker = $partsRepo->findByWorker($worker->worker_id);
    $html = render_template('frontend/workers/worker.tpl.php', ['worker' => $worker, 'parts' => $partsByWorker]);
    file_put_contents("wwwroot/workers/{$worker->worker_alias}.html", $html);
}
// ... repeat for all parts, tracks, etc.

// 4. Generate Index Pages
$html = render_template('frontend/workers/index.tpl.php', ['workers' => $allWorkers]);
file_put_contents("wwwroot/workers/index.html", $html);
// ... repeat for all other indexes.

echo "Static site generation complete!";
```

## Conclusion & Recommendation

For a content-driven project like this, **adopting a static site generation model for the public-facing frontend is a highly recommended, modern approach.**

You gain significant benefits in **performance, scalability, and security** for the cost of adding a build step.

**Recommendation:**
1.  Keep the existing PHP admin panel as is.
2.  Create a single PHP generator script that can rebuild the entire frontend site.
3.  Trigger this script using a webhook from the admin panel after database writes.
4.  Configure your web server to prioritize serving the generated static HTML files.

This strategy provides the best of both worlds: a fast, secure frontend and a fully dynamic, real-time backend for content management.
