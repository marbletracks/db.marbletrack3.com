<div class="PagePanel">
    <h1>All Notebooks</h1>
    <ul>
        <?php foreach ($notebooks as $notebook): ?>
            <li>
                <strong>ID <?= htmlspecialchars($notebook->notebook_id) ?>:</strong>
                <?= htmlspecialchars($notebook->title ?? '(no title)') ?><br>
                <em>Created:</em> <?= htmlspecialchars($notebook->created_at ?? '—') ?><br>
                <a href="/admin/notebooks/notebook.php?id=<?= $notebook->notebook_id ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/admin/notebooks/notebook.php">Create New Notebook</a></p>
</div>
