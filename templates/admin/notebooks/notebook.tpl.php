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
        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php foreach ($notebook->photos ?? [''] as $photo): ?>
    <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"><br>
                <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
<?php endforeach; ?>
                <!-- add empty row so we always have space -->
                <input type="text" size=130 name="image_urls[]" value=""><br>
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
