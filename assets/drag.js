document.addEventListener('DOMContentLoaded', function () {
    const draggables = document.querySelectorAll('.draggable-item');
    const groups = document.querySelectorAll('.sortable-group');

    let dragSrcEl = null;

    draggables.forEach(item => {
        item.addEventListener('dragstart', function (e) {
            dragSrcEl = this;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.url);
            this.classList.add('dragging');
        });

        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
        });
    });

    groups.forEach(group => {
        group.addEventListener('dragover', function (e) {
            e.preventDefault();
            const dragging = document.querySelector('.dragging');
            const afterElement = getDragAfterElement(this, e.clientY);
            if (afterElement == null) {
                this.appendChild(dragging);
            } else {
                this.insertBefore(dragging, afterElement);
            }
        });

        group.addEventListener('drop', function () {
            const urls = Array.from(this.querySelectorAll('.draggable-item')).map(item => item.dataset.url);
            const groupId = this.dataset.groupId;

            fetch('save_sort_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ group_id: groupId, new_order: urls })
            })
            .then(res => res.ok ? console.log('Sort order saved') : console.error('Failed to save sort order'))
            .catch(err => console.error(err));
        });
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.draggable-item:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return offset < 0 && offset > closest.offset
                ? { offset, element: child }
                : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
});
