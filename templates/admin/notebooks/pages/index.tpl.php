<?php
// File: /templates/admin/notebooks/pages/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Pages</h1>
    <ul>
    <p><a href="/admin/notebooks/pages/page.php">Create New Page</a></p>
        <?php foreach ($pages as $page): ?>
            <li>
                <strong>ID <?= htmlspecialchars($page->page_id) ?>:</strong>
                <?php if ($page->photos[0]): ?>
                    <a href="<?= $page->photos[0]->getUrl() ?>"><img src="<?= $page->photos[0]->getThumbnailUrl() ?>" alt="Page photo"></a><br>
                <?php endif; ?>
                Page <?= htmlspecialchars($page->number) ?> of Notebook <?= htmlspecialchars($page->notebook_id) ?><br>
                <em>Created:</em> <?= htmlspecialchars($page->created_at ?? '—') ?><br>
                <a href="/admin/notebooks/notebook.php?id=<?= $page->notebook_id ?>">Notebook</a> |
                <a href="/admin/notebooks/pages/page.php?id=<?= $page->page_id ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/notebooks/pages/page.php">Create New Page</a></p>
</div>
