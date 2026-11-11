<?php
require_once __DIR__.'/includes/functions.php';
http_response_code(404);
require __DIR__.'/includes/header.php';
?>
<section class="empty" aria-labelledby="not-found-title"><p class="eyebrow">404</p><h1 id="not-found-title"><?=e(t('ไม่พบหน้าที่คุณต้องการ'))?></h1><p><?=e(t('หน้านี้อาจถูกย้าย หรือที่อยู่เว็บไซต์อาจไม่ถูกต้อง'))?><br><?=e(t('เลือกดูสินค้า หรือกลับไปเริ่มต้นที่หน้าหลักได้เลย'))?></p><a class="button" href="<?=url()?>"><?=e(t('กลับหน้าหลัก'))?></a> <a class="button secondary" href="<?=url('products.php')?>"><?=e(t('เลือกดูสินค้า'))?></a><p style="margin-top:24px"><a href="<?=url('contact.php')?>"><?=e(t('ติดต่อร้านค้า'))?></a></p></section>
<?php require __DIR__.'/includes/footer.php'; ?>
