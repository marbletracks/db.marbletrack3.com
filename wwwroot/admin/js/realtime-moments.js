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

            // --- 1. Prepare Data for Editor ---
            const tokenIds = Array.from(tokenItems).map(item => item.dataset.tokenId);
            const phraseText = Array.from(tokenItems).map(item => item.textContent.trim()).join(' ');
            let notes = phraseText;
            let frame_start = '';
            let frame_end = '';

            const framePattern = /\s+(\d+)\s*[-~]?\s*(\d+)$/;
            const match = phraseText.match(framePattern);
            if (match) {
                notes = phraseText.replace(framePattern, '').trim();
                frame_start = match[1];
                frame_end = match[2];
            }

            const lastToken = tokenItems[tokenItems.length - 1];
            let moment_date = lastToken.dataset.tokenDate;
            if (!moment_date) {
                moment_date = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
            }

            // --- 2. Populate and Show Editor ---
            const phraseBuilderSection = card.querySelector('.phrase-builder-section');
            const momentEditor = card.querySelector('.moment-editor');

            momentEditor.querySelector('input[name="token_ids"]').value = JSON.stringify(tokenIds);
            momentEditor.querySelector('input[name="phrase_string"]').value = phraseText;
            momentEditor.querySelector('textarea[name="notes"]').value = notes;
            momentEditor.querySelector('input[name="frame_start"]').value = frame_start;
            momentEditor.querySelector('input[name="frame_end"]').value = frame_end;
            momentEditor.querySelector('input[name="moment_date"]').value = moment_date;

            phraseBuilderSection.style.display = 'none';
            momentEditor.style.display = 'block';

            // --- 3. Trigger Perspective Loading ---
            updatePreviewAndPerspectives(momentEditor);
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
                    // Reload the page to show the new moment and updated token list
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

    // --- Perspective and Preview Logic (Adapted from moment.tpl.php) ---
    let debounceTimer;
    document.querySelectorAll('.shortcodey-textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const editor = this.closest('.moment-editor');
            debounceTimer = setTimeout(() => updatePreviewAndPerspectives(editor), 300);
        });
    });

    function updatePreviewAndPerspectives(editor) {
        const notesTextarea = editor.querySelector('.shortcodey-textarea');
        const previewDiv = editor.querySelector('.notes-preview');
        const perspectivesDiv = editor.querySelector('.perspective-fields');
        const text = notesTextarea.value;

        fetch('/admin/ajax/expand_shortcodes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'text=' + encodeURIComponent(text)
        })
        .then(response => response.json())
        .then(data => {
            previewDiv.innerHTML = data.expanded_text || '';
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
                    textarea.value = text; // Default to the main note text

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
            previewDiv.innerHTML = '<span style="color: red;">Error loading preview.</span>';
            console.error('Fetch error:', error);
        });
    }
});