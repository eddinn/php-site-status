document.addEventListener("DOMContentLoaded", () => {
    const intervalSelect = document.getElementById("refreshInterval");
    const darkToggle = document.getElementById("darkToggle");
    const offlineToggle = document.getElementById("offlineToggle");
    const exportJsonBtn = document.createElement("button");
    const exportMdBtn = document.createElement("button");

    exportJsonBtn.innerText = "Export JSON";
    exportJsonBtn.className = "btn btn-outline-primary ms-2";
    exportMdBtn.innerText = "Export Markdown";
    exportMdBtn.className = "btn btn-outline-secondary ms-2";
    document.querySelector(".d-flex").append(exportJsonBtn, exportMdBtn);

    let refreshTimer = null;
    let showOnlyOffline = false;

    function updateStatus() {
        const serviceItems = document.querySelectorAll(".service-item");
        const groupOnlineCount = {};
        const groupStatusMap = {};

        serviceItems.forEach(item => {
            const url = item.dataset.url;
            const group = item.dataset.group;
            const badge = document.getElementById(`${group}_badge`);

            fetch(`check_service.php?url=${encodeURIComponent(url)}`)
                .then(res => res.json())
                .then(data => {
                    const isOnline = data.online;
                    const title = data.title || "No Title";
                    const dot = item.querySelector(".status-dot");

                    dot.className = "status-dot " + (isOnline ? "dot-online" : "dot-offline");
                    item.className = `list-group-item service-item ${isOnline ? "bg-online" : "bg-offline"}`;
                    item.querySelector("strong").innerText = title;
                    item.dataset.status = isOnline ? "online" : "offline";

                    if (!groupOnlineCount[group]) groupOnlineCount[group] = { online: 0, total: 0 };
                    if (!groupStatusMap[group]) groupStatusMap[group] = [];

                    groupStatusMap[group].push({ element: item, online: isOnline });
                    if (isOnline) groupOnlineCount[group].online++;
                    groupOnlineCount[group].total++;

                    item.style.display = showOnlyOffline && isOnline ? "none" : "block";

                    if (groupOnlineCount[group].total === document.querySelectorAll(`[data-group="${group}"]`).length) {
                        badge.innerText = `${groupOnlineCount[group].online} / ${groupOnlineCount[group].total}`;
                        sortGroup(groupStatusMap[group], group);
                    }
                })
                .catch(() => {
                    item.className = "list-group-item service-item bg-offline";
                    item.querySelector("strong").innerText = "Unreachable";
                    item.querySelector(".status-dot").className = "status-dot dot-offline";
                    item.dataset.status = "offline";
                    if (showOnlyOffline) item.style.display = "block";
                });
        });
    }

    function sortGroup(statuses, group) {
        const list = document.querySelector(`[data-group="${group}"] ul`);
        const sorted = statuses.sort((a, b) => a.online === b.online ? 0 : a.online ? 1 : -1);
        sorted.forEach(s => list.appendChild(s.element));
    }

    function exportData(asMarkdown = false) {
        const groups = document.querySelectorAll(".group-card");
        const exportData = {};
        let md = "# Homelab Service Status\n\n";

        groups.forEach(group => {
            const groupName = group.querySelector(".card-header span").innerText;
            const services = group.querySelectorAll(".service-item");
            exportData[groupName] = [];

            md += `## ${groupName}\n`;

            services.forEach(service => {
                const url = service.dataset.url;
                const title = service.querySelector("strong").innerText;
                const status = service.dataset.status || "unknown";
                exportData[groupName].push({
                    title,
                    url,
                    status
                });

                md += `- [${title}](${url}) - ${status === "online" ? "🟢 Online" : "🔴 Offline"}\n`;
            });

            md += `\n`;
        });

        if (asMarkdown) {
            downloadFile("homelab_status.md", md);
        } else {
            downloadFile("homelab_status.json", JSON.stringify(exportData, null, 2));
        }
    }

    function downloadFile(filename, content) {
        const blob = new Blob([content], { type: "text/plain" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
    }

    // Auto-refresh control
    intervalSelect.addEventListener("change", () => {
        if (refreshTimer) clearInterval(refreshTimer);
        const interval = parseInt(intervalSelect.value);
        if (interval > 0) refreshTimer = setInterval(updateStatus, interval);
    });

    darkToggle.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem("dark-mode", document.body.classList.contains("dark-mode"));
    });

    offlineToggle.addEventListener("click", () => {
        showOnlyOffline = !showOnlyOffline;
        offlineToggle.classList.toggle("btn-outline-danger", !showOnlyOffline);
        offlineToggle.classList.toggle("btn-outline-secondary", showOnlyOffline);
        offlineToggle.innerText = showOnlyOffline ? "Show All Services" : "Show Only Offline";
        updateStatus();
    });    

    exportJsonBtn.addEventListener("click", () => exportData(false));
    exportMdBtn.addEventListener("click", () => exportData(true));

    if (localStorage.getItem("dark-mode") === "true") {
        document.body.classList.add("dark-mode");
    }

    intervalSelect.dispatchEvent(new Event("change"));
    updateStatus();
});
