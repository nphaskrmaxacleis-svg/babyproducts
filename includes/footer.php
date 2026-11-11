</main>
<footer class="boutique-footer contact-store-footer">
<div><a class="brand" href="<?=url()?>">baby products</a><p><?=e(t('คัดสรรสินค้าแม่และเด็กด้วยความใส่ใจ'))?><br><?=e(t('เพื่อทุกวันของครอบครัว'))?></p><small><?=e(t('เว็บไซต์ตัวอย่างเพื่อการศึกษา สินค้าเป็นข้อมูลจำลอง และภาพสินค้าเป็นภาพสร้างด้วย AI'))?></small></div>
<div class="footer-contact"><strong><?=e(t('ติดต่อ Baby Products'))?></strong><dl><?php foreach(public_contact_channels() as $code=>$channel): ?><div><dt><?=e(t($channel['label']))?></dt><dd><?=e($channel['value'])?></dd></div><?php endforeach ?></dl><a href="<?=url('contact.php')?>"><?=e(t('ติดต่อเรา / ฝากข้อความถึงร้านค้า'))?></a></div>
<div><strong><?=e(t('บริการลูกค้า'))?></strong><a href="<?=url('terms.php#shipping')?>"><?=e(t('ข้อมูลการจัดส่ง'))?></a><a href="<?=url('terms.php#ordering')?>"><?=e(t('วิธีการสั่งซื้อ'))?></a><a href="<?=url('privacy.php')?>"><?=e(t('นโยบายความเป็นส่วนตัว'))?></a><a href="<?=url('terms.php')?>"><?=e(t('เงื่อนไขการให้บริการ'))?></a><a href="<?=url('privacy_request.php')?>"><?=e(t('คำขอข้อมูลส่วนบุคคล'))?></a></div>
<p>© <?=date('Y')?> Baby Products</p></footer></body></html>
