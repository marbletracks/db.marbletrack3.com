<?php
// File: /templates/admin/pages/page.tpl.php
?>
<div class="PagePanel">
    <h1><?= $page ? 'Edit Page' : 'Create Page' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Notebook ID:<br>
            <input type="text" name="notebook_id" value="<?= htmlspecialchars($page->notebook_id ?? '1') ?>">
        </label><br><br>

        <label>
            Page Number:<br>
            <input type="text" name="number" value="<?= htmlspecialchars($page->number ?? '') ?>">
        </label><br><br>

        <label>
            Created At (YYYY-MM-DD HH:MM:SS):<br>
            <input type="text" name="created_at"
                value="<?= htmlspecialchars($page->created_at ?? date('Y-m-d H:i:s')) ?>">
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
                <?php if(!empty($page->photos)):foreach ($page->photos ?? [''] as $photo): ?>
                    <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"><br>
                    <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
                <?php endforeach; ?>
                <?php endif; ?>
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

    <?php if ($page): ?>
        <p><a href="/admin/notebooks/notebook.php?id=<?= $page->notebook_id ?>">Back to Notebook
                <?= htmlspecialchars($page->notebook_id) ?></a></p>
    <?php endif; ?>
</div>
