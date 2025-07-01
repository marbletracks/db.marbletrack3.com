<?php
// File: /templates/admin/notebooks/pages/columns/column.tpl.php
?>
<div class="PagePanel">
    <h1><?= $column ? 'Edit Column' : 'Create Column' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($page): ?>
        <p><a href="/admin/notebooks/pages/page.php?id=<?= $page->page_id ?>">Back to Page <?= htmlspecialchars($page->number) ?></a></p>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Worker:<br>
            <select name="worker_id">
                <option value="">-- Select a Worker --</option>
                <?php foreach ($workers as $worker): ?>
                    <option value="<?= $worker->worker_id ?>" <?= ($column->worker_id ?? '') == $worker->worker_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars("{$worker->worker_alias} - {$worker->name}") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>
            Column Name:<br>
            <input type="text" name="col_name" value="<?= htmlspecialchars($column->col_name ?? '') ?>">
        </label><br><br>

        <label>
            Sort Order:<br>
            <input type="number" name="col_sort" value="<?= htmlspecialchars($column->col_sort ?? '0') ?>">
        </label><br><br>

        <button type="submit">Save Column</button>
    </form>
</div>