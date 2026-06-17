<?php

$back = $_SERVER['HTTP_REFERER'] ?? '../index.php';

?>

<a href="<?= $back ?>" class="back-btn">
    ← Kembali
</a>