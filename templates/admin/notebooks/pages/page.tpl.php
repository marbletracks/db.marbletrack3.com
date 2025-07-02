<?php
// File: /templates/admin/notebooks/pages/page.tpl.php
?>
<div class="PagePanel">
    <h1><?= $page ? 'Edit Page' : 'Create Page' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Notebook ID:<br>
            <input type="text" name="notebook_id" value="<?= htmlspecialchars($page->notebook_id ?? '1') ?>">
        </label><br><br>

        <label>
            Page Number:<br>
            <input type="text" name="number" value="<?= htmlspecialchars($page->number ?? '') ?>">
        </label><br><br>

        <label>
            Created At (YYYY-MM-DD HH:MM:SS):<br>
            <input type="text" name="created_at"
                value="<?= htmlspecialchars($page->created_at ?? date('Y-m-d H:i:s')) ?>">
        </label><br><br>

        <label>
            Columns:<br>
            <div id="columns-section">
                <?php if (!empty($columns)): ?>
                    <?php foreach ($columns as $column): ?>
                        <div class="column-item" draggable="true" data-column-id="<?= $column->column_id ?>">
                            <div class="drag-handle">⋮⋮</div>
                            <div class="column-content">
                                <strong><?= htmlspecialchars($column->col_name) ?></strong>
                                <?php if (!empty($column->worker_name)): ?>
                                    <span class="column-worker">(<?= htmlspecialchars($column->worker_name) ?>)</span>
                                <?php elseif (!empty($column->worker_alias)): ?>
                                    <span class="column-worker">(<?= htmlspecialchars($column->worker_alias) ?>)</span>
                                <?php endif; ?>
                                <span class="token-count">(<?= $column->token_count ?> tokens)</span>
                                <span class="column-sort">(Sort: <?= $column->col_sort ?>)</span>
                                <a href="/admin/notebooks/pages/columns/column.php?id=<?= $column->column_id ?>" class="edit-column">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No columns created yet.</p>
                <?php endif; ?>
                <?php if ($page): ?>
                    <p><a href="/admin/notebooks/pages/columns/column.php?page_id=<?= $page->page_id ?>" class="create-column-link">Create Column</a></p>
                <?php endif; ?>
            </div>
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
                <?php if(!empty($page->photos)): foreach ($page->photos ?? [] as $index => $photo): ?>
                    <div class="photo-item" draggable="true" data-index="<?= $index ?>">
                        <div class="drag-handle">⋮⋮</div>
                        <div class="photo-content">
                            <a href="<?= htmlspecialchars($photo->getUrl()) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview">
                            </a>
                            <input type="text" size="120" name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>">
                            <button type="button" class="remove-photo" onclick="removePhoto(this)">×</button>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
                <div class="photo-item">
                    <div class="photo-content">
                        <input type="text" size="120" name="image_urls[]" value="" placeholder="Add new photo URL">
                        <button type="button" class="remove-photo" onclick="removePhoto(this)" style="display: none;">×</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addImageUrlField()">Add another</button>
        </label>

        <style>
            .photo-item {
                display: flex;
                align-items: center;
                margin: 5px 0;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #f9f9f9;
            }
            .photo-item.dragging {
                opacity: 0.5;
            }
            .photo-item.drag-over {
                border-color: #007cba;
                background: #e6f3ff;
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
            .photo-content {
                display: flex;
                align-items: center;
                flex: 1;
                gap: 10px;
            }
            .photo-content img {
                max-width: 80px;
                max-height: 60px;
            }
            .remove-photo {
                background: #dc3232;
                color: white;
                border: none;
                padding: 5px 8px;
                border-radius: 3px;
                cursor: pointer;
            }
            .remove-photo:hover {
                background: #a00;
            }
            .primary-indicator {
                background: #0073aa;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                margin-left: 5px;
            }
            .column-item {
                display: flex;
                align-items: center;
                margin: 5px 0;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                background: #f5f5f5;
            }
            .column-item.dragging {
                opacity: 0.5;
            }
            .column-item.drag-over {
                border-color: #007cba;
                background: #e6f3ff;
            }
            .column-content {
                display: flex;
                align-items: center;
                flex: 1;
                gap: 10px;
            }
            .column-sort {
                color: #666;
                font-size: 11px;
            }
            .token-count {
                color: #333;
                font-size: 11px;
                margin-left: 10px;
            }
            .column-worker {
                color: #0073aa;
                font-size: 11px;
                font-style: italic;
            }
            .edit-column {
                background: #0073aa;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                text-decoration: none;
                font-size: 11px;
            }
            .edit-column:hover {
                background: #005a87;
            }
            .create-column-link {
                background: #00a32a;
                color: white;
                padding: 5px 10px;
                border-radius: 3px;
                text-decoration: none;
                font-size: 12px;
            }
            .create-column-link:hover {
                background: #007a20;
            }
        </style>

        <script>
            let draggedElement = null;

            function addImageUrlField() {
                const container = document.getElementById('image-url-fields');
                const newItem = document.createElement('div');
                newItem.className = 'photo-item';
                newItem.innerHTML = `
                    <div class="photo-content">
                        <input type="text" size="120" name="image_urls[]" value="" placeholder="Add new photo URL">
                        <button type="button" class="remove-photo" onclick="removePhoto(this)">×</button>
                    </div>
                `;

                // Insert before the last item (which should be the empty one)
                const lastItem = container.lastElementChild;
                container.insertBefore(newItem, lastItem);

                // Focus the new input
                newItem.querySelector('input').focus();
            }

            function removePhoto(button) {
                const photoItem = button.closest('.photo-item');
                photoItem.remove();
                updatePrimaryIndicators();
            }

            function updatePrimaryIndicators() {
                const photoItems = document.querySelectorAll('.photo-item');
                photoItems.forEach((item, index) => {
                    const indicator = item.querySelector('.primary-indicator');
                    if (index === 0 && item.querySelector('input[name="image_urls[]"]').value.trim()) {
                        if (!indicator) {
                            const newIndicator = document.createElement('span');
                            newIndicator.className = 'primary-indicator';
                            newIndicator.textContent = 'PRIMARY';
                            item.querySelector('.photo-content').appendChild(newIndicator);
                        }
                    } else if (indicator) {
                        indicator.remove();
                    }
                });
            }

            // Set up drag and drop
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('image-url-fields');

                container.addEventListener('dragstart', function(e) {
                    if (e.target.classList.contains('photo-item')) {
                        draggedElement = e.target;
                        e.target.classList.add('dragging');
                    }
                });

                container.addEventListener('dragend', function(e) {
                    if (e.target.classList.contains('photo-item')) {
                        e.target.classList.remove('dragging');
                        draggedElement = null;
                        updatePrimaryIndicators();
                    }
                });

                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(container, e.clientY);
                    if (draggedElement) {
                        if (afterElement == null) {
                            container.appendChild(draggedElement);
                        } else {
                            container.insertBefore(draggedElement, afterElement);
                        }
                    }
                });

                // Make photo items draggable
                function makePhotoItemDraggable(item) {
                    if (item.querySelector('input[name="image_urls[]"]').value.trim()) {
                        item.draggable = true;
                        const handle = item.querySelector('.drag-handle');
                        if (!handle) {
                            const newHandle = document.createElement('div');
                            newHandle.className = 'drag-handle';
                            newHandle.textContent = '⋮⋮';
                            item.insertBefore(newHandle, item.firstChild);
                        }
                    }
                }

                // Initial setup
                document.querySelectorAll('.photo-item').forEach(makePhotoItemDraggable);
                updatePrimaryIndicators();

                // Monitor input changes to update draggability
                container.addEventListener('input', function(e) {
                    if (e.target.name === 'image_urls[]') {
                        const photoItem = e.target.closest('.photo-item');
                        makePhotoItemDraggable(photoItem);
                        updatePrimaryIndicators();
                    }
                });
            });

            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.photo-item:not(.dragging)')];

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

            // Column drag and drop functionality
            document.addEventListener('DOMContentLoaded', function() {
                const columnsContainer = document.getElementById('columns-section');
                if (!columnsContainer) return;

                columnsContainer.addEventListener('dragstart', function(e) {
                    if (e.target.classList.contains('column-item')) {
                        draggedElement = e.target;
                        e.target.classList.add('dragging');
                    }
                });

                columnsContainer.addEventListener('dragend', function(e) {
                    if (e.target.classList.contains('column-item')) {
                        e.target.classList.remove('dragging');
                        draggedElement = null;
                        updateColumnSort();
                    }
                });

                columnsContainer.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (draggedElement && draggedElement.classList.contains('column-item')) {
                        const afterElement = getColumnDragAfterElement(columnsContainer, e.clientY);
                        if (afterElement == null) {
                            columnsContainer.appendChild(draggedElement);
                        } else {
                            columnsContainer.insertBefore(draggedElement, afterElement);
                        }
                    }
                });
            });

            function getColumnDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.column-item:not(.dragging)')];

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

            function updateColumnSort() {
                const columnItems = document.querySelectorAll('.column-item');
                columnItems.forEach((item, index) => {
                    const sortSpan = item.querySelector('.column-sort');
                    if (sortSpan) {
                        sortSpan.textContent = `(Sort: ${index})`;
                    }
                });
            }
        </script>

        <button type="submit">Save</button>
    </form>

    <?php if ($page): ?>
        <p><a href="/admin/notebooks/notebook.php?id=<?= $page->notebook_id ?>">Back to Notebook
                <?= htmlspecialchars($page->notebook_id) ?></a></p>
    <?php endif; ?>
</div>
