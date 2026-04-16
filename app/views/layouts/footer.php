</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
$jsAssetPath = dirname(__DIR__, 3) . '/public/assets/js/app.js';
$jsVersion = file_exists($jsAssetPath) ? filemtime($jsAssetPath) : time();
?>
<script src="<?= ASSETS_URL ?>/js/app.js?v=<?= $jsVersion ?>"></script>
</body>
</html>
