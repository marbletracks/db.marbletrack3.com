<div class="PagePanel">
    <h1>Create Episode for Livestream</h1>
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
        <em>YT Link:</em> <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($streamCode) ?>" target="_blank">Watch</a><br>

        <button type="submit">Create Episode</button>
    </form>
</div>