<?php
// File: /templates/admin/parts/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Parts</h1>

    <p><a href="/admin/parts/part.php">Create New Part</a></p>

    <div style="margin-bottom: 20px;">
        <form method="get" action="/admin/parts/" style="display: flex; align-items: center; gap: 10px;">
            <label for="filter">Filter parts:</label>
            <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>" 
                   placeholder="Search by alias or name..." style="padding: 5px; width: 250px;">
            <button type="submit" style="padding: 5px 10px;">Filter</button>
            <?php if (!empty($filter)): ?>
                <a href="/admin/parts/" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear</a>
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

    <?php if (empty($parts)): ?>
        <p>No parts found<?= !empty($filter) ? ' matching your filter' : '' ?>.</p>
    <?php endif; ?>

    <p><a href="/admin/parts/part.php">Create New Part</a></p>
</div>
