<div class="PagePanel">
    <h1><?= $track ? 'Edit ' . htmlspecialchars($track->track_name) : 'Create Track' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Alias:<br>
            <input type="text" id="track_alias" name="track_alias"
                   value="<?= htmlspecialchars($track->track_alias ?? '') ?>" required>
            <small>URL-safe identifier (e.g., 'outer_spiral', 'triple_splitter_system')</small>
        </label><br><br>

        <label>
            Name:<br>
            <input type="text" size="60" id="track_name" name="track_name"
                   value="<?= htmlspecialchars($track->track_name ?? '') ?>" required>
            <small>Human-readable name (e.g., 'Outer Spiral', 'Triple Splitter System')</small>
        </label><br><br>

        <label>
            Description:<br>
            <textarea name="track_description" rows="8" cols="80"><?= htmlspecialchars($track->track_description ?? '') ?></textarea>
            <small>What does this track do? How does it transport or route marbles?</small>
        </label><br><br>

        <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc;">
            <legend><strong>Marble Sizes Accepted</strong></legend>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="marble_sizes[]" value="small"
                       <?= in_array('small', $track->marble_sizes_accepted ?? []) ? 'checked' : '' ?>>
                Small marbles
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="marble_sizes[]" value="medium"
                       <?= in_array('medium', $track->marble_sizes_accepted ?? []) ? 'checked' : '' ?>>
                Medium marbles
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="marble_sizes[]" value="large"
                       <?= in_array('large', $track->marble_sizes_accepted ?? []) ? 'checked' : '' ?>>
                Large marbles
            </label>
        </fieldset>

        <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc;">
            <legend><strong>Track Types</strong> (can select multiple)</legend>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="is_transport" value="1"
                       <?= ($track->is_transport ?? false) ? 'checked' : '' ?>>
                <span style="color: #17a2b8;"><strong>Transport Track</strong></span> - Moves marbles from one location to another
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="is_splitter" value="1"
                       <?= ($track->is_splitter ?? false) ? 'checked' : '' ?>>
                <span style="color: #ffc107;"><strong>Splitter Track</strong></span> - Divides marble flow by size or direction
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="is_landing_zone" value="1"
                       <?= ($track->is_landing_zone ?? false) ? 'checked' : '' ?>>
                <span style="color: #28a745;"><strong>Landing Zone</strong></span> - Terminal destination where marbles end up
            </label>
        </fieldset>

        <?php if ($track): ?>
            <!-- Show connected tracks if editing -->
            <?php if (!empty($upstream)): ?>
                <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;">
                    <legend><strong>Upstream Tracks (feed into this track)</strong></legend>
                    <?php foreach ($upstream as $up): ?>
                        <div style="margin-bottom: 8px;">
                            <a href="/admin/tracks/track.php?id=<?= $up->track_id ?>"><?= htmlspecialchars($up->track_name) ?></a>
                            <span style="color: #6c757d;">→ (<?= htmlspecialchars($up->getMarbleSizesDisplay()) ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <?php if (!empty($downstream)): ?>
                <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;">
                    <legend><strong>Downstream Tracks (this track feeds into)</strong></legend>
                    <?php foreach ($downstream as $down): ?>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #6c757d;">(<?= htmlspecialchars($down->getMarbleSizesDisplay()) ?>) →</span>
                            <a href="/admin/tracks/track.php?id=<?= $down->track_id ?>"><?= htmlspecialchars($down->track_name) ?></a>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <?php if (!empty($parts)): ?>
                <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;">
                    <legend><strong>Component Parts (<?= count($parts) ?>)</strong></legend>
                    <?php foreach ($parts as $part): ?>
                        <div style="margin-bottom: 5px;">
                            <span style="color: #6c757d; font-size: 0.9em;">[<?= htmlspecialchars($part->role_in_track ?? 'main') ?>]</span>
                            <a href="/admin/parts/part.php?id=<?= $part->part_id ?>"><?= htmlspecialchars($part->name ?: $part->part_alias) ?></a>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
        <?php endif; ?>

        <button type="submit"><?= $track ? 'Update Track' : 'Create Track' ?></button>
        <a href="/admin/tracks/" style="margin-left: 20px;">Cancel</a>
    </form>
</div>
