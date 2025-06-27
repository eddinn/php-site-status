<?php
$version_file = __DIR__ . '/../version.txt';
$version = file_exists($version_file) ? trim(file_get_contents($version_file)) : '0.0.1';
$git_hash = trim(shell_exec('git rev-parse --short HEAD') ?? 'unknown');
$date_str = date('d/m/Y');
?>
    <footer class="text-center mt-5 text-muted small">
        <hr>
        Version: <?= e($version) ?> <?= e($git_hash) ?> — <?= e($date_str) ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js" defer></script>
</body>
</html>
