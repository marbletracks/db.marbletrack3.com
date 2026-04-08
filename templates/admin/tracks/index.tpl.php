<?php
// File: /templates/admin/tracks/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Tracks</h1>

    <p><a href="/admin/tracks/track.php">Create New Track</a></p>

    <div style="margin-bottom: 20px;">
        <!-- Quick Filter Buttons -->
        <div style="margin-bottom: 15px;">
            <strong>Quick Filter:</strong>
            <a href="/admin/tracks/?type=marble<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 10px; text-decoration: none; border-radius: 4px; <?= $type === 'marble' ? 'background: #dc3545; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #dc3545;' ?>">
                🔴 Marble
            </a>
            <a href="/admin/tracks/?type=worker<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $type === 'worker' ? 'background: #fd7e14; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #fd7e14;' ?>">
                👷 Worker
            </a>
            <a href="/admin/tracks/?type=mixed<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $type === 'mixed' ? 'background: #17a2b8; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #17a2b8;' ?>">
                🔄 Mixed
            </a>
            <a href="/admin/tracks/?type=landing<?= !empty($filter) ? '&filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $type === 'landing' ? 'background: #28a745; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #28a745;' ?>">
                🎯 Landing Zones
            </a>
            <a href="/admin/tracks/?<?= !empty($filter) ? 'filter=' . urlencode($filter) : '' ?>"
               style="padding: 5px 12px; margin-left: 5px; text-decoration: none; border-radius: 4px; <?= $type === 'all' ? 'background: #6c757d; color: white;' : 'background: #f8f9fa; border: 1px solid #dee2e6; color: #6c757d;' ?>">
                📋 All Tracks
            </a>
        </div>

        <!-- Text Search -->
        <form method="get" action="/admin/tracks/" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <label for="filter">Search:</label>
            <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>"
                   placeholder="Search by alias or name..." style="padding: 5px; width: 250px;">
            <button type="submit" style="padding: 5px 10px;">Filter</button>
            <?php if (!empty($filter)): ?>
                <a href="/admin/tracks/?type=<?= urlencode($type) ?>" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear Search</a>
            <?php endif; ?>
        </form>

        <!-- Status Summary -->
        <?php if (!empty($filter) || $type !== 'all'): ?>
            <p style="margin-top: 10px; font-style: italic;">
                <?php if (!empty($filter)): ?>
                    Searching: <strong><?= htmlspecialchars($filter) ?></strong> |
                <?php endif; ?>
                <?php
                    $typeLabels = [
                        'marble' => '🔴 Marble',
                        'worker' => '👷 Worker',
                        'mixed' => '🔄 Mixed',
                        'landing' => '🎯 Landing Zones',
                        'all' => '📋 All Tracks',
                    ];
                ?>
                Filter: <strong><?= $typeLabels[$type] ?? 'All Tracks' ?></strong> |
                <strong><?= count($tracks) ?></strong> track<?= count($tracks) !== 1 ? 's' : '' ?> found
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($tracks)): ?>
        <p>No tracks found.</p>
    <?php else: ?>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($tracks as $track): ?>
                <?php
                    if ($track->isLandingZone()) {
                        $bg = '#f8f9fa'; $border = '#28a745';
                    } elseif ($track->isSplitter()) {
                        $bg = '#fff3cd'; $border = '#ffc107';
                    } else {
                        $bg = '#d1ecf1'; $border = '#17a2b8';
                    }
                ?>
                <li style="display: flex; align-items: flex-start; margin-bottom: 15px; padding: 10px; background: <?= $bg ?>; border-left: 4px solid <?= $border ?>;">
                    <div>
                        <strong><?= htmlspecialchars($track->track_name) ?></strong>
                        <span style="color: #6c757d; margin-left: 10px;">
                            <?= $track->getEntityTypeEmoji() ?> <?= htmlspecialchars($track->getEntityTypeDescription()) ?>
                            (<?= htmlspecialchars($track->getMarbleSizesDisplay()) ?>)
                        </span><br>

                        <?php if (!empty($track->technical_description)): ?>
                            <?= nl2br(htmlspecialchars($track->technical_description)) ?><br>
                        <?php endif; ?>

                        <em>Alias:</em> <?= htmlspecialchars($track->track_alias) ?> |
                        <em>Type:</em> <?= htmlspecialchars($track->getTypeDescription()) ?><br>

                        <a href="/admin/tracks/track.php?id=<?= htmlspecialchars($track->track_id) ?>">
                            Edit (id=<?= htmlspecialchars($track->track_id) ?>)
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;">
        <h3>Track Color Legend</h3>
        <ul style="margin: 0;">
            <li style="margin-bottom: 5px;"><span style="display: inline-block; width: 20px; height: 15px; background: #28a745; margin-right: 10px;"></span>Landing Zones - Final destinations for marbles</li>
            <li style="margin-bottom: 5px;"><span style="display: inline-block; width: 20px; height: 15px; background: #17a2b8; margin-right: 10px;"></span>Transport Tracks - Move marbles from place to place</li>
            <li><span style="display: inline-block; width: 20px; height: 15px; background: #ffc107; margin-right: 10px;"></span>Splitter Tracks - Divide marble flow by size or direction</li>
        </ul>
    </div>

    <p style="margin-top: 20px;"><a href="/admin/tracks/track.php">Create New Track</a></p>
</div>
