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

        <div id="token-form" style="display: none; position: absolute; z-index: 10; background: #f0f0f0; border: 1px solid #ccc; padding: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <h3 id="token-form-title">Create Token</h3>
            <input type="hidden" id="token-id" value="">
            <input type="hidden" id="token-x-pos" value="">
            <input type="hidden" id="token-y-pos" value="">
            <label>
                Token Text:<br>
                <textarea id="token-string" rows="3" cols="40"></textarea>
            </label><br><br>
            <label>
                Date (optional):<br>
                <input type="text" id="token-date" placeholder="e.g., 2024-01-15">
            </label><br><br>
            <label>
                Color:<br>
                <select id="token-color">
                    <option value="Black">Black</option>
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                </select>
            </label><br><br>
            <button type="button" id="save-token-btn">Save</button>
            <button type="button" id="cancel-token-btn">Cancel</button>
        </div>

        <div id="tokens-canvas" style="position: relative; width: 800px; height: 1600px; border: 2px solid #ddd; margin-top: 20px; background: #f9f9f9;">
            <?php foreach ($tokens as $token): ?>
                <div class="token-item"
                     data-token-id="<?= $token->token_id ?>"
                     data-token-color="<?= htmlspecialchars($token->token_color) ?>"
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
                    <div class="token-content">
                        <?= htmlspecialchars($token->token_string) ?>
                        <?php if ($token->token_date): ?>
                            <br><small class="token-date" style="color: #666;"><?= htmlspecialchars($token->token_date) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="token-controls" style="position: absolute; top: 2px; right: 2px; display: none;">
                        <button class="edit-token-btn" style="font-size: 10px; padding: 1px 3px;">✏️</button>
                        <button class="delete-token-btn" style="font-size: 10px; padding: 1px 3px;">❌</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('tokens-canvas');
        const tokenForm = document.getElementById('token-form');
        const saveTokenBtn = document.getElementById('save-token-btn');
        const cancelTokenBtn = document.getElementById('cancel-token-btn');
        const tokenFormTitle = document.getElementById('token-form-title');
        const tokenIdInput = document.getElementById('token-id');
        const tokenXPosInput = document.getElementById('token-x-pos');
        const tokenYPosInput = document.getElementById('token-y-pos');
        const tokenStringInput = document.getElementById('token-string');
        const tokenDateInput = document.getElementById('token-date');
        const tokenColorInput = document.getElementById('token-color');

        let draggedToken = null;
        let startX, startY, startLeft, startTop;

        // --- Event Listeners ---

        canvas.addEventListener('dblclick', handleCanvasDblClick);
        saveTokenBtn.addEventListener('click', saveToken);
        cancelTokenBtn.addEventListener('click', hideTokenForm);
        canvas.addEventListener('mousedown', handleDragStart);
        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
        canvas.addEventListener('mouseover', (e) => showElement(e.target.closest('.token-item')?.querySelector('.token-controls')));
        canvas.addEventListener('mouseout', (e) => hideElement(e.target.closest('.token-item')?.querySelector('.token-controls')));
        canvas.addEventListener('click', handleCanvasClick);


        // --- Main Functions ---

        function handleCanvasDblClick(e) {
            const tokenElement = e.target.closest('.token-item');
            if (tokenElement) {
                e.stopPropagation(); // Prevent canvas dblclick from firing
                showEditForm(tokenElement);
            } else if (e.target === canvas) {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                showCreateForm(x, y);
            }
        }

        function handleCanvasClick(e) {
            if (e.target.classList.contains('edit-token-btn')) {
                showEditForm(e.target.closest('.token-item'));
            }
            if (e.target.classList.contains('delete-token-btn')) {
                const tokenElement = e.target.closest('.token-item');
                if (confirm('Are you sure you want to delete this token?')) {
                    deleteToken(tokenElement.dataset.tokenId);
                }
            }
        }

        function showCreateForm(x, y) {
            hideTokenForm(); // Reset form
            tokenFormTitle.textContent = 'Create New Token';
            tokenXPosInput.value = Math.round(x);
            tokenYPosInput.value = Math.round(y);

            // Recall last used date
            const lastDate = localStorage.getItem('lastTokenDate');
            if (lastDate) {
                tokenDateInput.value = lastDate;
            }

            tokenForm.style.left = `${x + 5}px`;
            tokenForm.style.top = `${y + 5}px`;
            showElement(tokenForm);
            tokenStringInput.focus();
        }

        function showEditForm(tokenElement) {
            hideTokenForm();
            const tokenId = tokenElement.dataset.tokenId;
            const content = tokenElement.querySelector('.token-content').firstChild.textContent.trim();
            const dateElement = tokenElement.querySelector('.token-date');
            const date = dateElement ? dateElement.textContent : '';
            const color = tokenElement.dataset.tokenColor || 'Black';

            tokenFormTitle.textContent = 'Edit Token';
            tokenIdInput.value = tokenId;
            tokenStringInput.value = content;
            tokenDateInput.value = date;
            tokenColorInput.value = color;

            tokenForm.style.left = tokenElement.style.left;
            tokenForm.style.top = tokenElement.style.top;
            showElement(tokenForm);
            tokenStringInput.focus();
        }

        function hideTokenForm() {
            hideElement(tokenForm);
            tokenIdInput.value = '';
            tokenStringInput.value = '';
            tokenDateInput.value = '';
            tokenColorInput.value = 'Black';
            tokenXPosInput.value = '';
            tokenYPosInput.value = '';
        }

        function saveToken() {
            const id = tokenIdInput.value;
            const action = id ? 'update' : 'create';
            const tokenData = {
                action: action,
                column_id: '<?= $column->column_id ?>',
                token_string: tokenStringInput.value.trim(),
                token_date: tokenDateInput.value.trim(),
                token_color: tokenColorInput.value,
                token_id: id
            };

            if (!tokenData.token_string) {
                alert('Token text cannot be empty.');
                return;
            }

            // Store date for next time
            if (tokenData.token_date) {
                localStorage.setItem('lastTokenDate', tokenData.token_date);
            }

            const formData = new FormData();
            if (action === 'create') {
                tokenData.token_x_pos = tokenXPosInput.value;
                tokenData.token_y_pos = tokenYPosInput.value;
                tokenData.token_width = 150; // Default width
                tokenData.token_height = 80; // Default height
            }

            for (const key in tokenData) {
                formData.append(key, tokenData[key]);
            }

            fetch('/admin/ajax/tokens.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideTokenForm();
                    if (action === 'create') {
                        renderToken(data.token);
                    } else {
                        updateTokenInDOM(data.token);
                    }
                } else {
                    alert('Error saving token: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the token.');
            });
        }

        function deleteToken(tokenId) {
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
                    const tokenElement = document.querySelector(`.token-item[data-token-id="${tokenId}"]`);
                    if (tokenElement) {
                        tokenElement.remove();
                    }
                } else {
                    alert('Error deleting token: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the token.');
            });
        }

        function renderToken(token) {
            const tokenElement = document.createElement('div');
            tokenElement.className = 'token-item';
            tokenElement.dataset.tokenId = token.token_id;
            tokenElement.dataset.tokenColor = token.token_color;
            tokenElement.style.position = 'absolute';
            tokenElement.style.left = `${token.token_x_pos}px`;
            tokenElement.style.top = `${token.token_y_pos}px`;
            tokenElement.style.width = `${token.token_width}px`;
            tokenElement.style.height = `${token.token_height}px`;
            tokenElement.style.cursor = 'move';
            tokenElement.style.padding = '5px';
            tokenElement.style.fontSize = '12px';
            tokenElement.style.overflow = 'hidden';
            tokenElement.style.resize = 'both';
            tokenElement.style.minWidth = '50px';
            tokenElement.style.minHeight = '30px';

            updateTokenStyle(tokenElement, token.token_color);

            const content = document.createElement('div');
            content.className = 'token-content';
            content.innerHTML = htmlspecialchars(token.token_string) +
                (token.token_date ? `<br><small class="token-date" style="color: #666;">${htmlspecialchars(token.token_date)}</small>` : '');

            const controls = document.createElement('div');
            controls.className = 'token-controls';
            controls.style.position = 'absolute';
            controls.style.top = '2px';
            controls.style.right = '2px';
            controls.style.display = 'none';
            controls.innerHTML = `<button class="edit-token-btn" style="font-size: 10px; padding: 1px 3px;">✏️</button> <button class="delete-token-btn" style="font-size: 10px; padding: 1px 3px;">❌</button>`;

            tokenElement.appendChild(content);
            tokenElement.appendChild(controls);
            canvas.appendChild(tokenElement);

            resizeObserver.observe(tokenElement);
        }

        function updateTokenInDOM(token) {
            const tokenElement = document.querySelector(`.token-item[data-token-id="${token.token_id}"]`);
            if (!tokenElement) return;

            tokenElement.dataset.tokenColor = token.token_color;
            tokenElement.querySelector('.token-content').innerHTML = htmlspecialchars(token.token_string) +
                (token.token_date ? `<br><small class="token-date" style="color: #666;">${htmlspecialchars(token.token_date)}</small>` : '');

            updateTokenStyle(tokenElement, token.token_color);
        }

        function updateTokenStyle(element, color) {
            const lowerColor = color.toLowerCase();
            element.style.background = lowerColor === 'red' ? '#ffeeee' : (lowerColor === 'blue' ? '#eeeeff' : '#ffffff');
            element.style.border = `2px solid ${lowerColor === 'red' ? '#ff0000' : (lowerColor === 'blue' ? '#0000ff' : '#000000')}`;
        }

        // --- Drag and Drop ---

        function handleDragStart(e) {
            const token = e.target.closest('.token-item');
            if (!token || e.target.closest('.token-controls')) return;

            const rect = token.getBoundingClientRect();
            if (e.clientX > rect.right - 16 && e.clientY > rect.bottom - 16) {
                return; // It's a resize, not a drag
            }

            draggedToken = token;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(token.style.left);
            startTop = parseInt(token.style.top);
            e.preventDefault();
        }

        function handleDragMove(e) {
            if (!draggedToken) return;
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            draggedToken.style.left = `${Math.max(0, startLeft + deltaX)}px`;
            draggedToken.style.top = `${Math.max(0, startTop + deltaY)}px`;
        }

        function handleDragEnd() {
            if (!draggedToken) return;
            const tokenId = draggedToken.dataset.tokenId;
            const newX = parseInt(draggedToken.style.left);
            const newY = parseInt(draggedToken.style.top);
            updateTokenPosition(tokenId, newX, newY);
            draggedToken = null;
        }

        function updateTokenPosition(id, x, y) {
            const formData = new FormData();
            formData.append('action', 'update_position');
            formData.append('token_id', id);
            formData.append('x_pos', x);
            formData.append('y_pos', y);
            fetch('/admin/ajax/tokens.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if (!data.success) console.error('Failed to update position'); })
                .catch(err => console.error('Error updating position:', err));
        }

        // --- Resize ---

        const resizeObserver = new ResizeObserver(entries => {
            for (let entry of entries) {
                if (entry.target.resizeTimeout) clearTimeout(entry.target.resizeTimeout);
                entry.target.resizeTimeout = setTimeout(() => {
                    const token = entry.target;
                    const tokenId = token.dataset.tokenId;
                    const newWidth = Math.round(entry.contentRect.width);
                    const newHeight = Math.round(entry.contentRect.height);
                    updateTokenSize(tokenId, newWidth, newHeight);
                }, 500);
            }
        });

        document.querySelectorAll('.token-item').forEach(token => resizeObserver.observe(token));

        function updateTokenSize(id, width, height) {
            const formData = new FormData();
            formData.append('action', 'update_size');
            formData.append('token_id', id);
            formData.append('width', width);
            formData.append('height', height);
            fetch('/admin/ajax/tokens.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if (!data.success) console.error('Failed to update size'); })
                .catch(err => console.error('Error updating size:', err));
        }

        // --- Utility Functions ---
        function showElement(el) { if (el) el.style.display = 'block'; }
        function hideElement(el) { if (el) el.style.display = 'none'; }
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return str.replace(/[&<>"']/g, m => map[m]);
        }
    });
    </script>
    <?php endif; // ($column): ?>
</div>
