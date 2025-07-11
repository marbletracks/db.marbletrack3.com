document.addEventListener('DOMContentLoaded', function () {
    console.log('sortable-moments.js loaded');
    const sortableList = document.getElementById('sortable-moments');
    let draggedItem = null;

    if (sortableList) {
        console.log('Sortable list found', sortableList);
        sortableList.addEventListener('mousedown', e => {
            if (e.target.classList.contains('drag-handle')) {
                console.log('Mousedown on a drag handle');
            }
        });

        sortableList.addEventListener('dragstart', e => {
            draggedItem = e.target;
            setTimeout(() => {
                draggedItem.classList.add('dragging');
            }, 0);
            console.log('Drag started:', draggedItem);
        });

        sortableList.addEventListener('dragend', e => {
            console.log('Drag ended');
            if (draggedItem) {
                draggedItem.classList.remove('dragging');
                draggedItem = null;
                updateHiddenInput();
            }
        });

        sortableList.addEventListener('dragover', e => {
            e.preventDefault();
            if (draggedItem) {
                const afterElement = getDragAfterElement(sortableList, e.clientY);
                if (afterElement == null) {
                    sortableList.appendChild(draggedItem);
                } else {
                    sortableList.insertBefore(draggedItem, afterElement);
                }
            }
        });
    } else {
        console.log('Sortable list not found');
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

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-moment')) {
            const listItem = e.target.closest('li');
            console.log('Removing moment:', listItem.dataset.momentId);
            const momentId = listItem.dataset.momentId;
            const notes = listItem.querySelector('a').textContent.trim();

            listItem.remove();

            const select = document.getElementById('add-moment-select');
            const option = document.createElement('option');
            option.value = momentId;
            option.dataset.notes = notes;
            option.textContent = notes;
            select.appendChild(option);
            updateHiddenInput();
        }
    });

    const addMomentSelect = document.getElementById('add-moment-select');
    if (addMomentSelect) {
        addMomentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) return;

            const momentId = selectedOption.value;
            const notes = selectedOption.dataset.notes;
            const frameStart = selectedOption.dataset.frameStart;
            const frameEnd = selectedOption.dataset.frameEnd;
            const momentDate = selectedOption.dataset.momentDate;
            console.log('Adding moment:', momentId);

            const newLi = document.createElement('li');
            newLi.dataset.momentId = momentId;
            newLi.draggable = true;

            let frameHTML = '';
            if (frameStart || frameEnd) {
                frameHTML = ` (Frames: ${frameStart || '?'} - ${frameEnd || '?'})`;
            }
            let dateHTML = '';
            if (momentDate) {
                dateHTML = ` (${momentDate})`;
            } else {
                dateHTML = ` (--/--/----)`;
            }

            newLi.innerHTML = `
                <div class="drag-handle">⋮⋮</div>
                ${dateHTML} <a href="/admin/moments/moment.php?id=${momentId}">${notes}</a> ${frameHTML}
                <button type="button" class="remove-moment">Remove</button>
            `;

            let list = document.getElementById('sortable-moments');
            if (!list) {
                console.log('Creating sortable list because it does not exist.');
                list = document.createElement('ul');
                list.id = 'sortable-moments';
                const h2 = document.createElement('h2');
                h2.textContent = 'Associated Moments';
                const form = document.querySelector('form');
                form.insertBefore(h2, addMomentSelect.parentElement);
                form.insertBefore(list, h2.nextSibling);
            }

            list.appendChild(newLi);
            selectedOption.remove();
            updateHiddenInput();
        });
    }

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
        console.log('Updated hidden input:', momentIds.join(','));
    }
});
