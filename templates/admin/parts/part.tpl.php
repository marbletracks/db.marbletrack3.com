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
        <ul id="sortable-moments">
            <?php foreach ($part_moments as $moment): ?>
                <li data-moment-id="<?= $moment->moment_id ?>">
                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>">
                        <?= htmlspecialchars($moment->notes) ?>
                    </a>
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
                        <option value="<?= $moment->moment_id ?>" data-notes="<?= htmlspecialchars($moment->notes) ?>">
                            <?= htmlspecialchars($moment->notes) ?>
                        </option>
                    <?php endif;
                endforeach; ?>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sortableList = document.getElementById('sortable-moments');
    let draggedItem = null;

    if (sortableList) {
        sortableList.addEventListener('dragstart', e => {
            draggedItem = e.target;
            setTimeout(() => {
                e.target.style.display = 'none';
            }, 0);
        });

        sortableList.addEventListener('dragend', e => {
            setTimeout(() => {
                draggedItem.style.display = '';
                draggedItem = null;
            }, 0);
            updateHiddenInput();
        });

        sortableList.addEventListener('dragover', e => {
            e.preventDefault();
            const afterElement = getDragAfterElement(sortableList, e.clientY);
            if (afterElement == null) {
                sortableList.appendChild(draggedItem);
            } else {
                sortableList.insertBefore(draggedItem, afterElement);
            }
        });
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

            const newLi = document.createElement('li');
            newLi.dataset.momentId = momentId;
            newLi.draggable = true;
            newLi.innerHTML = `
                <a href="/admin/moments/moment.php?id=${momentId}">${notes}</a>
                <button type="button" class="remove-moment">Remove</button>
            `;

            if (!sortableList) {
                const newSortableList = document.createElement('ul');
                newSortableList.id = 'sortable-moments';
                const h2 = document.createElement('h2');
                h2.textContent = 'Associated Moments';
                const form = document.querySelector('form');
                form.insertBefore(newSortableList, addMomentSelect.parentElement);
                form.insertBefore(h2, newSortableList);
            }
            
            document.getElementById('sortable-moments').appendChild(newLi);
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
    }
});
</script>
