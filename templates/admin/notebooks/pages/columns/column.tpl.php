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
        <!-- Token Canvas Section -->
        <div class="tokens-section">
            <h2>Tokens</h2>
            <p>Double-click on the canvas below to create a new token.</p>
            
            <div id="tokens-canvas" class="tokens-canvas">
                <!-- Tokens will be loaded here via JavaScript -->
            </div>

            <!-- Token Form (initially hidden) -->
            <div id="token-form" class="token-form" style="display: none;">
                <h3>Add/Edit Token</h3>
                <form id="token-form-element">
                    <input type="hidden" id="token-id" name="token_id" value="">
                    <input type="hidden" id="column-id" name="column_id" value="<?= $column->column_id ?>">
                    <input type="hidden" id="token-x-pos" name="token_x_pos" value="0">
                    <input type="hidden" id="token-y-pos" name="token_y_pos" value="0">
                    
                    <label>
                        Token Text:<br>
                        <input type="text" id="token-string" name="token_string" required>
                    </label><br><br>
                    
                    <label>
                        Date (optional):<br>
                        <input type="text" id="token-date" name="token_date" placeholder="e.g., 2023-12-01">
                    </label><br><br>
                    
                    <label>
                        Width:<br>
                        <input type="number" id="token-width" name="token_width" value="100" min="50" max="300">
                    </label><br><br>
                    
                    <label>
                        Height:<br>
                        <input type="number" id="token-height" name="token_height" value="50" min="30" max="150">
                    </label><br><br>
                    
                    <label>
                        Color:<br>
                        <select id="token-color" name="token_color">
                            <option value="Black">Black</option>
                            <option value="Red">Red</option>
                            <option value="Blue">Blue</option>
                        </select>
                    </label><br><br>
                    
                    <button type="submit">Save Token</button>
                    <button type="button" id="cancel-token">Cancel</button>
                    <button type="button" id="delete-token" style="display: none; background: #dc3545;">Delete Token</button>
                </form>
            </div>
        </div>

        <style>
            .tokens-section {
                margin-top: 30px;
                border-top: 1px solid #ccc;
                padding-top: 20px;
            }
            
            .tokens-canvas {
                position: relative;
                width: 100%;
                height: 500px;
                border: 2px dashed #ccc;
                background: #f9f9f9;
                cursor: pointer;
                margin: 20px 0;
            }
            
            .token {
                position: absolute;
                border: 2px solid #333;
                background: white;
                padding: 5px;
                cursor: move;
                border-radius: 3px;
                font-size: 12px;
                line-height: 1.2;
                word-wrap: break-word;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                user-select: none;
            }
            
            .token.red {
                border-color: #dc3545;
                background: #fff5f5;
            }
            
            .token.blue {
                border-color: #007bff;
                background: #f0f8ff;
            }
            
            .token.black {
                border-color: #333;
                background: white;
            }
            
            .token:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            
            .token-form {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border: 1px solid #ccc;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                z-index: 1000;
                width: 400px;
            }
            
            .token-form h3 {
                margin-top: 0;
            }
            
            .token-form label {
                display: block;
                margin-bottom: 10px;
            }
            
            .token-form input, .token-form select {
                width: 100%;
                padding: 5px;
                border: 1px solid #ccc;
                border-radius: 3px;
            }
            
            .token-form button {
                margin-right: 10px;
                padding: 8px 16px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
            }
            
            .token-form button[type="submit"] {
                background: #007bff;
                color: white;
            }
            
            .token-form button[type="button"] {
                background: #6c757d;
                color: white;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('tokens-canvas');
                const tokenForm = document.getElementById('token-form');
                const formElement = document.getElementById('token-form-element');
                const cancelButton = document.getElementById('cancel-token');
                const deleteButton = document.getElementById('delete-token');
                
                let currentToken = null;
                let draggedToken = null;
                let dragOffset = { x: 0, y: 0 };

                // Load existing tokens
                loadTokens();

                // Double-click to create new token
                canvas.addEventListener('dblclick', function(e) {
                    if (e.target === canvas) {
                        const rect = canvas.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;
                        
                        showTokenForm(null, x, y);
                    }
                });

                // Token form submission
                formElement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveToken();
                });

                // Cancel button
                cancelButton.addEventListener('click', function() {
                    hideTokenForm();
                });

                // Delete button
                deleteButton.addEventListener('click', function() {
                    if (currentToken && confirm('Are you sure you want to delete this token?')) {
                        deleteToken(currentToken.token_id);
                    }
                });

                function loadTokens() {
                    const columnId = document.getElementById('column-id').value;
                    
                    fetch(`/admin/ajax/tokens.php?action=list&column_id=${columnId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                renderTokens(data.tokens);
                            }
                        })
                        .catch(error => console.error('Error loading tokens:', error));
                }

                function renderTokens(tokens) {
                    // Clear existing tokens
                    const existingTokens = canvas.querySelectorAll('.token');
                    existingTokens.forEach(token => token.remove());

                    // Render new tokens
                    tokens.forEach(token => {
                        createTokenElement(token);
                    });
                }

                function createTokenElement(token) {
                    const tokenElement = document.createElement('div');
                    tokenElement.className = `token ${token.token_color.toLowerCase()}`;
                    tokenElement.style.left = token.token_x_pos + 'px';
                    tokenElement.style.top = token.token_y_pos + 'px';
                    tokenElement.style.width = token.token_width + 'px';
                    tokenElement.style.height = token.token_height + 'px';
                    tokenElement.textContent = token.token_string;
                    tokenElement.dataset.tokenId = token.token_id;

                    // Double-click to edit
                    tokenElement.addEventListener('dblclick', function(e) {
                        e.stopPropagation();
                        showTokenForm(token);
                    });

                    // Drag functionality
                    tokenElement.addEventListener('mousedown', function(e) {
                        draggedToken = tokenElement;
                        const rect = tokenElement.getBoundingClientRect();
                        const canvasRect = canvas.getBoundingClientRect();
                        dragOffset.x = e.clientX - rect.left;
                        dragOffset.y = e.clientY - rect.top;
                        
                        tokenElement.style.zIndex = '999';
                        document.addEventListener('mousemove', dragToken);
                        document.addEventListener('mouseup', stopDragToken);
                        e.preventDefault();
                    });

                    canvas.appendChild(tokenElement);
                }

                function dragToken(e) {
                    if (!draggedToken) return;
                    
                    const canvasRect = canvas.getBoundingClientRect();
                    let x = e.clientX - canvasRect.left - dragOffset.x;
                    let y = e.clientY - canvasRect.top - dragOffset.y;
                    
                    // Keep within canvas bounds
                    x = Math.max(0, Math.min(x, canvas.offsetWidth - draggedToken.offsetWidth));
                    y = Math.max(0, Math.min(y, canvas.offsetHeight - draggedToken.offsetHeight));
                    
                    draggedToken.style.left = x + 'px';
                    draggedToken.style.top = y + 'px';
                }

                function stopDragToken() {
                    if (draggedToken) {
                        draggedToken.style.zIndex = '';
                        
                        // Update token position in database
                        const tokenId = draggedToken.dataset.tokenId;
                        const x = parseInt(draggedToken.style.left);
                        const y = parseInt(draggedToken.style.top);
                        
                        updateTokenPosition(tokenId, x, y);
                        
                        draggedToken = null;
                    }
                    document.removeEventListener('mousemove', dragToken);
                    document.removeEventListener('mouseup', stopDragToken);
                }

                function showTokenForm(token, x = 0, y = 0) {
                    currentToken = token;
                    
                    if (token) {
                        // Edit existing token
                        document.getElementById('token-id').value = token.token_id;
                        document.getElementById('token-string').value = token.token_string;
                        document.getElementById('token-date').value = token.token_date;
                        document.getElementById('token-x-pos').value = token.token_x_pos;
                        document.getElementById('token-y-pos').value = token.token_y_pos;
                        document.getElementById('token-width').value = token.token_width;
                        document.getElementById('token-height').value = token.token_height;
                        document.getElementById('token-color').value = token.token_color;
                        deleteButton.style.display = 'inline-block';
                    } else {
                        // Create new token
                        document.getElementById('token-id').value = '';
                        document.getElementById('token-string').value = '';
                        document.getElementById('token-date').value = '';
                        document.getElementById('token-x-pos').value = x;
                        document.getElementById('token-y-pos').value = y;
                        document.getElementById('token-width').value = '100';
                        document.getElementById('token-height').value = '50';
                        document.getElementById('token-color').value = 'Black';
                        deleteButton.style.display = 'none';
                    }
                    
                    tokenForm.style.display = 'block';
                    document.getElementById('token-string').focus();
                }

                function hideTokenForm() {
                    tokenForm.style.display = 'none';
                    currentToken = null;
                }

                function saveToken() {
                    const formData = new FormData(formElement);
                    const tokenId = formData.get('token_id');
                    
                    if (tokenId) {
                        // Update existing token
                        fetch(`/admin/ajax/tokens.php?id=${tokenId}`, {
                            method: 'PUT',
                            body: new URLSearchParams(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                hideTokenForm();
                                loadTokens();
                            } else {
                                alert('Error updating token: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error updating token:', error);
                            alert('Error updating token');
                        });
                    } else {
                        // Create new token
                        fetch('/admin/ajax/tokens.php?action=create', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                hideTokenForm();
                                loadTokens();
                            } else {
                                alert('Error creating token: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error creating token:', error);
                            alert('Error creating token');
                        });
                    }
                }

                function updateTokenPosition(tokenId, x, y) {
                    const formData = new URLSearchParams();
                    formData.append('token_x_pos', x);
                    formData.append('token_y_pos', y);
                    
                    fetch(`/admin/ajax/tokens.php?id=${tokenId}`, {
                        method: 'PUT',
                        body: formData
                    })
                    .catch(error => console.error('Error updating token position:', error));
                }

                function deleteToken(tokenId) {
                    fetch(`/admin/ajax/tokens.php?id=${tokenId}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            hideTokenForm();
                            loadTokens();
                        } else {
                            alert('Error deleting token: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting token:', error);
                        alert('Error deleting token');
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>