<?php
// File: /templates/admin/parts/images/index.tpl.php
?>
<div class="PagePanel">
    <h1>Parts - Upload Images</h1>

    <p><a href="/admin/parts/">← Back to All Parts</a></p>

    <div style="margin-bottom: 20px;">
        <form method="get" action="/admin/parts/images/" style="display: flex; align-items: center; gap: 10px;">
            <label for="filter">Filter parts:</label>
            <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>" 
                   placeholder="Search by alias or name..." style="padding: 5px; width: 250px;">
            <button type="submit" style="padding: 5px 10px;">Filter</button>
            <?php if (!empty($filter)): ?>
                <a href="/admin/parts/images/" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear</a>
            <?php endif; ?>
        </form>
        <?php if (!empty($filter)): ?>
            <p style="margin-top: 10px; font-style: italic;">
                Showing results for: <strong><?= htmlspecialchars($filter) ?></strong> 
                (<?= count($parts) ?> part<?= count($parts) !== 1 ? 's' : '' ?> found)
            </p>
        <?php endif; ?>
    </div>

    <ul style="list-style: none; padding: 0;">
        <?php foreach ($parts as $part): ?>
            <li style="display: flex; align-items: center; margin-bottom: 15px; padding: 10px; border: 1px solid #eee; border-radius: 5px;">
                <?php if (!empty($part->photos[0])): ?>
                    <a href="<?= htmlspecialchars($part->photos[0]->getUrl()) ?>" style="margin-right: 15px;">
                        <img src="<?= htmlspecialchars($part->photos[0]->getThumbnailUrl()) ?>" alt="part photo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                    </a>
                <?php else: ?>
                    <div style="width: 60px; height: 60px; background: #f5f5f5; border: 1px dashed #ccc; border-radius: 4px; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;">
                        No photo
                    </div>
                <?php endif; ?>

                <div style="flex-grow: 1;">
                    <strong style="font-size: 1.1em;"><?= htmlspecialchars($part->name) ?></strong><br>
                    <em>Alias:</em> <?= htmlspecialchars($part->part_alias ?? '—') ?>
                </div>

                <div style="margin-left: 20px;">
                    <a href="/admin/parts/images/create.php?part_id=<?= htmlspecialchars($part->part_id) ?>" 
                       style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                        Upload Images
                    </a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($parts)): ?>
        <p>No parts found<?= !empty($filter) ? ' matching your filter' : '' ?>.</p>
    <?php endif; ?>

    <p><a href="/admin/parts/">← Back to All Parts</a></p>
</div>