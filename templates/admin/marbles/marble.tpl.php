<div class="PagePanel">
    <h1><?= $marble ? 'Edit Marble' : 'Add Marble' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Alias (shortcode trigger, e.g. <code>Lb</code> for Large blue):<br>
            <input type="text" name="marble_alias" value="<?= htmlspecialchars($marble->marble_alias ?? '') ?>">
        </label><br><br>

        <label>
            Name:<br>
            <input type="text" name="marble_name" value="<?= htmlspecialchars($marble->marble_name ?? '') ?>">
        </label><br><br>

        <label>
            Team Name (optional, e.g. "The Three Tigers"):<br>
            <input type="text" name="team_name" value="<?= htmlspecialchars($marble->team_name ?? '') ?>">
        </label><br><br>

        <label>
            Size:<br>
            <select name="size">
                <option value="small" <?= ($marble->size ?? '') === 'small' ? 'selected' : '' ?>>Small</option>
                <option value="medium" <?= ($marble->size ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                <option value="large" <?= ($marble->size ?? '') === 'large' ? 'selected' : '' ?>>Large</option>
            </select>
        </label><br><br>

        <label>
            Color:<br>
            <input type="text" name="color" value="<?= htmlspecialchars($marble->color ?? '') ?>">
        </label><br><br>

        <label>
            Quantity (how many on set):<br>
            <input type="number" name="quantity" min="1" value="<?= $marble->quantity ?? 1 ?>">
        </label><br><br>

        <label>
            Description:<br>
            <textarea name="description" rows="5" cols="80"><?= htmlspecialchars($marble->description ?? '') ?></textarea>
        </label><br><br>

        <label>
            Photo URLs (one per line):<br>
            <textarea name="photo_urls" rows="4" cols="80"><?php
                if ($marble && !empty($marble->photos)) {
                    echo htmlspecialchars(implode("\n", array_map(fn($p) => $p->getUrl(), $marble->photos)));
                }
            ?></textarea>
        </label><br><br>

        <button type="submit">Save</button>
    </form>

    <?php if ($marble): ?>
        <p>Shortcode: <code>[marble:<?= htmlspecialchars($marble->slug) ?>]</code></p>
    <?php endif; ?>
</div>
