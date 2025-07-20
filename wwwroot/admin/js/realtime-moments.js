document.addEventListener('DOMContentLoaded', function () {
    // Initialize SortableJS for all worker sections
    document.querySelectorAll('.worker-card').forEach(card => {
        const workerId = card.querySelector('.create-moment-btn').dataset.workerId;
        const availableContainer = document.getElementById(`available-tokens-${workerId}`);
        const buildPhraseContainer = document.getElementById(`build-a-phrase-${workerId}`);

        if (!availableContainer || !buildPhraseContainer) {
            return; // Skip this worker if containers aren't found
        }

        // Make both containers sortable and part of the same group
        new Sortable(availableContainer, {
            group: {
                name: `worker-${workerId}`,
                pull: true,
                put: true
            },
            animation: 150,
            forceFallback: true,
            ghostClass: 'blue-background-class'
        });

        new Sortable(buildPhraseContainer, {
            group: {
                name: `worker-${workerId}`,
                pull: true,
                put: true
            },
            animation: 150,
            forceFallback: true,
            ghostClass: 'blue-background-class'
        });
    });

    // Add event listeners for the 'Create Moment' buttons
    document.querySelectorAll('.create-moment-btn').forEach(button => {
        button.addEventListener('click', function () {
            const workerId = this.dataset.workerId;
            const buildPhraseContainer = document.getElementById(`build-a-phrase-${workerId}`);
            const tokenItems = buildPhraseContainer.querySelectorAll('.token-item');

            if (tokenItems.length === 0) {
                alert('Please drag some tokens into the "Build-a-Phrase" box first.');
                return;
            }

            const tokenIds = Array.from(tokenItems).map(item => item.dataset.tokenId);

            if (confirm('Are you sure you want to create a moment from these tokens?')) {
                const formData = new FormData();
                formData.append('action', 'create_from_tokens');
                formData.append('token_ids', JSON.stringify(tokenIds));

                fetch('/admin/ajax/create_moment_from_tokens.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Moment created successfully!');
                        window.location.reload(); // Reload to see the changes
                    } else {
                        alert('Error: ' + (data.error || 'An unknown error occurred.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected network error occurred.');
                });
            }
        });
    });
});
