<div class="PagePanel">
    <h1><?= $notebook ? 'Edit Notebook' : 'Create Notebook' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Title:<br>
            <input type="text" name="title" value="<?= htmlspecialchars($notebook->title ?? '') ?>">
        </label><br><br>

        <label>
            Created At (YYYY-MM-DD HH:MM:SS):<br>
            <input type="text" name="created_at"
                value="<?= htmlspecialchars($notebook->created_at ?? date('Y-m-d H:i:s')) ?>">
        </label><br><br>

        <button type="submit">Save</button>
    </form>
</div>
