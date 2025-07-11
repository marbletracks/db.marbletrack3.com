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
        <h4>(Oldest on top)</h4>
        <ul id="sortable-moments">
            <?php foreach ($part_moments as $moment): ?>
                <li data-moment-id="<?= $moment->moment_id ?>" draggable="true">
                    <div class="drag-handle">⋮⋮</div>
                    <?php if ($moment->moment_date): ?>
                        (<?= htmlspecialchars($moment->moment_date) ?>)&nbsp;
                    <?php else: // ($moment->moment_date): ?>
                        (<?= "--/--/----" ?>)&nbsp;
                    <?php endif; // ($moment->moment_date): ?>
                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>"><?= htmlspecialchars($moment->notes) ?></a>
                    <?php if ($moment->frame_start || $moment->frame_end): ?>
                        &nbsp; (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                    <?php endif; ?>

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
                $part_moment_ids = array_map(fn($m) => $m->moment_id, $part_moments);
                foreach ($all_moments as $moment):
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
                endforeach; ?>
            </select>
        </label><br><br>

        <style>
            #sortable-moments {
                list-style-type: none;
                padding: 0;
            }
            #sortable-moments li {
                display: flex;
                align-items: center;
                margin: 5px 0;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
            }
            #sortable-moments li.dragging {
                opacity: 0.5;
            }
            .drag-handle {
                cursor: grab;
                padding: 0 10px;
                color: #666;
                font-weight: bold;
                user-select: none;
            }
            .drag-handle:active {
                cursor: grabbing;
            }
            .remove-moment {
                margin-left: auto;
                background: #dc3232;
                color: white;
                border: none;
                padding: 5px 8px;
                border-radius: 3px;
                cursor: pointer;
            }
        </style>

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
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed');
    const sortableList = document.getElementById('sortable-moments');
    let draggedItem = null;

    if (sortableList) {
        console.log('Sortable list found', sortableList);
        // We listen for `mousedown` on the handle to initiate a drag.
        sortableList.addEventListener('mousedown', e => {
            if (e.target.classList.contains('drag-handle')) {
                console.log('Mousedown on a drag handle');
                // The `draggable` attribute is on the parent `li`.
                // We don't need to do anything here, the browser will initiate the drag on the `li`.
            }
        });

        sortableList.addEventListener('dragstart', e => {
            // The target of the dragstart event is the `li` element itself.
            draggedItem = e.target;
            // It's good practice to add a class to the dragged item for styling.
            setTimeout(() => {
                draggedItem.classList.add('dragging');
            }, 0);
            console.log('Drag started:', draggedItem);
        });

        sortableList.addEventListener('dragend', e => {
            console.log('Drag ended');
            if (draggedItem) {
                // Clean up the class.
                draggedItem.classList.remove('dragging');
                draggedItem = null;
                updateHiddenInput();
            }
        });

        sortableList.addEventListener('dragover', e => {
            // This is necessary to allow dropping.
            e.preventDefault();
            if (draggedItem) {
                const afterElement = getDragAfterElement(sortableList, e.clientY);
                // console.log('Dragging over, after element:', afterElement); // This can be noisy
                if (afterElement == null) {
                    sortableList.appendChild(draggedItem);
                } else {
                    sortableList.insertBefore(draggedItem, afterElement);
                }
            }
        });
    } else {
        console.log('Sortable list not found');
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('li:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Handle removing moments
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-moment')) {
            const listItem = e.target.closest('li');
            console.log('Removing moment:', listItem.dataset.momentId);
            const momentId = listItem.dataset.momentId;
            const notes = listItem.querySelector('a').textContent.trim();

            listItem.remove();

            // Add back to the dropdown
            const select = document.getElementById('add-moment-select');
            const option = document.createElement('option');
            option.value = momentId;
            option.dataset.notes = notes;
            option.textContent = notes;
            select.appendChild(option);
            updateHiddenInput();
        }
    });

    // Handle adding moments
    const addMomentSelect = document.getElementById('add-moment-select');
    if (addMomentSelect) {
        addMomentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) return;

            const momentId = selectedOption.value;
            const notes = selectedOption.dataset.notes;
            const frameStart = selectedOption.dataset.frameStart;
            const frameEnd = selectedOption.dataset.frameEnd;
            const momentDate = selectedOption.dataset.momentDate;
            console.log('Adding moment:', momentId);

            const newLi = document.createElement('li');
            newLi.dataset.momentId = momentId;
            newLi.draggable = true;

            let frameHTML = '';
            if (frameStart || frameEnd) {
                frameHTML = ` (Frames: ${frameStart || '?'} - ${frameEnd || '?'})`;
            }
            let dateHTML = '';
            if (momentDate) {
                dateHTML = ` (${momentDate})`;
            } else {
                dateHTML = ` (--/--/----)`;
            }

            newLi.innerHTML = `
                <div class="drag-handle">⋮⋮</div>
                ${dateHTML} <a href="/admin/moments/moment.php?id=${momentId}">${notes}</a> ${frameHTML}
                <button type="button" class="remove-moment">Remove</button>
            `;

            let list = document.getElementById('sortable-moments');
            if (!list) {
                console.log('Creating sortable list because it does not exist.');
                list = document.createElement('ul');
                list.id = 'sortable-moments';
                const h2 = document.createElement('h2');
                h2.textContent = 'Associated Moments';
                const form = document.querySelector('form');
                form.insertBefore(h2, addMomentSelect.parentElement);
                form.insertBefore(list, h2.nextSibling);
            }

            list.appendChild(newLi);
            selectedOption.remove();
            updateHiddenInput();
        });
    }

    // Update hidden input with sorted moment IDs before form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', updateHiddenInput);
    }

    function updateHiddenInput() {
        const momentIds = [];
        const momentList = document.getElementById('sortable-moments');
        if(momentList) {
            momentList.querySelectorAll('li').forEach(item => {
                momentIds.push(item.dataset.momentId);
            });
        }
        document.getElementById('moment_ids_hidden').value = momentIds.join(',');
        console.log('Updated hidden input:', momentIds.join(','));
    }
});
</script>

