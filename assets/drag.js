document.addEventListener("DOMContentLoaded", () => {
    const csrf = document.querySelector('meta[name="csrf"]').content;

    document.querySelectorAll(".sortable-group").forEach(groupEl => {
        const group = groupEl.dataset.group;

        new Sortable(groupEl, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function () {
                const serviceUrls = Array.from(groupEl.querySelectorAll("[data-url]"))
                    .map(item => item.getAttribute("data-url"));

                fetch("save_sort_order.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        csrf,
                        group,
                        order: serviceUrls
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) alert("Failed to save sort order.");
                });
            }
        });
    });
});
