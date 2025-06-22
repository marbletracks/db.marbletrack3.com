<div class="PagePanel">
    <h1>All Photos in Database</h1>
    <ul>
        <?php foreach ($photos as $photo): ?>
            <li>
                <strong>ID <?= htmlspecialchars($photo->photo_id) ?></strong><br>
                <img src="<?= htmlspecialchars($photo->getUrl()) ?>" alt="Photo <?= $photo->photo_id ?>"
                    style="max-height:100px;"><br>
                <a href="<?= htmlspecialchars($photo->getUrl()) ?>"
                    target="_blank"><?= htmlspecialchars($photo->getUrl()) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
