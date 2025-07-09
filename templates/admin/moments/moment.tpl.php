<div class="PagePanel">
    <h1><?= $moment ? 'Edit Moment' : 'Create Moment' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Notes:<br>
            <textarea name="notes" rows="4" cols="50"><?= htmlspecialchars($moment->notes ?? '') ?></textarea>
        </label><br><br>

        <label>
            Frame Start:<br>
            <input type="number" name="frame_start" value="<?= htmlspecialchars((string)($moment->frame_start ?? '')) ?>">
        </label><br><br>

        <label>
            Frame End:<br>
            <input type="number" name="frame_end" value="<?= htmlspecialchars((string)($moment->frame_end ?? '')) ?>">
        </label><br><br>

        <label>
            Phrase ID:<br>
            <input type="number" name="phrase_id" value="<?= htmlspecialchars((string)($moment->phrase_id ?? '')) ?>">
        </label><br><br>

        <label>
            Take:<br>
            <select name="take_id">
                <option value="">-- Select a Take --</option>
                <?php foreach ($takes as $take): ?>
                    <option value="<?= $take->take_id ?>" <?= ($moment && $moment->take_id == $take->take_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($take->take_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php foreach ($moment->photos as $photo): ?>
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
