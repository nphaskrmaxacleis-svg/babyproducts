<?php
require 'bootstrap.php';$search=is_string($_GET['q']??null)?trim($_GET['q']):'';$status=is_string($_GET['status']??null)?$_GET['status']:'';$payment=is_string($_GET['payment']??null)?$_GET['payment']:'';
$where=[];$args=[];
if($search!==''){$number=preg_replace('/^BP-0*/i','',$search);$where[]='(recipient LIKE ? OR id=?)';$args[]='%'.$search.'%';$args[]=ctype_digit($number)?(int)$number:0;}
if(isset(STATUSES[$status])){$where[]='status=?';$args[]=$status;}
if(isset(PAYMENT_STATUSES[$payment])){$where[]='payment_status=?';$args[]=$payment;}
$filter=$where?' WHERE '.implode(' AND ',$where):'';$count=(int)q('SELECT COUNT(*) FROM orders'.$filter,$args)->fetchColumn();$pages=max(1,(int)ceil($count/25));$page=max(1,min($pages,(int)($_GET['page']??1)));$orders=q('SELECT * FROM orders'.$filter.' ORDER BY id DESC LIMIT 25 OFFSET '.(($page-1)*25),$args)->fetchAll();
$pending=(int)q('SELECT COUNT(*) FROM orders WHERE payment_status="pending_review"')->fetchColumn();admin_header('คำสั่งซื้อ');
?>
<p><a href="?payment=pending_review">สลิปรอตรวจสอบ <?=$pending?> <?=e(t('รายการ'))?></a> · <a href="<?=url('admin/payment_settings.php')?>">ตั้งค่าบัญชีธนาคาร</a></p>
<form class="filters"><input name="q" placeholder="BP-000001 หรือชื่อผู้รับ" value="<?=e($search)?>"><select name="status"><option value="">ทุกสถานะคำสั่งซื้อ</option><?php foreach(STATUSES as $key=>$label): ?><option value="<?=$key?>" <?=$status===$key?'selected':''?>><?=e(t($label))?></option><?php endforeach ?></select><select name="payment"><option value="">ทุกสถานะชำระเงิน</option><?php foreach(PAYMENT_STATUSES as $key=>$label): ?><option value="<?=$key?>" <?=$payment===$key?'selected':''?>><?=e(t($label))?></option><?php endforeach ?></select><button><?=e(t('ค้นหา'))?></button></form><p>พบ <?=$count?> <?=e(t('รายการ'))?></p>
<div class="table-wrap"><table><thead><tr><th>เลขที่ / วันที่</th><th>ผู้รับ</th><th><?=e(t('ยอดรวม'))?></th><th>วิธีชำระ</th><th><?=e(t('คำสั่งซื้อ'))?></th><th>การชำระเงิน</th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><a href="<?=url('admin/order_detail.php?id='.$o['id'])?>">BP-<?=sprintf('%06d',$o['id'])?></a><small class="message-email"><?=e($o['created_at'])?></small></td><td><?=e($o['recipient'])?></td><td><?=money($o['total'])?></td><td><?=payment_method_label($o)?></td><td><?=order_badge($o)?></td><td><?=payment_badge($o)?></td></tr><?php endforeach ?></tbody></table></div>
<div class="pagination"><?php for($number=1;$number<=$pages;$number++): ?><a href="?<?=e(http_build_query(['q'=>$search,'status'=>$status,'payment'=>$payment,'page'=>$number]))?>"><?=$number?></a><?php endfor ?></div>
<?php admin_footer(); ?>
