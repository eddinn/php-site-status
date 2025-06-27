document.addEventListener('DOMContentLoaded', () => {
    const groups = document.querySelectorAll('.sortable-group');

    groups.forEach(group => {
        const items = group.querySelectorAll('.draggable-item');
        let dragging = null;

        items.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                dragging = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.url);
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });
        });

        group.addEventListener('dragover', (e) => {
            e.preventDefault();
            const draggingItem = group.querySelector('.dragging');
            const afterElement = getDragAfterElement(group, e.clientY);
            if (afterElement == null) {
                group.appendChild(draggingItem);
            } else {
                group.insertBefore(draggingItem, afterElement);
            }
        });

        group.addEventListener('drop', () => {
            const urls = Array.from(group.querySelectorAll('.draggable-item'))
                              .map(el => el.dataset.url);
            const groupId = group.dataset.groupId;

            fetch('save_sort_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ group_id: groupId, new_order: urls })
            }).then(res => {
                if (!res.ok) console.error('Failed to save order');
            });
        });
    });

    function getDragAfterElement(container, y) {
        const items = [...container.querySelectorAll('.draggable-item:not(.dragging)')];
        return items.reduce((closest, item) => {
            const box = item.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) 
                ? { offset, element: item }
                : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
});
