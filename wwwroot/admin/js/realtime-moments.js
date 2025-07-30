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
            hideEditorAndSearchResults(card);
        });
    });

    function hideEditorAndSearchResults(card) {
        const phraseBuilderSection = card.querySelector('.phrase-builder-section');
        const momentEditor = card.querySelector('.moment-editor');
        const searchResults = card.querySelector('.moment-search-results');

        momentEditor.style.display = 'none';
        searchResults.style.display = 'none';
        phraseBuilderSection.style.display = 'block';

        // Reset button states
        const saveBtn = momentEditor.querySelector('.save-moment-btn');
        saveBtn.textContent = 'Search for Moments';
        saveBtn.style.display = 'inline-block';
        momentEditor.querySelector('.create-new-moment-btn').style.display = 'none';
    }

    // --- Moment Editor Form Submission (now SEARCH) ---
    document.querySelectorAll('.moment-editor-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const workerId = this.dataset.workerId;
            const card = this.closest('.worker-card');
            const frame_start = this.querySelector('input[name="frame_start"]').value;
            const frame_end = this.querySelector('input[name="frame_end"]').value;

            if (!frame_start || !frame_end) {
                alert('Please enter both a start and end frame.');
                return;
            }

            const formData = new FormData();
            formData.append('worker_id', workerId);
            formData.append('frame_start', frame_start);
            formData.append('frame_end', frame_end);

            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Searching...';

            fetch('/admin/ajax/search_moments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMomentSearchResults(card, data.moments);
                } else {
                    alert('Error: ' + (data.error || 'An unknown error occurred.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected network error occurred.');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Search for Moments';
            });
        });
    });

    function displayMomentSearchResults(card, moments) {
        const searchResultsContainer = card.querySelector('.moment-search-results');
        const resultsDiv = searchResultsContainer.querySelector('.results-container');
        const momentEditor = card.querySelector('.moment-editor');
        resultsDiv.innerHTML = '';

        if (moments.length > 0) {
            const list = document.createElement('ul');
            list.style.listStyleType = 'none';
            list.style.paddingLeft = '0';

            moments.forEach(moment => {
                const item = document.createElement('li');
                item.style.marginBottom = '10px';
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'selected_moment';
                radio.value = moment.moment_id;
                radio.id = `moment-${moment.moment_id}`;
                radio.addEventListener('change', () => {
                    card.querySelector('.use-selected-moment-btn').disabled = false;
                });

                const label = document.createElement('label');
                label.htmlFor = `moment-${moment.moment_id}`;
                label.style.marginLeft = '10px';
                label.innerHTML = `<strong>ID ${moment.moment_id}:</strong> [${moment.frame_start}-${moment.frame_end}] <em>${moment.notes}</em>`;

                item.appendChild(radio);
                item.appendChild(label);
                list.appendChild(item);
            });
            resultsDiv.appendChild(list);
        } else {
            resultsDiv.innerHTML = '<p>No similar moments found.</p>';
        }

        searchResultsContainer.style.display = 'block';
        momentEditor.querySelector('.save-moment-btn').style.display = 'none';
        momentEditor.querySelector('.create-new-moment-btn').style.display = 'inline-block';
    }

    // --- "Create New Moment" Button ---
    document.querySelectorAll('.create-new-moment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            saveMoment(form);
        });
    });

    function saveMoment(form) {
        const formData = new FormData(form);
        const button = form.querySelector('.create-new-moment-btn');
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
                button.textContent = 'Create New Moment';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected network error occurred.');
            button.disabled = false;
            button.textContent = 'Create New Moment';
        });
    }

    // --- "Use Selected Moment" Button ---
    document.querySelectorAll('.use-selected-moment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.worker-card');
            const selectedRadio = card.querySelector('input[name="selected_moment"]:checked');
            if (!selectedRadio) {
                alert('Please select a moment to use.');
                return;
            }
            const moment_id = selectedRadio.value;
            const momentEditor = card.querySelector('.moment-editor');
            const token_ids = momentEditor.querySelector('input[name="token_ids"]').value;
            const phrase_string = momentEditor.querySelector('input[name="phrase_string"]').value;

            createPhraseForExistingMoment(this, { moment_id, token_ids, phrase_string });
        });
    });

    function createPhraseForExistingMoment(button, data) {
        button.disabled = true;
        button.textContent = 'Saving...';

        const formData = new FormData();
        formData.append('moment_id', data.moment_id);
        formData.append('token_ids', data.token_ids);
        formData.append('phrase_string', data.phrase_string);

        fetch('/admin/ajax/create_phrase_for_moment.php', {
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
                button.textContent = 'Use Selected Moment & Create Phrase';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected network error occurred.');
            button.disabled = false;
            button.textContent = 'Use Selected Moment & Create Phrase';
        });
    }


    // --- Add Token Button Logic ---
    document.querySelectorAll('.add-token-btn').forEach(button => {
        button.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            const tokenForm = document.getElementById(`token-form-${workerId}`);

            // Toggle form visibility
            if (tokenForm.style.display === 'none' || !tokenForm.style.display) {
                tokenForm.style.display = 'block';
                tokenForm.querySelector('textarea[name="token_string"]').focus();
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
            const formData = new FormData(this);
            formData.append('action', 'create_for_worker');
            formData.append('worker_id', workerId);

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            fetch('/admin/ajax/tokens.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide form and reset
                    const tokenForm = document.getElementById(`token-form-${workerId}`);
                    tokenForm.style.display = 'none';
                    this.reset();

                    // Add the new token to the available tokens container
                    addTokenToContainer(workerId, data.token);
                } else {
                    alert('Error creating token: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the token.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Token';
            });
        });
    });

    function addTokenToContainer(workerId, token) {
        const container = document.getElementById(`available-tokens-${workerId}`);

        // Remove "No available tokens" message if it exists
        const noTokensMsg = container.querySelector('p');
        if (noTokensMsg) noTokensMsg.remove();

        // Create new token element
        const tokenElement = document.createElement('div');
        tokenElement.className = 'token-item';
        if (token.is_permanent) tokenElement.classList.add('token-permanent');
        tokenElement.dataset.tokenId = token.token_id;
        tokenElement.dataset.tokenDate = token.token_date || '';
        tokenElement.title = `Token ID: ${token.token_id}`;
        tokenElement.textContent = token.token_string;
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

                    // Add unused photos if they exist (for workers)
                    if (p.type === 'worker' && p.unused_photos && p.unused_photos.length > 0) {
                        const photosContainer = document.createElement('div');
                        photosContainer.className = 'unused-photos-container';
                        photosContainer.style.cssText = 'margin: 5px 0; display: flex; gap: 5px; flex-wrap: wrap;';

                        p.unused_photos.forEach(photo => {
                            const photoThumb = document.createElement('img');
                            photoThumb.src = photo.thumbnail_url; // Use thumbnail for display
                            photoThumb.style.cssText = 'width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;';
                            photoThumb.title = `Photo ID: ${photo.photo_id} - Click to copy full-size URL to clipboard`;
                            photoThumb.dataset.photoId = photo.photo_id;
                            photoThumb.dataset.fullUrl = photo.full_url;

                            // Click handler to copy full-size URL to clipboard
                            photoThumb.addEventListener('click', async function() {
                                try {
                                    await navigator.clipboard.writeText(photo.full_url);
                                    // Visual feedback
                                    const originalTitle = this.title;
                                    this.title = 'Copied to clipboard!';
                                    this.style.border = '2px solid #28a745';
                                    setTimeout(() => {
                                        this.title = originalTitle;
                                        this.style.border = '1px solid #ddd';
                                    }, 1500);
                                } catch (err) {
                                    console.error('Failed to copy to clipboard:', err);
                                    // Fallback: show the URL in an alert so user can copy manually
                                    prompt('Copy this URL:', photo.full_url);
                                }
                            });

                            photosContainer.appendChild(photoThumb);
                        });

                        label.appendChild(photosContainer);
                    } else if (p.type === 'worker') {
                        console.log(`Note: Worker ${p.name} has no unused photos:`, p.unused_photos);
                    }

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
        .catch(error => console.error('Fetch error:', error));
    }
});
