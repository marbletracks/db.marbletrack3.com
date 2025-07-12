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
            <textarea id="shortcodey" name="notes" rows="15" cols="100"><?= htmlspecialchars($moment->notes ?? '') ?></textarea>
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
            <input type="number" name="frame_start" value="<?= htmlspecialchars((string)($moment->frame_start ?? '')) ?>">
        </label><br><br>

        <label>
            Frame End:<br>
            <input type="number" name="frame_end" value="<?= htmlspecialchars((string)($moment->frame_end ?? '')) ?>">
        </label><br><br>

        <label>
            Phrase ID:<br>
            <input type="number" name="phrase_id" value="<?= htmlspecialchars((string)($moment->phrase_id ?? '')) ?>">
        </label><br><br>

        <label>
            Take:<br>
            <select name="take_id">
                <option value="">-- Select a Take --</option>
                <?php foreach ($takes as $take): ?>
                    <option value="<?= $take->take_id ?>" <?= ($moment && $moment->take_id == $take->take_id) ? 'selected' : '' ?>>
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
    let debounceTimer;

    // Store original values to prevent overwriting manual edits
    let perspectiveValues = {};

    function updatePreviewAndPerspectives() {
        const text = notesTextarea.value;

        fetch('/admin/ajax/expand_shortcodes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'text=' + encodeURIComponent(text)
        })
        .then(response => response.json())
        .then(data => {
            // Update live preview
            previewDiv.innerHTML = data.expanded_text || '';

            // Update perspective fields
            perspectivesDiv.innerHTML = ''; // Clear existing fields
            if (data.perspectives && data.perspectives.length > 0) {
                const header = document.createElement('h3');
                header.textContent = 'Perspectives';
                perspectivesDiv.appendChild(header);

                data.perspectives.forEach(p => {
                    const fieldId = `perspective-${p.type}-${p.id}`;

                    const label = document.createElement('label');
                    label.style.display = 'block';
                    label.style.marginTop = '10px';
                    label.textContent = `As ${p.name} (${p.type}):`;

                    const textarea = document.createElement('textarea');
                    textarea.name = `perspectives[${p.type}][${p.id}]`;
                    textarea.id = fieldId;
                    textarea.rows = 3;
                    textarea.style.width = '100%';

                    // Pre-populate with expanded text, but respect manual edits
                    if (perspectiveValues[fieldId] === undefined) {
                        textarea.value = data.expanded_text;
                    } else {
                        textarea.value = perspectiveValues[fieldId];
                    }

                    textarea.addEventListener('input', () => {
                        perspectiveValues[fieldId] = textarea.value;
                    });

                    perspectivesDiv.appendChild(label);
                    perspectivesDiv.appendChild(textarea);
                });
            }
        })
        .catch(error => {
            previewDiv.innerHTML = '<span style="color: red;">Error loading preview.</span>';
            console.error('Fetch error:', error);
        });
    }

    notesTextarea.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        // Clear saved values when the source text changes
        perspectiveValues = {};
        debounceTimer = setTimeout(updatePreviewAndPerspectives, 300);
    });

    // Initial load
    updatePreviewAndPerspectives();
});
</script>
