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
            <textarea id="shortcodey" name="part_description" rows="15" cols="100"><?= htmlspecialchars($part->description ?? '') ?></textarea>
            <div id="autocomplete"></div>
        </label><br><br>

        <?php if (!empty($part_moments)): ?>
        <h2>Associated Moments</h2>
        <ul>
            <?php foreach ($part_moments as $moment): ?>
                <li>
                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>">
                        <?= htmlspecialchars($moment->notes) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <label>
            Moments:<br>
            <select name="moment_ids[]" multiple size="10">
                <?php
                $part_moment_ids = array_map(fn($m) => $m->moment_id, $part_moments);
                foreach ($all_moments as $moment): ?>
                    <option value="<?= $moment->moment_id ?>" <?= in_array($moment->moment_id, $part_moment_ids) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($moment->notes) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php if (!empty($part->photos)):foreach ($part->photos ?? [''] as $photo): ?>
    <img src="<?= $photo->getThumbnailUrl() ?>" alt="Image preview"><br>
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
