<div class="PagePanel">
    <h1><?= $photo ? 'Edit Photo' : 'Create Photo' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            CDN Code (preferred):<br>
            <input type="text" name="code" value="<?= htmlspecialchars($photo->code ?? '') ?>">
        </label><br><br>

        <label>
            Fallback URL:<br>
            <input type="text" name="url" value="<?= htmlspecialchars($photo->url ?? '') ?>">
        </label><br><br>

        <?php if ($photo): ?>
            <label>Preview:<br>
                <img src="<?= htmlspecialchars($photo->getUrl()) ?>" style="max-height:200px;">
            </label><br><br>
        <?php endif; ?>

        <button type="submit">Save</button>
    </form>
</div>
