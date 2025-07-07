<div class="PagePanel">
    <h1><?= $episode ? 'Edit Episode' : 'Create Episode' ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="" method="post">
        <label>
            Episode Title: <br>
            <input type="text" name="title" value="<?= htmlspecialchars($defaultTitle) ?>" size="60">
        </label><br><br>

        <label>
            Description: <br>
            <textarea id="shortcodey" name="description" rows="16" cols="140"><?= htmlspecialchars($defaultDesc) ?></textarea>
            <div id="autocomplete"></div>
            </label><br><br>

        <label>
            Frames Description: <br>
            <textarea name="episode_frames" rows="16" cols="90"><?= htmlspecialchars($episode_frames) ?></textarea>
        </label><br><br>
        <label>
            Livestream ID (optional): <br>
            <input type="number" name="livestream_id" value="<?= $defaultLivestreamId ?: '' ?>" min="0">
        </label><br><br>

        <?php if ($streamCode): ?>
            <em>YT Link:</em> <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($streamCode) ?>" target="_blank">Watch</a><br><br>
        <?php endif; ?>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
                <?php if(!empty($episode->photos)):foreach ($episode->photos ?? [''] as $photo): ?>
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


        <button type="submit"><?= $episode ? 'Update Episode' : 'Create Episode' ?></button>
        <?php if ($episode): ?>
            <a href="/admin/episodes/" style="margin-left: 10px;">Cancel</a>
        <?php endif; ?>

    </form>
</div>
<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>
