<?php
ob_start();
$icon = getStatic('noprint.svg');
echo <<<HTML
<div class="d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <div class="display-1 fw-bold">{$icon}</div>
        <p class="fs-5" style="font-size: 14pt"> <span class="text-danger">Yah!</span> anda belum memasukan data ke dalam antrian cetak!</p>
    </div>
</div>
HTML;
$content = ob_get_clean();
include SB . 'admin' . DS . 'admin_template' . DS . 'notemplate_page_tpl.php';