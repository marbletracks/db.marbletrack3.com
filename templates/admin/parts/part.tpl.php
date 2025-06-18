<div class="PagePanel">
    <h1><?= $part ? 'Edit Part' : 'Create Part' ?></h1>

    <?php if (!empty($errors)): ?>
            <div class="Errors">
                <?php foreach ($errors as $err): ?>
                        <p class="error"><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Alias:<br>
            <input type="text" name="part_alias" value="<?= htmlspecialchars($part->part_alias ?? '') ?>">
        </label><br><br>

        <label>
            Name:<br>
            <input type="text" name="part_name" value="<?= htmlspecialchars($part->name ?? '') ?>">
        </label><br><br>

        <label>
            Description:<br>
            <textarea name="part_description" rows="5" cols="60"><?= htmlspecialchars($part->description ?? '') ?></textarea>
        </label><br><br>

        <button type="submit">Save</button>
    </form>
</div>
