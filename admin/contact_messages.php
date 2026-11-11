<?php
require 'bootstrap.php';
$status=is_string($_GET['status']??null)?$_GET['status']:'';
if(!array_key_exists($status,CONTACT_MESSAGE_STATUSES))$status='';
$search=trim(is_string($_GET['q']??null)?$_GET['q']:'');
$where=[];$params=[];
if($status!==''){$where[]='status=?';$params[]=$status;}
if($search!==''){$where[]='(name LIKE ? OR email LIKE ? OR subject LIKE ?)';$params=array_merge($params,array_fill(0,3,'%'.$search.'%'));}
$filter=$where?' WHERE '.implode(' AND ',$where):'';
$count=(int)q('SELECT COUNT(*) FROM contact_messages'.$filter,$params)->fetchColumn();
$pages=max(1,(int)ceil($count/20));$page=max(1,min($pages,(int)($_GET['page']??1)));$offset=($page-1)*20;
$messages=q('SELECT id,name,email,subject,status,created_at FROM contact_messages'.$filter.' ORDER BY id DESC LIMIT 20 OFFSET '.$offset,$params)->fetchAll();
admin_header('ข้อความจากลูกค้า');
?>
<div class="contact-admin-toolbar"><p>พบ <?=$count?> ข้อความ</p><a href="<?=url('admin/contact.php')?>"><?=e(t('จัดการข้อมูลติดต่อ'))?></a></div>
<form method="get" class="filters"><input name="q" value="<?=e($search)?>" placeholder="ค้นหาชื่อ อีเมล หรือหัวข้อ" aria-label="ค้นหาข้อความ"><select name="status" aria-label="กรองสถานะ"><option value="">ทุกสถานะ</option><?php foreach(CONTACT_MESSAGE_STATUSES as $key=>$label): ?><option value="<?=$key?>" <?=$key===$status?'selected':''?>><?=e(t($label))?></option><?php endforeach ?></select><button><?=e(t('ค้นหา'))?></button></form>
<?php if(!$messages): ?><div class="panel contact-no-messages"><h2>ยังไม่มีข้อความในรายการนี้</h2><p>ข้อความจากแบบฟอร์มติดต่อจะแสดงที่นี่ ผู้ดูแลสามารถเปิดอ่านและบันทึกสถานะได้</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>วันที่</th><th>ผู้ติดต่อ</th><th>หัวข้อ</th><th>สถานะ</th><th></th></tr></thead><tbody><?php foreach($messages as $message): ?><tr><td><?=e($message['created_at'])?></td><td><?=e($message['name'])?><small class="message-email"><?=e($message['email'])?></small></td><td><?=e($message['subject'])?></td><td><span class="message-status status-<?=e($message['status'])?>"><?=CONTACT_MESSAGE_STATUSES[$message['status']]?></span></td><td><a href="<?=url('admin/contact_message.php?id='.$message['id'])?>">อ่านรายละเอียด</a></td></tr><?php endforeach ?></tbody></table></div><?php endif ?>
<div class="pagination"><?php for($number=1;$number<=$pages;$number++): ?><a class="<?=$number===$page?'selected':''?>" href="?<?=e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$number]))?>"><?=$number?></a><?php endfor ?></div>
<?php admin_footer(); ?>
