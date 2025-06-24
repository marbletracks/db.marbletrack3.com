<?php
// File: /templates/admin/notebooks/pages/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Pages</h1>
    <p><a href="/admin/notebooks/pages/page.php">Create New Page</a></p>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($pages as $page): ?>
            <li style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                <?php if (!empty($page->photos[0])): ?>
                    <a href="<?= $page->photos[0]->getUrl() ?>" style="margin-right: 15px;">
                        <img src="<?= $page->photos[0]->getThumbnailUrl() ?>" alt="Page photo">
                    </a>
                <?php endif; ?>

                <div>
                    <strong>Page <?= htmlspecialchars($page->number) ?></strong>
                     of
                    <a href="/admin/notebooks/notebook.php?id=<?= $page->notebook_id ?>">Notebook <?= htmlspecialchars($page->notebook_id) ?></a><br>
                    <em>Created:</em> <?= htmlspecialchars($page->created_at ?? '—') ?><br>
                    <a href="/admin/notebooks/pages/page.php?id=<?= $page->page_id ?>">
                        Edit (id=<?= htmlspecialchars($page->page_id) ?>)
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/notebooks/pages/page.php">Create New Page</a></p>
</div>