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
            <legend><strong>Entity Type</strong></legend>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="entity_type" value="marble"
                       <?= ($track->entity_type ?? 'marble') === 'marble' ? 'checked' : '' ?>>
                <span style="color: #dc3545;"><strong>🔴 Marble Track</strong></span> - Gravity-fed marble transport
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="entity_type" value="worker"
                       <?= ($track->entity_type ?? 'marble') === 'worker' ? 'checked' : '' ?>>
                <span style="color: #17a2b8;"><strong>👷 Worker Track</strong></span> - Worker-navigated transport (bridges, ramps)
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="entity_type" value="mixed"
                       <?= ($track->entity_type ?? 'marble') === 'mixed' ? 'checked' : '' ?>>
                <span style="color: #28a745;"><strong>🔄 Mixed Track</strong></span> - Both marbles and workers can use
            </label>
        </fieldset>

        <fieldset id="marble-sizes-fieldset" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc;">
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
                        <div style="margin-bottom: 8px; display: flex; align-items: center;" data-connection="upstream" data-from-track="<?= $up->track_id ?>" data-to-track="<?= $track->track_id ?>">
                            <a href="/admin/tracks/track.php?id=<?= $up->track_id ?>"><?= htmlspecialchars($up->track_name) ?></a>
                            <span style="color: #6c757d; margin-left: 10px;">→ (<?= htmlspecialchars($up->getMarbleSizesDisplay()) ?>)</span>
                            <button type="button" class="delete-connection-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Delete this connection">
                                ✕
                            </button>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <!-- Add Upstream Connection Form -->
            <div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 5px;">
                <strong>Add Upstream Track Connection</strong>
                <div style="margin-top: 10px;">
                    <select id="upstream-track-select" style="margin-right: 10px; padding: 5px;">
                        <option value="">Select a track that feeds into this track...</option>
                        <?php foreach ($available_tracks as $avail): ?>
                            <option value="<?= $avail->track_id ?>"><?= htmlspecialchars($avail->track_name) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div style="margin-top: 10px;">
                        <strong>Marble sizes:</strong>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="small" class="upstream-marble-size"> Small
                        </label>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="medium" class="upstream-marble-size"> Medium
                        </label>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="large" class="upstream-marble-size"> Large
                        </label>
                    </div>

                    <button type="button" id="add-upstream-btn" style="margin-top: 10px; padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        Add Upstream Connection
                    </button>
                </div>
            </div>

            <?php if (!empty($downstream)): ?>
                <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;">
                    <legend><strong>Downstream Tracks (this track feeds into)</strong></legend>
                    <?php foreach ($downstream as $down): ?>
                        <div style="margin-bottom: 8px; display: flex; align-items: center;" data-connection="downstream" data-from-track="<?= $track->track_id ?>" data-to-track="<?= $down->track_id ?>">
                            <span style="color: #6c757d;">(<?= htmlspecialchars($down->getMarbleSizesDisplay()) ?>) →</span>
                            <a href="/admin/tracks/track.php?id=<?= $down->track_id ?>" style="margin-left: 5px;"><?= htmlspecialchars($down->track_name) ?></a>
                            <button type="button" class="delete-connection-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Delete this connection">
                                ✕
                            </button>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <!-- Add Downstream Connection Form -->
            <div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 5px;">
                <strong>Add Downstream Track Connection</strong>
                <div style="margin-top: 10px;">
                    <select id="downstream-track-select" style="margin-right: 10px; padding: 5px;">
                        <option value="">Select a track this track feeds into...</option>
                        <?php foreach ($available_tracks as $avail): ?>
                            <option value="<?= $avail->track_id ?>"><?= htmlspecialchars($avail->track_name) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div style="margin-top: 10px;">
                        <strong>Marble sizes:</strong>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="small" class="downstream-marble-size"> Small
                        </label>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="medium" class="downstream-marble-size"> Medium
                        </label>
                        <label style="margin-left: 10px;">
                            <input type="checkbox" value="large" class="downstream-marble-size"> Large
                        </label>
                    </div>

                    <button type="button" id="add-downstream-btn" style="margin-top: 10px; padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        Add Downstream Connection
                    </button>
                </div>
            </div>

            <?php if (!empty($parts)): ?>
                <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;">
                    <legend><strong>Component Parts (<?= count($parts) ?>)</strong></legend>
                    <?php foreach ($parts as $part): ?>
                        <div style="margin-bottom: 8px; display: flex; align-items: center;" data-track-part data-track-id="<?= $track->track_id ?>" data-part-id="<?= $part->part_id ?>">
                            <span style="color: #6c757d; font-size: 0.9em;">[<?= htmlspecialchars($part->role_in_track ?? 'main') ?>]</span>
                            <a href="/admin/parts/part.php?id=<?= $part->part_id ?>" style="margin-left: 5px;"><?= htmlspecialchars($part->name ?: $part->part_alias) ?></a>
                            <?php if ($part->is_exclusive_to_track ?? false): ?>
                                <span style="margin-left: 8px; color: #dc3545; font-size: 14px; cursor: pointer;" class="exclusive-toggle-btn" title="This part is exclusive to this track (click to make it reusable)" data-exclusive="true">🔒</span>
                            <?php else: ?>
                                <span style="margin-left: 8px; color: #28a745; font-size: 14px; cursor: pointer;" class="exclusive-toggle-btn" title="This part can be used in other tracks (click to make it exclusive)" data-exclusive="false">🌐</span>
                            <?php endif; ?>
                            <button type="button" class="delete-part-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Remove this part from track">
                                ✕
                            </button>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <!-- Add Component Part Form -->
            <div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 5px;">
                <strong>Add Component Part</strong>
                <div style="margin-top: 10px;">
                    <select id="part-select" style="margin-right: 10px; padding: 5px; min-width: 200px;">
                        <option value="">Select a part to add to this track...</option>
                        <?php foreach ($available_parts as $avail_part): ?>
                            <option value="<?= $avail_part->part_id ?>"><?= htmlspecialchars($avail_part->name ?: $avail_part->part_alias) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="part-role-select" style="margin-right: 10px; padding: 5px;">
                        <option value="main">Main</option>
                        <option value="rail">Rail</option>
                        <option value="support">Support</option>
                        <option value="connector">Connector</option>
                        <option value="guide">Guide</option>
                    </select>

                    <button type="button" id="add-part-btn" style="padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        Add Part
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit"><?= $track ? 'Update Track' : 'Create Track' ?></button>
        <a href="/admin/tracks/" style="margin-left: 20px;">Cancel</a>
    </form>
</div>

<?php if ($track): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete connection buttons
    document.querySelectorAll('.delete-connection-btn').forEach(button => {
        button.addEventListener('click', function() {
            const connectionDiv = this.closest('[data-connection]');
            const fromTrackId = parseInt(connectionDiv.dataset.fromTrack);
            const toTrackId = parseInt(connectionDiv.dataset.toTrack);
            const connectionType = connectionDiv.dataset.connection;

            if (!confirm('Are you sure you want to delete this track connection?')) {
                return;
            }

            // Disable button during request
            this.disabled = true;
            this.textContent = '...';

            fetch('/admin/tracks/delete_connection.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    from_track_id: fromTrackId,
                    to_track_id: toTrackId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the connection div from the page
                    connectionDiv.style.transition = 'opacity 0.3s';
                    connectionDiv.style.opacity = '0';
                    setTimeout(() => {
                        connectionDiv.remove();

                        // Check if fieldset is now empty and hide it
                        const fieldset = connectionDiv.closest('fieldset');
                        const remainingConnections = fieldset.querySelectorAll('[data-connection]');
                        if (remainingConnections.length === 0) {
                            fieldset.style.display = 'none';
                        }
                    }, 300);
                } else {
                    alert('Error deleting connection: ' + (data.error || 'Unknown error'));
                    // Re-enable button
                    this.disabled = false;
                    this.textContent = '✕';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting connection: ' + error.message);
                // Re-enable button
                this.disabled = false;
                this.textContent = '✕';
            });
        });
    });

    // Handle exclusive part toggle buttons
    document.querySelectorAll('.exclusive-toggle-btn').forEach(button => {
        button.addEventListener('click', function() {
            const partDiv = this.closest('[data-track-part]');
            const trackId = parseInt(partDiv.dataset.trackId);
            const partId = parseInt(partDiv.dataset.partId);
            const currentlyExclusive = this.dataset.exclusive === 'true';
            const newExclusive = !currentlyExclusive;

            // Disable button during request
            this.style.opacity = '0.5';
            this.style.pointerEvents = 'none';

            fetch('/admin/tracks/toggle_part_exclusivity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    track_id: trackId,
                    part_id: partId,
                    is_exclusive: newExclusive
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    if (data.is_exclusive) {
                        this.textContent = '🔒';
                        this.style.color = '#dc3545';
                        this.title = 'This part is exclusive to this track (click to make it reusable)';
                        this.dataset.exclusive = 'true';
                    } else {
                        this.textContent = '🌐';
                        this.style.color = '#28a745';
                        this.title = 'This part can be used in other tracks (click to make it exclusive)';
                        this.dataset.exclusive = 'false';
                    }
                } else {
                    alert('Error toggling exclusivity: ' + (data.error || 'Unknown error'));
                }

                // Re-enable button
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error toggling exclusivity: ' + error.message);
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            });
        });
    });

    // Handle delete part buttons
    document.querySelectorAll('.delete-part-btn').forEach(button => {
        button.addEventListener('click', function() {
            const partDiv = this.closest('[data-track-part]');
            const trackId = parseInt(partDiv.dataset.trackId);
            const partId = parseInt(partDiv.dataset.partId);

            if (!confirm('Are you sure you want to remove this part from the track?')) {
                return;
            }

            // Disable button during request
            this.disabled = true;
            this.textContent = '...';

            fetch('/admin/tracks/delete_part.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    track_id: trackId,
                    part_id: partId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the part div from the page
                    partDiv.style.transition = 'opacity 0.3s';
                    partDiv.style.opacity = '0';
                    setTimeout(() => {
                        partDiv.remove();

                        // Update parts count in legend and check if fieldset is now empty
                        const fieldset = partDiv.closest('fieldset');
                        const remainingParts = fieldset.querySelectorAll('[data-track-part]');
                        const legend = fieldset.querySelector('legend strong');

                        if (remainingParts.length === 0) {
                            fieldset.style.display = 'none';
                        } else {
                            // Update count in legend
                            legend.textContent = `Component Parts (${remainingParts.length})`;
                        }
                    }, 300);
                } else {
                    alert('Error removing part: ' + (data.error || 'Unknown error'));
                    // Re-enable button
                    this.disabled = false;
                    this.textContent = '✕';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing part: ' + error.message);
                // Re-enable button
                this.disabled = false;
                this.textContent = '✕';
            });
        });
    });

    // Handle add part button
    document.getElementById('add-part-btn').addEventListener('click', function() {
        const partSelect = document.getElementById('part-select');
        const roleSelect = document.getElementById('part-role-select');
        const selectedPartId = parseInt(partSelect.value);
        const selectedRole = roleSelect.value;

        if (!selectedPartId) {
            alert('Please select a part to add');
            return;
        }

        // Disable button during request
        this.disabled = true;
        this.textContent = 'Adding...';

        fetch('/admin/tracks/add_part.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                track_id: <?= $track->track_id ?>,
                part_id: selectedPartId,
                part_role: selectedRole
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new part to component parts list
                addPartToList(data.part);

                // Remove part from dropdown
                const optionToRemove = partSelect.querySelector(`option[value="${selectedPartId}"]`);
                if (optionToRemove) {
                    optionToRemove.remove();
                }

                // Reset form
                partSelect.value = '';
                roleSelect.value = 'main';
            } else {
                alert('Error adding part: ' + (data.error || 'Unknown error'));
            }

            // Re-enable button
            this.disabled = false;
            this.textContent = 'Add Part';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding part: ' + error.message);
            this.disabled = false;
            this.textContent = 'Add Part';
        });
    });

    // Helper function to add part to DOM
    function addPartToList(part) {
        // Find or create component parts fieldset
        let fieldset = null;
        const legends = document.querySelectorAll('legend');
        for (let legend of legends) {
            if (legend.textContent.includes('Component Parts')) {
                fieldset = legend.closest('fieldset');
                break;
            }
        }

        if (!fieldset) {
            // Create fieldset if it doesn't exist
            const container = document.getElementById('part-select').closest('div');
            fieldset = document.createElement('fieldset');
            fieldset.style.cssText = 'margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;';
            fieldset.innerHTML = '<legend><strong>Component Parts (0)</strong></legend>';
            container.parentNode.insertBefore(fieldset, container);
        } else {
            fieldset.style.display = 'block';
        }

        // Create part div
        const partDiv = document.createElement('div');
        partDiv.style.cssText = 'margin-bottom: 8px; display: flex; align-items: center;';
        partDiv.setAttribute('data-track-part', '');
        partDiv.setAttribute('data-track-id', '<?= $track->track_id ?>');
        partDiv.setAttribute('data-part-id', part.id);

        partDiv.innerHTML = `
            <span style="color: #6c757d; font-size: 0.9em;">[${part.role}]</span>
            <a href="/admin/parts/part.php?id=${part.id}" style="margin-left: 5px;">${part.name}</a>
            <span style="margin-left: 8px; color: #28a745; font-size: 14px; cursor: pointer;" class="exclusive-toggle-btn" title="This part can be used in other tracks (click to make it exclusive)" data-exclusive="false">🌐</span>
            <button type="button" class="delete-part-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Remove this part from track">
                ✕
            </button>
        `;

        // Add exclusive toggle functionality to new button
        partDiv.querySelector('.exclusive-toggle-btn').addEventListener('click', function() {
            const trackId = parseInt(partDiv.dataset.trackId);
            const partId = parseInt(partDiv.dataset.partId);
            const currentlyExclusive = this.dataset.exclusive === 'true';
            const newExclusive = !currentlyExclusive;

            this.style.opacity = '0.5';
            this.style.pointerEvents = 'none';

            fetch('/admin/tracks/toggle_part_exclusivity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    track_id: trackId,
                    part_id: partId,
                    is_exclusive: newExclusive
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_exclusive) {
                        this.textContent = '🔒';
                        this.style.color = '#dc3545';
                        this.title = 'This part is exclusive to this track (click to make it reusable)';
                        this.dataset.exclusive = 'true';
                    } else {
                        this.textContent = '🌐';
                        this.style.color = '#28a745';
                        this.title = 'This part can be used in other tracks (click to make it exclusive)';
                        this.dataset.exclusive = 'false';
                    }
                } else {
                    alert('Error toggling exclusivity: ' + (data.error || 'Unknown error'));
                }
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error toggling exclusivity: ' + error.message);
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            });
        });

        // Add delete functionality to new button
        partDiv.querySelector('.delete-part-btn').addEventListener('click', function() {
            const trackId = parseInt(partDiv.dataset.trackId);
            const partId = parseInt(partDiv.dataset.partId);

            if (!confirm('Are you sure you want to remove this part from the track?')) {
                return;
            }

            this.disabled = true;
            this.textContent = '...';

            fetch('/admin/tracks/delete_part.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    track_id: trackId,
                    part_id: partId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    partDiv.style.transition = 'opacity 0.3s';
                    partDiv.style.opacity = '0';
                    setTimeout(() => {
                        // Add part back to dropdown
                        const partSelect = document.getElementById('part-select');
                        const option = document.createElement('option');
                        option.value = partId;
                        option.textContent = part.name;
                        partSelect.appendChild(option);

                        partDiv.remove();

                        // Update parts count
                        const remainingParts = fieldset.querySelectorAll('[data-track-part]');
                        const legend = fieldset.querySelector('legend strong');

                        if (remainingParts.length === 0) {
                            fieldset.style.display = 'none';
                        } else {
                            legend.textContent = `Component Parts (${remainingParts.length})`;
                        }
                    }, 300);
                } else {
                    alert('Error removing part: ' + (data.error || 'Unknown error'));
                    this.disabled = false;
                    this.textContent = '✕';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing part: ' + error.message);
                this.disabled = false;
                this.textContent = '✕';
            });
        });

        fieldset.appendChild(partDiv);

        // Update count in legend
        const remainingParts = fieldset.querySelectorAll('[data-track-part]');
        const legend = fieldset.querySelector('legend strong');
        legend.textContent = `Component Parts (${remainingParts.length})`;

        // Animate in
        partDiv.style.opacity = '0';
        setTimeout(() => {
            partDiv.style.transition = 'opacity 0.3s';
            partDiv.style.opacity = '1';
        }, 10);
    }

    // Handle add upstream connection
    document.getElementById('add-upstream-btn').addEventListener('click', function() {
        const trackSelect = document.getElementById('upstream-track-select');
        const selectedTrackId = parseInt(trackSelect.value);
        const marbleSizeCheckboxes = document.querySelectorAll('.upstream-marble-size:checked');

        if (!selectedTrackId) {
            alert('Please select a track to connect');
            return;
        }

        if (marbleSizeCheckboxes.length === 0) {
            alert('Please select at least one marble size');
            return;
        }

        const marbleSizes = Array.from(marbleSizeCheckboxes).map(cb => cb.value);

        // Disable button during request
        this.disabled = true;
        this.textContent = 'Adding...';

        fetch('/admin/tracks/add_connection.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                from_track_id: selectedTrackId,
                to_track_id: <?= $track->track_id ?>,
                marble_sizes: marbleSizes,
                connection_type: 'direct'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new connection to upstream list
                addConnectionToList('upstream', data.from_track, data.to_track, data.marble_sizes_display);

                // Reset form
                trackSelect.value = '';
                marbleSizeCheckboxes.forEach(cb => cb.checked = false);
            } else {
                alert('Error adding connection: ' + (data.error || 'Unknown error'));
            }

            // Re-enable button
            this.disabled = false;
            this.textContent = 'Add Upstream Connection';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding connection: ' + error.message);
            this.disabled = false;
            this.textContent = 'Add Upstream Connection';
        });
    });

    // Handle add downstream connection
    document.getElementById('add-downstream-btn').addEventListener('click', function() {
        const trackSelect = document.getElementById('downstream-track-select');
        const selectedTrackId = parseInt(trackSelect.value);
        const marbleSizeCheckboxes = document.querySelectorAll('.downstream-marble-size:checked');

        if (!selectedTrackId) {
            alert('Please select a track to connect');
            return;
        }

        if (marbleSizeCheckboxes.length === 0) {
            alert('Please select at least one marble size');
            return;
        }

        const marbleSizes = Array.from(marbleSizeCheckboxes).map(cb => cb.value);

        // Disable button during request
        this.disabled = true;
        this.textContent = 'Adding...';

        fetch('/admin/tracks/add_connection.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                from_track_id: <?= $track->track_id ?>,
                to_track_id: selectedTrackId,
                marble_sizes: marbleSizes,
                connection_type: 'direct'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new connection to downstream list
                addConnectionToList('downstream', data.from_track, data.to_track, data.marble_sizes_display);

                // Reset form
                trackSelect.value = '';
                marbleSizeCheckboxes.forEach(cb => cb.checked = false);
            } else {
                alert('Error adding connection: ' + (data.error || 'Unknown error'));
            }

            // Re-enable button
            this.disabled = false;
            this.textContent = 'Add Downstream Connection';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding connection: ' + error.message);
            this.disabled = false;
            this.textContent = 'Add Downstream Connection';
        });
    });

    // Helper function to add connection to DOM
    function addConnectionToList(type, fromTrack, toTrack, marbleSizesDisplay) {
        const fieldsetLegend = type === 'upstream' ? 'Upstream Tracks (feed into this track)' : 'Downstream Tracks (this track feeds into)';
        // Find existing fieldset by looking for legend text
        let fieldset = null;
        const legends = document.querySelectorAll('legend');
        for (let legend of legends) {
            if (legend.textContent.includes(fieldsetLegend)) {
                fieldset = legend.closest('fieldset');
                break;
            }
        }

        if (!fieldset) {
            // Create fieldset if it doesn't exist
            const container = type === 'upstream' ?
                document.getElementById('upstream-track-select').closest('div') :
                document.getElementById('downstream-track-select').closest('div');

            fieldset = document.createElement('fieldset');
            fieldset.style.cssText = 'margin-bottom: 20px; padding: 15px; border: 1px solid #e9ecef;';
            fieldset.innerHTML = `<legend><strong>${fieldsetLegend}</strong></legend>`;
            container.parentNode.insertBefore(fieldset, container);
        } else {
            fieldset.style.display = 'block';
        }

        // Create connection div
        const connectionDiv = document.createElement('div');
        connectionDiv.style.cssText = 'margin-bottom: 8px; display: flex; align-items: center;';
        connectionDiv.dataset.connection = type;
        connectionDiv.dataset.fromTrack = fromTrack.id;
        connectionDiv.dataset.toTrack = toTrack.id;

        if (type === 'upstream') {
            connectionDiv.innerHTML = `
                <a href="/admin/tracks/track.php?id=${fromTrack.id}">${fromTrack.name}</a>
                <span style="color: #6c757d; margin-left: 10px;">→ (${marbleSizesDisplay})</span>
                <button type="button" class="delete-connection-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Delete this connection">
                    ✕
                </button>
            `;
        } else {
            connectionDiv.innerHTML = `
                <span style="color: #6c757d;">(${marbleSizesDisplay}) →</span>
                <a href="/admin/tracks/track.php?id=${toTrack.id}" style="margin-left: 5px;">${toTrack.name}</a>
                <button type="button" class="delete-connection-btn" style="margin-left: 10px; padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;" title="Delete this connection">
                    ✕
                </button>
            `;
        }

        // Add delete functionality to new button
        connectionDiv.querySelector('.delete-connection-btn').addEventListener('click', function() {
            // Reuse existing delete logic
            const fromTrackId = parseInt(connectionDiv.dataset.fromTrack);
            const toTrackId = parseInt(connectionDiv.dataset.toTrack);

            if (!confirm('Are you sure you want to delete this track connection?')) {
                return;
            }

            this.disabled = true;
            this.textContent = '...';

            fetch('/admin/tracks/delete_connection.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    from_track_id: fromTrackId,
                    to_track_id: toTrackId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    connectionDiv.style.transition = 'opacity 0.3s';
                    connectionDiv.style.opacity = '0';
                    setTimeout(() => {
                        connectionDiv.remove();

                        const remainingConnections = fieldset.querySelectorAll('[data-connection]');
                        if (remainingConnections.length === 0) {
                            fieldset.style.display = 'none';
                        }
                    }, 300);
                } else {
                    alert('Error deleting connection: ' + (data.error || 'Unknown error'));
                    this.disabled = false;
                    this.textContent = '✕';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting connection: ' + error.message);
                this.disabled = false;
                this.textContent = '✕';
            });
        });

        fieldset.appendChild(connectionDiv);

        // Animate in
        connectionDiv.style.opacity = '0';
        setTimeout(() => {
            connectionDiv.style.transition = 'opacity 0.3s';
            connectionDiv.style.opacity = '1';
        }, 10);
    }
});

// Handle entity type changes - show/hide marble sizes fieldset
document.querySelectorAll('input[name="entity_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const marbleSizesFieldset = document.getElementById('marble-sizes-fieldset');
        const descriptionTextarea = document.querySelector('textarea[name="track_description"]');

        if (this.value === 'worker') {
            marbleSizesFieldset.style.display = 'none';
            if (!descriptionTextarea.value) {
                descriptionTextarea.placeholder = 'What does this worker track do? How do workers navigate it?';
            }
        } else {
            marbleSizesFieldset.style.display = 'block';
            if (!descriptionTextarea.value) {
                if (this.value === 'mixed') {
                    descriptionTextarea.placeholder = 'What does this track do? How does it transport marbles and workers?';
                } else {
                    descriptionTextarea.placeholder = 'What does this track do? How does it transport or route marbles?';
                }
            }
        }
    });
});

// Set initial state
const selectedEntityType = document.querySelector('input[name="entity_type"]:checked');
if (selectedEntityType) {
    selectedEntityType.dispatchEvent(new Event('change'));
}

</script>
<?php endif; ?>
