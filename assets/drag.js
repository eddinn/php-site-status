document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('#group-container')?.dataset.csrf;

    // Group drag/sort
    const groupContainer = document.querySelector('#group-container');
    if (groupContainer) {
        new Sortable(groupContainer, {
            animation: 150,
            handle: '.card-header',
            onEnd: () => {
                const groupIds = Array.from(groupContainer.querySelectorAll('.group-card'))
                    .map(card => card.dataset.groupId);
                fetch('save_group_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: new URLSearchParams({
                        group_ids: groupIds
                    })
                }).catch(err => console.error('Group order save failed', err));
            }
        });
    }

    // Service drag/sort (per group)
    document.querySelectorAll('.sortable-services').forEach(ul => {
        const groupId = ul.dataset.groupId;
        new Sortable(ul, {
            animation: 150,
            handle: '.service-title-row',
            onEnd: () => {
                const indexes = Array.from(ul.querySelectorAll('.service-item'))
                    .map(li => li.dataset.serviceIndex);
                fetch('save_sort_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: new URLSearchParams({
                        group_id: groupId,
                        service_order: indexes
                    })
                }).catch(err => console.error('Service sort save failed', err));
            }
        });
    });
});
