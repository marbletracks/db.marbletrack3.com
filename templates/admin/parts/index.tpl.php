<?php
// File: /templates/admin/parts/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Parts</h1>

    <p><a href="/admin/parts/part.php">Create New Part</a></p>

    <ul style="list-style: none; padding: 0;">
        <?php foreach ($parts as $part): ?>
            <li style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                <?php if (!empty($part->photos[0])): ?>
                    <a href="<?= htmlspecialchars($part->photos[0]->getUrl()) ?>" style="margin-right: 15px;">
                        <img src="<?= htmlspecialchars($part->photos[0]->getThumbnailUrl()) ?>" alt="part photo">
                    </a>
                <?php endif; ?>

                <div>
                    <strong><?= htmlspecialchars($part->name) ?></strong><br>

                    <?php if (!empty($part->description)): ?>
                        <?= nl2br(htmlspecialchars($part->description)) ?><br>
                    <?php endif; ?>

                    <em>Alias:</em> <a href="<?= $part->frontend_link; ?>"><?= htmlspecialchars($part->part_alias ?? '—') ?></a><br>

                    <?php if (!empty($part->created_at)): ?>
                        <em>Created:</em> <?= htmlspecialchars($part->created_at) ?><br>
                    <?php endif; ?>

                    <a href="/admin/parts/part.php?id=<?= htmlspecialchars($part->part_id) ?>">
                        Edit (id=<?= htmlspecialchars($part->part_id) ?>)
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="/admin/parts/part.php">Create New Part</a></p>
</div>
