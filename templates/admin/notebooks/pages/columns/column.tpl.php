<?php
// File: /templates/admin/notebooks/pages/columns/column.tpl.php
?>
<div class="PagePanel">
    <h1><?= $column ? 'Edit Column' : 'Create Column' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($page): ?>
        <p><a href="/admin/notebooks/pages/page.php?id=<?= $page->page_id ?>">Back to Page <?= htmlspecialchars($page->number) ?></a></p>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Worker:<br>
            <select name="worker_id">
                <option value="">-- Select a Worker --</option>
                <?php if (!empty($workers)): ?>
                    <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker->worker_id ?>" <?= ($column->worker_id ?? '') == $worker->worker_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars("{$worker->worker_alias} - {$worker->name}") ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No workers available</option>
                <?php endif; ?>
            </select>
        </label><br><br>

        <label>
            Column Name:<br>
            <input type="text" name="col_name" value="<?= htmlspecialchars($column->col_name ?? '') ?>">
        </label><br><br>

        <label>
            Sort Order:<br>
            <input type="number" name="col_sort" value="<?= htmlspecialchars($column->col_sort ?? '0') ?>">
        </label><br><br>

        <button type="submit">Save Column</button>
    </form>

    <?php if ($column): ?>
        <div class="tokens-section">
            <h2>Tokens</h2>
            <div class="tokens-controls">
                <button type="button" id="add-token-btn">Add Token</button>
                <div id="token-form" style="display: none; margin-top: 10px; padding: 10px; border: 1px solid #ccc;">
                    <h3>Create New Token</h3>
                    <label>
                        Token Text:<br>
                        <textarea id="new-token-string" rows="3" cols="40"></textarea>
                    </label><br><br>
                    <label>
                        Date (optional):<br>
                        <input type="text" id="new-token-date" placeholder="e.g., 2024-01-15">
                    </label><br><br>
                    <label>
                        Color:<br>
                        <select id="new-token-color">
                            <option value="Black">Black</option>
                            <option value="Red">Red</option>
                            <option value="Blue">Blue</option>
                        </select>
                    </label><br><br>
                    <button type="button" id="save-token-btn">Save Token</button>
                    <button type="button" id="cancel-token-btn">Cancel</button>
                </div>
            </div>

            <div id="tokens-canvas" style="position: relative; width: 800px; height: 1600px; border: 2px solid #ddd; margin-top: 20px; background: #f9f9f9;">
                <?php foreach ($tokens as $token): ?>
                    <div class="token-item"
                         data-token-id="<?= $token->token_id ?>"
                         style="position: absolute;
                                left: <?= $token->token_x_pos ?>px;
                                top: <?= $token->token_y_pos ?>px;
                                width: <?= $token->token_width ?>px;
                                height: <?= $token->token_height ?>px;
                                background: <?= strtolower($token->token_color) === 'red' ? '#ffeeee' : (strtolower($token->token_color) === 'blue' ? '#eeeeff' : '#ffffff') ?>;
                                border: 2px solid <?= strtolower($token->token_color) === 'red' ? '#ff0000' : (strtolower($token->token_color) === 'blue' ? '#0000ff' : '#000000') ?>;
                                cursor: move;
                                padding: 5px;
                                font-size: 12px;
                                overflow: hidden;
                                resize: both;
                                min-width: 50px;
                                min-height: 30px;">
                        <div class="token-content" ondblclick="editToken(<?= $token->token_id ?>)">
                            <?= htmlspecialchars($token->token_string) ?>
                            <?php if ($token->token_date): ?>
                                <br><small style="color: #666;"><?= htmlspecialchars($token->token_date) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="token-controls" style="position: absolute; top: 2px; right: 2px; display: none;">
                            <button onclick="editToken(<?= $token->token_id ?>)" style="font-size: 10px; padding: 1px 3px;">✏️</button>
                            <button onclick="deleteToken(<?= $token->token_id ?>)" style="font-size: 10px; padding: 1px 3px;">❌</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; // ($column): ?>
</div>

<?php if ($column): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('tokens-canvas');
    const addTokenBtn = document.getElementById('add-token-btn');
    const tokenForm = document.getElementById('token-form');
    const saveTokenBtn = document.getElementById('save-token-btn');
    const cancelTokenBtn = document.getElementById('cancel-token-btn');

    let draggedToken = null;
    let startX = 0;
    let startY = 0;
    let startLeft = 0;
    let startTop = 0;
    let isEditing = false;

    // Show/hide token form
    addTokenBtn.addEventListener('click', function() {
        tokenForm.style.display = tokenForm.style.display === 'none' ? 'block' : 'none';
        // Set the date field if there is a saved value
        if (tokenForm.style.display === 'block' && localStorage.getItem('lastTokenDate')) {
            document.getElementById('new-token-date').value = localStorage.getItem('lastTokenDate');
        }
    });

    cancelTokenBtn.addEventListener('click', function() {
        tokenForm.style.display = 'none';
        clearTokenForm();
    });

    // Save new token
    saveTokenBtn.addEventListener('click', function() {
        const tokenString = document.getElementById('new-token-string').value.trim();
        const tokenDate = document.getElementById('new-token-date').value.trim();
        const tokenColor = document.getElementById('new-token-color').value;

        if (!tokenString) {
            alert('Token text is required');
            return;
        }

        // Store the date to localStorage so it's remembered for subsequent tokens
        if (tokenDate) {
            localStorage.setItem('lastTokenDate', tokenDate);
        }

        createToken(tokenString, tokenDate, tokenColor);
    });

    function createToken(tokenString, tokenDate, tokenColor) {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('column_id', '<?= $column->column_id ?>');
        formData.append('token_string', tokenString);
        formData.append('token_date', tokenDate);
        formData.append('token_x_pos', '10');
        formData.append('token_y_pos', '10');
        formData.append('token_width', '100');
        formData.append('token_height', '50');
        formData.append('token_color', tokenColor);

        fetch('/admin/ajax/tokens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the new token
                location.reload();
            } else {
                alert('Error creating token: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating token');
        });
    }

    function clearTokenForm() {
        document.getElementById('new-token-string').value = '';
        document.getElementById('new-token-date').value = '';
        document.getElementById('new-token-color').value = 'Black';
    }

    // Make tokens draggable and resizable
    canvas.addEventListener('mousedown', function(e) {
        const token = e.target.closest('.token-item');
        if (!token || isEditing) return;

        // --- Only drag if NOT clicking the resize handle (bottom-right 16x16px) ---
        const rect = token.getBoundingClientRect();
        // Adjust 16 to match effective resize handle area for your UI/UX preference
        if (e.clientX > rect.right - 16 && e.clientY > rect.bottom - 16) {
            // Let the browser handle the native resize!
            return;
        }

        draggedToken = token;
        startX = e.clientX;
        startY = e.clientY;
        startLeft = parseInt(token.style.left);
        startTop = parseInt(token.style.top);

        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!draggedToken) return;

        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;

        const newLeft = Math.max(0, startLeft + deltaX);
        const newTop = Math.max(0, startTop + deltaY);

        draggedToken.style.left = newLeft + 'px';
        draggedToken.style.top = newTop + 'px';
    });

    document.addEventListener('mouseup', function(e) {
        if (!draggedToken) return;

        const tokenId = draggedToken.dataset.tokenId;
        const newLeft = parseInt(draggedToken.style.left);
        const newTop = parseInt(draggedToken.style.top);

        // Update position in database
        updateTokenPosition(tokenId, newLeft, newTop);

        draggedToken = null;
    });

    function updateTokenPosition(tokenId, x, y) {
        const formData = new FormData();
        formData.append('action', 'update_position');
        formData.append('token_id', tokenId);
        formData.append('x_pos', x);
        formData.append('y_pos', y);

        fetch('/admin/ajax/tokens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error updating position:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Show controls on hover
    canvas.addEventListener('mouseover', function(e) {
        const token = e.target.closest('.token-item');
        if (token) {
            const controls = token.querySelector('.token-controls');
            if (controls) {
                controls.style.display = 'block';
            }
        }
    });

    canvas.addEventListener('mouseout', function(e) {
        const token = e.target.closest('.token-item');
        if (token) {
            const controls = token.querySelector('.token-controls');
            if (controls) {
                controls.style.display = 'none';
            }
        }
    });

    // Handle resize
    const resizeObserver = new ResizeObserver(entries => {
        for (let entry of entries) {
            const token = entry.target;
            if (token.classList.contains('token-item')) {
                const tokenId = token.dataset.tokenId;
                const newWidth = Math.round(entry.contentRect.width);
                const newHeight = Math.round(entry.contentRect.height);

                // Debounce the resize updates
                clearTimeout(token.resizeTimeout);
                token.resizeTimeout = setTimeout(() => {
                    updateTokenSize(tokenId, newWidth, newHeight);
                }, 500);
            }
        }
    });

    // Observe all tokens for resize
    document.querySelectorAll('.token-item').forEach(token => {
        resizeObserver.observe(token);
    });

    function updateTokenSize(tokenId, width, height) {
        const formData = new FormData();
        formData.append('action', 'update_size');
        formData.append('token_id', tokenId);
        formData.append('width', width);
        formData.append('height', height);

        fetch('/admin/ajax/tokens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error updating size:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});

function editToken(tokenId) {
    isEditing = true;
    const tokenString = prompt('Enter new token text:');
    if (tokenString !== null && tokenString.trim() !== '') {
        const tokenDate = prompt('Enter token date (optional):') || '';
        const tokenColor = prompt('Enter token color (Black, Red, Blue):', 'Black') || 'Black';

        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('token_id', tokenId);
        formData.append('column_id', '<?= $column->column_id ?>');
        formData.append('token_string', tokenString.trim());
        formData.append('token_date', tokenDate);
        formData.append('token_color', tokenColor);

        // Get current position and size
        const token = document.querySelector(`[data-token-id="${tokenId}"]`);
        formData.append('token_x_pos', parseInt(token.style.left));
        formData.append('token_y_pos', parseInt(token.style.top));
        formData.append('token_width', parseInt(token.style.width));
        formData.append('token_height', parseInt(token.style.height));

        fetch('/admin/ajax/tokens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating token: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating token');
        })
        .finally(() => {
            isEditing = false;
        });
    } else {
        isEditing = false;
    }
}

function deleteToken(tokenId) {
    if (confirm('Are you sure you want to delete this token?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('token_id', tokenId);

        fetch('/admin/ajax/tokens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting token: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting token');
        });
    }
}
</script>
<?php endif; // ($column): ?>
