<?php
require_once __DIR__ . '/../../lib/thumbnail_for_string.php';
?>

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
        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php foreach ($image_urls ?? [''] as $url): ?>
    <img src="<?= thumbnail_for_string($url, 100, 100) ?>" alt="Image preview" style="max-width: 100px; max-height: 100px;"><br>
                <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($url) ?>"><br>
<?php endforeach; ?>
            </div>
            <button type="button" onclick="addImageUrlField()">Add another</button>
        </label>

        <script>
            function addImageUrlField() {
                const div = document.getElementById('image-url-fields');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'image_urls[]';
                div.appendChild(input);
                div.appendChild(document.createElement('br'));
            }
        </script>

        <button type="submit">Save</button>
    </form>
</div>
