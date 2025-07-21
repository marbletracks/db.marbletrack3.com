<div class="PagePanel">
    <h1><?= $moment ? 'Edit Moment' : 'Create Moment' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Notes:<br>
            <textarea id="shortcodey" name="notes" class="shortcodey-textarea" rows="15" cols="100"><?= htmlspecialchars($moment->notes ?? '') ?></textarea>
            <div id="autocomplete"></div>
            <div style="margin-top: 10px;">
                <strong>Live Preview:</strong>
                <div id="notes-preview" style="padding: 5px; border: 1px solid #ccc; min-height: 50px; background-color: #f9f9f9;"></div>
            </div>

            <div id="perspective-fields" style="margin-top: 20px;">
                <!-- Dynamic fields will be inserted here -->
            </div>
        </label><br><br>

        <label>
            Frame Start:<br>
            <input type="number" id="frame_start" name="frame_start" value="<?= htmlspecialchars((string)($moment->frame_start ?? $default_frame_start)) ?>">
        </label>
        <label>
            Seconds:
            <input type="number" id="frame_start_seconds" style="width: 60px;">
        </label><br><br>

        <label>
            Frame End:<br>
            <input type="number" id="frame_end" name="frame_end" value="<?= htmlspecialchars((string)($moment->frame_end ?? '')) ?>">
        </label>
        <label>
            Seconds:
            <input type="number" id="frame_end_seconds" style="width: 60px;">
        </label><br><br>

        <label>
            Take:<br>
            <select name="take_id">
                <option value="">-- Select a Take --</option>
                <?php foreach ($takes as $take): ?>
<?php
$selected = false;
if($moment && $moment->take_id == $take->take_id) { $selected = true; }  // editing an existing moment
elseif($default_take_id == $take->take_id) { $selected = true; }   // creating new moment for this take_id
else { $selected = false; }
?>
                    <option value="<?= $take->take_id ?>" <?= $selected ? 'selected' : '' ?>>
                        <?= htmlspecialchars($take->take_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>
            Moment Date:<br>
            <input type="date" name="moment_date" value="<?= htmlspecialchars($moment->moment_date ?? '') ?>">
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
<?php foreach ($moment->photos as $photo): ?>
    <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"><br>
                <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
<?php endforeach; ?>
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

        <button type="submit">Save</button>
    </form>
</div>
<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notesTextarea = document.getElementById('shortcodey');
    const previewDiv = document.getElementById('notes-preview');
    const perspectivesDiv = document.getElementById('perspective-fields');
    const translations = <?= json_encode($translations ?? []) ?>;
    let debounceTimer;

    function updatePreviewAndPerspectives() {
        const text = notesTextarea.value;

        fetch('/admin/ajax/expand_shortcodes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'text=' + encodeURIComponent(text)
        })
        .then(response => response.json())
        .then(data => {
            previewDiv.innerHTML = data.expanded_text || '';
            perspectivesDiv.innerHTML = '';

            if (data.perspectives && data.perspectives.length > 0) {
                const header = document.createElement('h3');
                header.textContent = 'Perspectives';
                perspectivesDiv.appendChild(header);

                data.perspectives.forEach(p => {
                    const saved_translation = translations[p.type] && translations[p.type][p.id] ? translations[p.type][p.id] : null;

                    const container = document.createElement('div');
                    container.style.marginBottom = '15px';

                    const label = document.createElement('label');
                    label.style.display = 'block';
                    label.style.fontWeight = 'bold';
                    label.textContent = `As ${p.name} (${p.type}):`;

                    const textarea = document.createElement('textarea');
                    textarea.name = `perspectives[${p.type}][${p.id}][note]`;
                    textarea.rows = 3;
                    textarea.style.width = '100%';
                    textarea.value = saved_translation ? saved_translation.note : text;

                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.style.marginLeft = '10px';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = `perspectives[${p.type}][${p.id}][is_significant]`;
                    checkbox.value = '1';
                    if (saved_translation && saved_translation.is_significant) {
                        checkbox.checked = true;
                    }

                    checkboxLabel.appendChild(checkbox);
                    checkboxLabel.append(' Is Significant Moment?');

                    container.appendChild(label);
                    container.appendChild(textarea);
                    container.appendChild(checkboxLabel);
                    perspectivesDiv.appendChild(container);
                });
            }
        })
        .catch(error => {
            previewDiv.innerHTML = '<span style="color: red;">Error loading preview: ' + error + '</span>';
            console.error('Fetch error:', error);
        });
    }

    notesTextarea.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updatePreviewAndPerspectives, 300);
    });

    updatePreviewAndPerspectives();
});

document.getElementById('frame_start_seconds').addEventListener('input', function(e) {
    const seconds = e.target.value;
    if (seconds && !isNaN(seconds)) {
        const frameStartInput = document.getElementById('frame_start');
        frameStartInput.value = Math.round(seconds * 12);
    }
});

document.getElementById('frame_end_seconds').addEventListener('input', function(e) {
    const seconds = e.target.value;
    if (seconds && !isNaN(seconds)) {
        const frameEndInput = document.getElementById('frame_end');
        frameEndInput.value = Math.round(seconds * 12);
    }
});
</script>
