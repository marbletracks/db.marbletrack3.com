function initializeSignificanceUpdater(perspectiveId, perspectiveType) {
    if (!perspectiveId) return;

    document.querySelectorAll('.is-significant-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const momentId = this.dataset.momentId;
            const isSignificant = this.checked;
            const listItem = this.closest('li');

            listItem.classList.add('saving');

            const formData = new FormData();
            formData.append('moment_id', momentId);
            formData.append('perspective_id', perspectiveId);
            formData.append('perspective_type', perspectiveType);
            formData.append('is_significant', isSignificant);

            fetch('/admin/ajax/update_moment_significance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Error saving significance.');
                    // Revert checkbox on failure
                    this.checked = !isSignificant;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('A network error occurred.');
                this.checked = !isSignificant;
            })
            .finally(() => {
                // Remove the visual indicator
                setTimeout(() => listItem.classList.remove('saving'), 250);
            });
        });
    });
}
