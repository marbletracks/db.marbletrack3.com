<div class="PagePanel">
    <h1><?= $worker ? 'Edit Worker' : 'Create Worker' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Name:<br>
            <input type="text" name="name" value="<?= htmlspecialchars($worker->name ?? '') ?>">
        </label><br><br>
        <label>
            Alias:<br>
            <input type="text" name="worker_alias" value="<?= htmlspecialchars($worker->worker_alias ?? '') ?>">
        </label><br><br>
        <label>
            Description:<br>
            <textarea id="shortcodey" name="description" rows="15" cols="100"><?= htmlspecialchars($worker->description ?? '') ?></textarea>
            <div id="autocomplete"></div>
        </label><br><br>
        <label>
            Image URLs:<br>
            <div id="image-url-fields">
                <?php if(!empty($worker->photos)):foreach ($worker->photos ?? [''] as $photo): ?>
                    <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"><br>
                    <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
                <?php endforeach; ?>
                <?php endif; ?>
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
<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>

