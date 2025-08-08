<?php
// File: /templates/admin/parts/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Parts</h1>

    <p><a href="/admin/parts/part.php">Create New Part</a></p>

    <div style="margin-bottom: 20px;">
        <!-- Quick Filter Buttons -->
        <div style="margin-bottom: 15px;">
            <strong>Quick Filter:</strong>
            <a href="/admin/parts/?status=needs_work<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 10px; text-decoration: none; border-radius: 4px; <?= $status === 'needs_work' ? 'background: #dc3545; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #dc3545;' ?>">
                🚨 Needs Work
            </a>
            <a href="/admin/parts/?status=complete<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $status === 'complete' ? 'background: #28a745; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #28a745;' ?>">
                ✅ Complete
            </a>
            <a href="/admin/parts/?<?= !empty($filter) ? 'filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $status === 'all' ? 'background: #6c757d; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;' ?>">
                📋 All Parts
            </a>
        </div>

        <!-- Text Search -->
        <form method="get" action="/admin/parts/" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <label for="filter">Search:</label>
            <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>"
                   placeholder="Search by alias or name..." style="padding: 5px; width: 250px;">
            <button type="submit" style="padding: 5px 10px;">Filter</button>
            <?php if (!empty($filter)): ?>
                <a href="/admin/parts/?status=<?= urlencode($status) ?>" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear Search</a>
            <?php endif; ?>
        </form>

        <!-- Status Summary -->
        <?php if (!empty($filter) || $status !== 'all'): ?>
            <p style="margin-top: 10px; font-style: italic;">
                <?php if (!empty($filter)): ?>
                    Searching: <strong><?= htmlspecialchars($filter) ?></strong> |
                <?php endif; ?>
                Filter: <strong><?= $status === 'needs_work' ? 'Needs Work' : ($status === 'complete' ? 'Complete' : 'All Parts') ?></strong> |
                <strong><?= count($parts) ?></strong> part<?= count($parts) !== 1 ? 's' : '' ?> found
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
                        <?php
                        $desc = trim($part->description);
                        $needsWork = $desc === '' || $desc === trim($part->name);
                        $isLong = strlen($desc) > 150;
                        ?>

                        <?php if ($needsWork): ?>
                            <em style="color: #dc3545;">[Needs description]</em><br>
                        <?php elseif ($isLong): ?>
                            <div class="description-container" data-part-id="<?= $part->part_id ?>">
                                <div class="description-short">
                                    <?= nl2br(htmlspecialchars(substr($desc, 0, 150))) ?>...
                                    <a href="#" onclick="toggleDescription(<?= $part->part_id ?>); return false;" style="color: #007bff; text-decoration: none; font-weight: bold;">Show More</a>
                                </div>
                                <div class="description-full" style="display: none;">
                                    <?= nl2br(htmlspecialchars($desc)) ?>
                                    <a href="#" onclick="toggleDescription(<?= $part->part_id ?>); return false;" style="color: #007bff; text-decoration: none; font-weight: bold;">Show Less</a>
                                </div>
                            </div><br>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($desc)) ?><br>
                        <?php endif; ?>
                    <?php else: ?>
                        <em style="color: #dc3545;">[Needs description]</em><br>
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

<script>
function toggleDescription(partId) {
    const container = document.querySelector(`[data-part-id="${partId}"]`);
    const shortDiv = container.querySelector('.description-short');
    const fullDiv = container.querySelector('.description-full');

    if (shortDiv.style.display === 'none') {
        // Currently showing full, switch to short
        shortDiv.style.display = 'block';
        fullDiv.style.display = 'none';
    } else {
        // Currently showing short, switch to full
        shortDiv.style.display = 'none';
        fullDiv.style.display = 'block';
    }
}
</script>
