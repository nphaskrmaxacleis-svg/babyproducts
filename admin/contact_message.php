<?php
require 'bootstrap.php';
$id=(int)($_GET['id']??0);$error='';
$message=q('SELECT * FROM contact_messages WHERE id=?',[$id])->fetch();
if(!$message){http_response_code(404);admin_header('ไม่พบข้อความ');echo '<p>ไม่พบข้อความที่ต้องการ</p>';admin_footer();exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();$status=post('status');
    if(!array_key_exists($status,CONTACT_MESSAGE_STATUSES))$error='สถานะข้อความไม่ถูกต้อง';
    else{q('UPDATE contact_messages SET status=?,updated_by=? WHERE id=?',[$status,$admin['id'],$id]);flash('บันทึกสถานะข้อความแล้ว');go('admin/contact_message.php?id='.$id);}
}
admin_header('รายละเอียดข้อความ');
?>
<p><a href="<?=url('admin/contact_messages.php')?>">กลับไปข้อความทั้งหมด</a></p>
<article class="panel contact-message-detail"><span class="message-status status-<?=e($message['status'])?>"><?=CONTACT_MESSAGE_STATUSES[$message['status']]?></span><h2><?=e($message['subject'])?></h2><dl class="message-metadata"><div><dt>ผู้ติดต่อ</dt><dd><?=e($message['name'])?></dd></div><div><dt><?=e(t('อีเมล'))?></dt><dd><?=e($message['email'])?></dd></div><div><dt><?=e(t('เบอร์โทรศัพท์'))?></dt><dd><?=e($message['phone']?:'ไม่ได้ระบุ')?></dd></div><div><dt>วันที่ส่ง</dt><dd><?=e($message['created_at'])?></dd></div></dl><div class="customer-message-body"><?=e($message['message'])?></div></article>
<section class="panel contact-message-actions"><h2>จัดการสถานะ</h2><?php if($error): ?><p class="error" role="alert"><?=e(t($error))?></p><?php endif ?><form method="post"><?=csrf()?><label>สถานะข้อความ<select name="status"><?php foreach(CONTACT_MESSAGE_STATUSES as $key=>$label): ?><option value="<?=$key?>" <?=$message['status']===$key?'selected':''?>><?=e(t($label))?></option><?php endforeach ?></select></label><button>บันทึกสถานะ</button></form><p>เลือก “ตอบกลับแล้ว” หลังผู้ดูแลติดต่อกลับจริง ระบบนี้บันทึกสถานะและข้อความในเว็บไซต์ ไม่ส่งอีเมลหรือข้อความผ่านแอปภายนอกอัตโนมัติ</p></section>
<?php admin_footer(); ?>
