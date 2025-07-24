<div class="PagePanel">
    <h1><?= $part ? 'Edit Part' : 'Create Part' ?></h1>

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
            <input type="text" id="part_alias" name="part_alias"
                   value="<?= htmlspecialchars($part->part_alias ?? '') ?>">
            <div id="alias-error" style="color:red; margin-top:4px;"></div>
        </label><br><br>

        <label>
            Name:<br>
            <input type="text" size="100" id="part_name" name="part_name" value="<?= htmlspecialchars($part->name ?? '') ?>">
        </label><br><br>

        <label>
            Description:<br>
            <textarea id="shortcodey" name="part_description" class="shortcodey-textarea" rows="15" cols="100"><?= htmlspecialchars($part->description ?? '') ?></textarea>
            <div id="autocomplete"></div>
        </label><br><br>

        <?php if ($part && !empty($part->moments)): ?>
        <h2>Associated Moments</h2>
        <h4>(Oldest on top)</h4>
        <ul id="sortable-moments">
            <?php foreach ($part->moments as $moment): ?>
                <li data-moment-id="<?= $moment->moment_id ?>" draggable="true">
                    <div class="drag-handle">⋮⋮</div>
                    <?php if ($moment->moment_date): ?>
                        (<?= htmlspecialchars($moment->moment_date) ?>)&nbsp;
                    <?php else: // ($moment->moment_date): ?>
                        (<?= "--/--/----" ?>)&nbsp;
                    <?php endif; // ($moment->moment_date): ?>
                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>"><?= htmlspecialchars($moment->notes) ?></a>
                    <?php if ($moment->frame_start || $moment->frame_end): ?>
                        (<a href="/admin/moments/?take_id=<?= $moment->take_id ?>">T:<?= $moment->take_id ?></a>
                         F:<?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                    <?php endif; ?>

                    <label class="is-significant-label">
                        <input type="checkbox" class="is-significant-checkbox" data-moment-id="<?= $moment->moment_id ?>" <?= $moment->is_significant ? 'checked' : '' ?>>
                        IS
                    </label>
                    <button type="button" class="remove-moment">Remove</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <input type="hidden" name="moment_ids" id="moment_ids_hidden">

        <label>
            Add Moment:<br>
            <select id="add-moment-select">
                <option value="">Select a moment to add</option>
                <?php
                $part_moment_ids = $part ? array_map(fn($m) => $m->moment_id, $part->moments) : [];

                foreach ($prioritized_moments as $moment):
                    if (!in_array($moment->moment_id, $part_moment_ids)): ?>
                        <option value="<?= $moment->moment_id ?>"
                                data-notes="<?= htmlspecialchars($moment->notes) ?>"
                                data-frame-start="<?= $moment->frame_start ?>"
                                data-frame-end="<?= $moment->frame_end ?>"
                                data-moment-date="<?= $moment->moment_date ?>">
                            <?= htmlspecialchars($moment->notes) ?>
                            <?php if ($moment->frame_start || $moment->frame_end): ?>
                                (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                            <?php endif; ?>
                            <?php if ($moment->moment_date): ?>
                                (<?= htmlspecialchars($moment->moment_date) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endif;
                endforeach;

                if (!empty($prioritized_moments) && !empty($other_moments)) {
                    echo '<option disabled>--------------------</option>';
                }

                foreach ($other_moments as $moment):
                    if (!in_array($moment->moment_id, $part_moment_ids)): ?>
                        <option value="<?= $moment->moment_id ?>"
                                data-notes="<?= htmlspecialchars($moment->notes) ?>"
                                data-frame-start="<?= $moment->frame_start ?>"
                                data-frame-end="<?= $moment->frame_end ?>"
                                data-moment-date="<?= $moment->moment_date ?>">
                            <?= htmlspecialchars($moment->notes) ?>
                            <?php if ($moment->frame_start || $moment->frame_end): ?>
                                (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                            <?php endif; ?>
                            <?php if ($moment->moment_date): ?>
                                (<?= htmlspecialchars($moment->moment_date) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endif;
                endforeach;
                ?>
            </select>
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php if (!empty($part->photos)):foreach ($part->photos ?? [''] as $photo): ?>
    <a href="<?= $photo->getUrl() ?>"><img src="<?= $photo->getThumbnailUrl() ?>" alt="Image preview"></a><br>
    <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
<?php endforeach; ?>
<?php endif; ?>
                <!-- add empty row so we always have space -->
                <input type="text" size=130 name="image_urls[]" value=""><br>
            </div>
            <button type="button" onclick="addImageUrlField()">Add another</button>
        </label>

        <script>
            function addImageUrlField() {
                const div = document.getElementById('image-url-fields');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'image_urls[]';
                div.appendChild(input);
                div.appendChild(document.createElement('br'));
            }
        </script>

        <button id="save-button" type="submit">Save</button>
    </form>
</div>

<script>
    // Auto-fill alias from part name (lowercase initials)
    document.addEventListener('DOMContentLoaded', function() {
        const nameField = document.getElementById('part_name');
        const aliasField = document.getElementById('part_alias');
        const aliasError = document.getElementById('alias-error');
        const saveButton = document.getElementById('save-button');

        function updateAlias() {
            const value = nameField.value.trim();
            if (!value) {
                aliasField.value = '';
                return;
            }
            const initials = value
                .split(/\s+/)
                .map(word => word.charAt(0).toLowerCase())
                .join('');
            aliasField.value = initials;
        }

        nameField.addEventListener('input', updateAlias);

        function updateAlias() {
            const value = nameField.value.trim();
            if (!value) {
                aliasField.value = '';
                aliasError.style.display = 'none';
                saveButton.disabled = false;
                return;
            }
            const initials = value
                .split(/\s+/)
                .map(word => word.charAt(0).toLowerCase())
                .join('');
            aliasField.value = initials;

            // Check for duplicates via AJAX
            fetch(`/admin/ajax/shortcode_filter.php?q=${encodeURIComponent(initials)}&exact=true`)
                .then(response => response.json())
                .then(data => {
                    // console.log(data);
                    const duplicate = data.some(item => item.alias === initials);
                    if (duplicate) {
                        aliasError.textContent = 'Alias "' + initials + '" is already in use by ' +
                            data.find(item => item.alias === initials).name;
                        aliasError.style.display = 'block';
                        saveButton.disabled = true;
                    } else {
                        aliasError.textContent = '';
                        aliasError.style.display = 'none';
                        saveButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error checking alias:', error);
                    // On error, allow save but log
                    aliasError.textContent = '';
                    aliasError.style.display = 'none';
                    saveButton.disabled = false;
                });
        }
    });
</script>
<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>
<link rel="stylesheet" href="/admin/css/sortable-moments.css">
<script src="/admin/js/sortable-moments.js" defer></script>
<link rel="stylesheet" href="/admin/css/is-significant.css">
<script src="/admin/js/update-significance.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSignificanceUpdater(<?= $part->part_id ?? 'null' ?>, 'part');
});
</script>

