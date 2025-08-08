<?php
// File: /templates/admin/tracks/index.tpl.php
?>
<div class="PagePanel">
    <h1>All Tracks</h1>

    <p><a href="/admin/tracks/track.php">Create New Track</a></p>

    <div style="margin-bottom: 20px;">
        <p><em>Tracks are logical groupings of Parts that transport marbles via gravity. Each Track can transport specific marble sizes and connects to other Tracks to form the complete marble flow network.</em></p>
    </div>

    <?php
    $landing_zones = array_filter($tracks, fn($track) => $track->isLandingZone());
    $other_tracks = array_filter($tracks, fn($track) => !$track->isLandingZone());
    ?>

    <?php if (!empty($landing_zones)): ?>
        <h2>Landing Zones (Terminal Destinations)</h2>
        <ul style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($landing_zones as $track): ?>
                <li style="display: flex; align-items: flex-start; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-left: 4px solid #28a745;">
                    <div>
                        <strong><?= htmlspecialchars($track->track_name) ?></strong>
                        <span style="color: #6c757d; margin-left: 10px;">(<?= htmlspecialchars($track->getMarbleSizesDisplay()) ?>)</span><br>

                        <?php if (!empty($track->track_description)): ?>
                            <?= nl2br(htmlspecialchars($track->track_description)) ?><br>
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

    <?php if (!empty($other_tracks)): ?>
        <h2>Transport & Splitter Tracks</h2>
        <ul style="list-style: none; padding: 0;">
            <?php foreach ($other_tracks as $track): ?>
                <li style="display: flex; align-items: flex-start; margin-bottom: 15px; padding: 10px; background: <?= $track->isSplitter() ? '#fff3cd' : '#d1ecf1' ?>; border-left: 4px solid <?= $track->isSplitter() ? '#ffc107' : '#17a2b8' ?>;">
                    <div>
                        <strong><?= htmlspecialchars($track->track_name) ?></strong>
                        <span style="color: #6c757d; margin-left: 10px;">(<?= htmlspecialchars($track->getMarbleSizesDisplay()) ?>)</span><br>

                        <?php if (!empty($track->track_description)): ?>
                            <?= nl2br(htmlspecialchars($track->track_description)) ?><br>
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

    <?php if (empty($tracks)): ?>
        <p>No tracks found.</p>
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
