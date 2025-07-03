<div class="PagePanel">

    Name:<br>
    <?= htmlspecialchars($worker->name ?? '') ?>

    Alias:<br>
    <?= htmlspecialchars($worker->worker_alias ?? '') ?>

    Description:<br>
    <?= htmlspecialchars($worker->description ?? '') ?>
    <br>
    <div id="image-url-fields">
        <?php if (!empty($worker->photos)):
            foreach ($worker->photos ?? [''] as $photo): ?>
                <a href="<?= $photo->getUrl(); ?>">
                    <img src="<?= $photo->getThumbnailUrl() ?>" alt="Image preview"><br>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- add empty row so we always have space -->
    </div>
</div>