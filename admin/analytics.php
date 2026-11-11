<?php
require __DIR__.'/bootstrap.php';require_once __DIR__.'/../includes/analytics.php';
$data=analytics_read();krsort($data);$totals=[];$sum=0;
foreach($data as $pages)foreach($pages as $page=>$count){$totals[$page]=($totals[$page]??0)+(int)$count;$sum+=(int)$count;}
arsort($totals);admin_header('สถิติการเข้าชมเว็บไซต์');
?>
<div class="notice">นับจำนวนการเปิดหน้าสาธารณะในช่วง 90 วันล่าสุด ไม่ใช่จำนวนคน ไม่เก็บ IP ตัวตน คุกกี้ติดตาม หรือข้อมูลในช่องค้นหา การรีเฟรชและบอตอาจถูกนับรวม</div>
<section class="panel"><h2>การเปิดหน้าทั้งหมด <?=number_format($sum)?> ครั้ง</h2><p>สถิติภายในร้านค้า ไม่ได้ติดตั้ง Google Analytics หรือ Facebook Pixel และไม่ส่งข้อมูลออกไปยังผู้ให้บริการวิเคราะห์ภายนอก</p></section>
<div class="two-col"><section class="panel"><h2>แยกตามหน้า</h2><?php if(!$totals): ?><p>ยังไม่มีข้อมูล หรือระบบยังไม่สามารถบันทึกไฟล์สถิติได้</p><?php endif ?><?php foreach($totals as $page=>$count): ?><p><?=e($page)?> <strong><?=number_format($count)?> ครั้ง</strong></p><?php endforeach ?></section><section class="panel"><h2>แยกตามวัน</h2><?php foreach($data as $day=>$pages): ?><p><?=e($day)?> <strong><?=number_format(array_sum($pages))?> ครั้ง</strong></p><?php endforeach ?></section></div>
<?php admin_footer(); ?>
