document.addEventListener('DOMContentLoaded', function () {
    // --- Initialize SortableJS for all worker sections ---
    document.querySelectorAll('.worker-card').forEach(card => {
        const workerId = card.querySelector('.create-moment-btn').dataset.workerId;
        const availableContainer = document.getElementById(`available-tokens-${workerId}`);
        const buildPhraseContainer = document.getElementById(`build-a-phrase-${workerId}`);

        if (!availableContainer || !buildPhraseContainer) return;

        new Sortable(availableContainer, {
            group: { name: `worker-${workerId}`, pull: true, put: true },
            animation: 150,
            forceFallback: true,
            ghostClass: 'blue-background-class',
            onStart: () => document.body.classList.add('dragging'),
            onEnd: () => setTimeout(() => document.body.classList.remove('dragging'), 50)
        });

        new Sortable(buildPhraseContainer, {
            group: { name: `worker-${workerId}`, pull: true, put: true },
            animation: 150,
            forceFallback: true,
            ghostClass: 'blue-background-class',
            onStart: () => document.body.classList.add('dragging'),
            onEnd: () => setTimeout(() => document.body.classList.remove('dragging'), 50)
        });
    });

    // --- Click vs. Drag Logic for Toggling Permanence ---
    document.querySelectorAll('.tokens-container').forEach(container => {
        container.addEventListener('click', function(e) {
            const tokenItem = e.target.closest('.token-item');
            if (tokenItem && !document.body.classList.contains('dragging')) {
                toggleTokenPermanence(tokenItem);
            }
        });
    });

    function toggleTokenPermanence(tokenElement) {
        const tokenId = tokenElement.dataset.tokenId;
        if (!tokenId) return;

        fetch('/admin/ajax/toggle_token_permanence.php', {
            method: 'POST',
            body: new URLSearchParams({ 'token_id': tokenId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tokenElement.classList.toggle('token-permanent', data.is_permanent);
            } else {
                alert('Error: ' + (data.error || 'An unknown error occurred.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected network error occurred.');
        });
    }

    // --- "Create Moment" Button Logic ---
    document.querySelectorAll('.create-moment-btn').forEach(button => {
        button.addEventListener('click', function () {
            const workerId = this.dataset.workerId;
            const card = this.closest('.worker-card');
            const buildPhraseContainer = card.querySelector(`#build-a-phrase-${workerId}`);
            const tokenItems = buildPhraseContainer.querySelectorAll('.token-item');

            if (tokenItems.length === 0) {
                alert('Please drag some tokens into the "Build-a-Phrase" box first.');
                return;
            }

            const tokenIds = Array.from(tokenItems).map(item => item.dataset.tokenId);
            const phraseText = Array.from(tokenItems).map(item => item.textContent.trim()).join(' ');
            
            // --- Prepare Data for Editor ---
            let frame_start = '';
            let frame_end = '';

            const framePattern = /\s+(\d+)\s*[-~]?\s*(\d+)$/;
            const match = phraseText.match(framePattern);
            if (match) {
                frame_start = match[1];
                frame_end = match[2];
            }

            const lastToken = tokenItems[tokenItems.length - 1];
            let moment_date = lastToken.dataset.tokenDate;
            if (!moment_date) {
                moment_date = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
            }

            // --- Populate and Show Editor ---
            const phraseBuilderSection = card.querySelector('.phrase-builder-section');
            const momentEditor = card.querySelector('.moment-editor');

            momentEditor.querySelector('input[name="token_ids"]').value = JSON.stringify(tokenIds);
            momentEditor.querySelector('input[name="phrase_string"]').value = phraseText;
            // Set the initial text, the autocomplete will handle the rest
            const notesTextarea = momentEditor.querySelector('textarea[name="notes"]');
            notesTextarea.value = phraseText.replace(framePattern, '').trim();
            momentEditor.querySelector('input[name="frame_start"]').value = frame_start;
            momentEditor.querySelector('input[name="frame_end"]').value = frame_end;
            momentEditor.querySelector('input[name="moment_date"]').value = moment_date;

            phraseBuilderSection.style.display = 'none';
            momentEditor.style.display = 'block';

            // Manually trigger the input event to get the perspectives to load
            notesTextarea.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // --- "Cancel" Button Logic ---
    document.querySelectorAll('.cancel-edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const card = this.closest('.worker-card');
            const phraseBuilderSection = card.querySelector('.phrase-builder-section');
            const momentEditor = card.querySelector('.moment-editor');

            momentEditor.style.display = 'none';
            phraseBuilderSection.style.display = 'block';
        });
    });

    // --- "Save Moment" Form Submission ---
    document.querySelectorAll('.moment-editor-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Saving...';

            fetch('/admin/ajax/save_moment_from_realtime.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'An unknown error occurred.'));
                    button.disabled = false;
                    button.textContent = 'Save Moment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected network error occurred.');
                button.disabled = false;
                button.textContent = 'Save Moment';
            });
        });
    });

    // --- Add Token Button Logic ---
    document.querySelectorAll('.add-token-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            const tokenForm = document.getElementById(`token-form-${workerId}`);
            
            // Toggle form visibility
            if (tokenForm.style.display === 'none' || !tokenForm.style.display) {
                tokenForm.style.display = 'block';
                tokenForm.querySelector('textarea[name="token_string"]').focus();
                
                // Pre-fill date with last used date if available
                const lastDate = localStorage.getItem('lastTokenDate');
                if (lastDate) {
                    tokenForm.querySelector('input[name="token_date"]').value = lastDate;
                }
            } else {
                tokenForm.style.display = 'none';
            }
        });
    });

    // --- Cancel Token Button Logic ---
    document.querySelectorAll('.cancel-token-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            const tokenForm = document.getElementById(`token-form-${workerId}`);
            
            // Hide form and reset
            tokenForm.style.display = 'none';
            const form = tokenForm.querySelector('.token-creation-form');
            form.reset();
        });
    });

    // --- Token Creation Form Submission ---
    document.querySelectorAll('.token-creation-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const workerId = this.dataset.workerId;
            const tokenString = this.querySelector('textarea[name="token_string"]').value.trim();
            const tokenDate = this.querySelector('input[name="token_date"]').value.trim();
            const tokenColor = this.querySelector('select[name="token_color"]').value;
            
            if (!tokenString) {
                alert('Token text cannot be empty.');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            // Store date for next time
            if (tokenDate) {
                localStorage.setItem('lastTokenDate', tokenDate);
            }
            
            // Use timestamp-based coordinates instead of X/Y positioning
            const timestamp = Math.floor(Date.now() / 1000); // Unix timestamp
            
            // Create FormData for the token
            const formData = new FormData();
            formData.append('action', 'create_for_worker');
            formData.append('worker_id', workerId);
            formData.append('token_string', tokenString);
            formData.append('token_date', tokenDate);
            formData.append('token_color', tokenColor);
            formData.append('token_x_pos', timestamp.toString());
            formData.append('token_y_pos', timestamp.toString());
            formData.append('token_width', Math.max(50, 10 + tokenString.length * 7).toString());
            formData.append('token_height', '30');
            
            fetch('/admin/ajax/tokens.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Hide form and reset
                    const tokenForm = document.getElementById(`token-form-${workerId}`);
                    tokenForm.style.display = 'none';
                    this.reset();
                    
                    // Add the new token to the available tokens container
                    addTokenToContainer(workerId, data.token);
                    
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.style.cssText = 'background: #d4edda; color: #155724; padding: 8px; border: 1px solid #c3e6cb; border-radius: 4px; margin-top: 10px;';
                    successMsg.textContent = 'Token created successfully!';
                    tokenForm.insertAdjacentElement('afterend', successMsg);
                    setTimeout(() => successMsg.remove(), 3000);
                } else {
                    alert('Error creating token: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the token. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    });

    function addTokenToContainer(workerId, token) {
        const container = document.getElementById(`available-tokens-${workerId}`);
        
        // Remove "No available tokens" message if it exists
        const noTokensMsg = container.querySelector('p');
        if (noTokensMsg && noTokensMsg.textContent.includes('No available tokens')) {
            noTokensMsg.remove();
        }
        
        // Create new token element
        const tokenElement = document.createElement('div');
        tokenElement.className = 'token-item';
        if (token.is_permanent) {
            tokenElement.classList.add('token-permanent');
        }
        tokenElement.dataset.tokenId = token.token_id;
        tokenElement.dataset.tokenDate = token.token_date || '';
        tokenElement.title = `Token ID: ${token.token_id}`;
        tokenElement.textContent = token.token_string;
        
        // Add to container
        container.appendChild(tokenElement);
        
        // No need to reinitialize SortableJS as it automatically detects new elements
    }

    // --- Perspective Loading Logic ---
    let debounceTimer;
    document.querySelectorAll('.shortcodey-textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const editor = this.closest('.moment-editor');
            debounceTimer = setTimeout(() => updatePerspectives(editor), 300);
        });
    });

    function updatePerspectives(editor) {
        const notesTextarea = editor.querySelector('.shortcodey-textarea');
        const perspectivesDiv = editor.querySelector('.perspective-fields');
        const text = notesTextarea.value;

        fetch('/admin/ajax/expand_shortcodes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'text=' + encodeURIComponent(text)
        })
        .then(response => response.json())
        .then(data => {
            perspectivesDiv.innerHTML = ''; // Clear previous perspectives
            if (data.perspectives && data.perspectives.length > 0) {
                const header = document.createElement('h3');
                header.textContent = 'Perspectives';
                perspectivesDiv.appendChild(header);

                data.perspectives.forEach(p => {
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
                    textarea.value = text;

                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.style.marginLeft = '10px';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = `perspectives[${p.type}][${p.id}][is_significant]`;
                    checkbox.value = '1';

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
            console.error('Fetch error:', error);
        });
    }
});