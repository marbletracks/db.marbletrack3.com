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
            <textarea name="description" rows="6" cols="60"><?= htmlspecialchars($defaultDesc) ?></textarea>
        </label><br><br>

        <label>
            Livestream ID (optional): <br>
            <input type="number" name="livestream_id" value="<?= $defaultLivestreamId ?: '' ?>" min="0">
        </label><br><br>

        <?php if ($streamCode): ?>
            <em>YT Link:</em> <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($streamCode) ?>" target="_blank">Watch</a><br><br>
        <?php endif; ?>

        <button type="submit"><?= $episode ? 'Update Episode' : 'Create Episode' ?></button>
        <?php if ($episode): ?>
            <a href="/admin/episodes/" style="margin-left: 10px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>
