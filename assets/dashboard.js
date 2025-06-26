document.addEventListener("DOMContentLoaded", async () => {
    const dashboard = document.getElementById("service-dashboard");
    const darkToggle = document.getElementById("toggle-dark");
    const offlineToggle = document.getElementById("toggle-offline");
    const exportMd = document.getElementById("export-md");
    const exportJson = document.getElementById("export-json");

    const isAdmin = document.body.dataset.admin === "1";
    const csrf = document.querySelector("meta[name='csrf']").content;

    let darkMode = localStorage.getItem("darkMode") === "true";
    let showOnlyOffline = false;
    let serviceData = {};
    let statusCache = {};

    function setDarkMode(enabled) {
        document.body.classList.toggle("dark-mode", enabled);
        document.body.classList.toggle("light-mode", !enabled);
        darkToggle.textContent = enabled ? "🌞 Light Mode" : "🌓 Dark Mode";
        localStorage.setItem("darkMode", enabled);
        darkMode = enabled;
    }

    function getFallbackName(url) {
        try {
            const u = new URL(url);
            return u.hostname;
        } catch {
            return "Unnamed Service";
        }
    }

    function render() {
        dashboard.innerHTML = "";

        Object.entries(serviceData).forEach(([group, urls], groupIndex) => {
            const groupCol = document.createElement("div");
            groupCol.className = "col-md-6 group-card";
            groupCol.dataset.group = group;

            const groupCard = document.createElement("div");
            groupCard.className = "card h-100";
            groupCard.innerHTML = `<div class="card-header"><strong class="drag-handle">☰ ${group}</strong></div>`;

            const list = document.createElement("ul");
            list.className = "list-group list-group-flush sortable-services";
            list.dataset.group = group;

            urls.forEach((url, index) => {
                const status = statusCache[url];
                const isOffline = status && !status.online;
                if (showOnlyOffline && (!status || status.online)) return;

                const name = status?.title?.trim() || getFallbackName(url);
                const item = document.createElement("li");
                item.className = `list-group-item d-flex justify-content-between align-items-center ${status ? (status.online ? "bg-success bg-opacity-10" : "bg-danger bg-opacity-10") : ""}`;
                item.dataset.url = url;

                const linkHtml = `
                    <span class="me-2 ${status ? (status.online ? "text-success" : "text-danger") : ""}">●</span>
                    <strong class="me-3">${name}</strong>
                    <a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>
                `;

                const editBtn = isAdmin
                    ? `<a href="edit_service.php?group=${encodeURIComponent(group)}&index=${index}" class="btn btn-sm btn-outline-secondary ms-3" title="Edit"><span style="font-size: 1rem;">✏️</span></a>`
                    : "";

                item.innerHTML = `
                    <div class="d-flex align-items-center flex-grow-1">
                        ${linkHtml}
                    </div>
                    <div>${editBtn}</div>
                `;
                list.appendChild(item);
            });

            groupCard.appendChild(list);
            groupCol.appendChild(groupCard);
            dashboard.appendChild(groupCol);

            if (isAdmin) {
                new Sortable(list, {
                    animation: 150,
                    handle: ".drag-handle",
                    onEnd: () => saveGroupOrder(group, list)
                });
            }
        });

        if (isAdmin) {
            new Sortable(dashboard, {
                animation: 200,
                handle: ".drag-handle",
                draggable: ".group-card",
                onEnd: () => saveGroupLayout()
            });
        }
    }

    function checkService(url) {
        return fetch(`/ping.php?url=${encodeURIComponent(url)}`)
            .then(res => res.ok ? res.text() : "")
            .then(html => {
                const titleMatch = html.match(/<title>(.*?)<\/title>/i);
                return { online: true, title: titleMatch ? titleMatch[1].trim() : null };
            })
            .catch(() => ({ online: false, title: null }));
    }

    function updateStatuses() {
        const checks = [];
        for (const urls of Object.values(serviceData)) {
            for (const url of urls) {
                checks.push(
                    checkService(url).then(status => {
                        statusCache[url] = status;
                    })
                );
            }
        }

        Promise.all(checks).then(render);
    }

    function saveGroupLayout() {
        const order = Array.from(dashboard.querySelectorAll(".group-card")).map(col => col.dataset.group);
        fetch("save_group_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ csrf, order })
        });
    }

    function saveGroupOrder(group, listEl) {
        const urls = Array.from(listEl.children).map(li => li.dataset.url);
        fetch("save_sort_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ csrf, group, order: urls })
        });
    }

    darkToggle.onclick = () => {
        setDarkMode(!darkMode);
    };

    offlineToggle.onclick = () => {
        showOnlyOffline = !showOnlyOffline;
        offlineToggle.classList.toggle("btn-danger", showOnlyOffline);
        offlineToggle.classList.toggle("btn-outline-light", !showOnlyOffline);
        offlineToggle.textContent = showOnlyOffline ? "🟢 Show All" : "🔴 Show Offline";
        render();
    };

    exportMd.onclick = () => {
        let output = `# Service Status – ${new Date().toISOString()}\n\n`;
        for (const [group, urls] of Object.entries(serviceData)) {
            output += `## ${group}\n`;
            urls.forEach(url => {
                const status = statusCache[url];
                const name = status?.title || getFallbackName(url);
                output += `- ${status?.online ? "✅" : "❌"} [${name}](${url})\n`;
            });
            output += "\n";
        }
        const blob = new Blob([output], { type: "text/markdown" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "services.md";
        link.click();
    };

    exportJson.onclick = () => {
        const blob = new Blob([JSON.stringify(serviceData, null, 2)], { type: "application/json" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "services.json";
        link.click();
    };

    try {
        const response = await fetch("services.json");
        serviceData = await response.json();
        render();
        updateStatuses();
    } catch (e) {
        dashboard.innerHTML = "<div class='alert alert-danger'>Failed to load services.</div>";
    }

    setDarkMode(darkMode);
});
