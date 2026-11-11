<?php
require 'bootstrap.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{save_store_contact_settings($_POST,(int)$admin['id']);flash('บันทึกข้อมูลติดต่อแล้ว เว็บไซต์จะแสดงข้อมูลล่าสุดอัตโนมัติ');go('admin/contact.php');}
    catch(PDOException $exception){error_log('Contact settings error: '.$exception->getCode());$error='ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่';}
    catch(RuntimeException $exception){$error=$exception->getMessage();}
}
$channels=store_contact_channels();
$newCount=(int)q('SELECT COUNT(*) FROM contact_messages WHERE status="new"')->fetchColumn();
admin_header('จัดการข้อมูลติดต่อ');
?>
<div class="contact-admin-toolbar"><p>ตั้งค่าข้อมูลที่ใช้ร่วมกันในหน้าติดต่อเราและ Footer</p><a class="button secondary" href="<?=url('admin/contact_messages.php')?>"><?=e(t('ข้อความลูกค้า'))?><?=$newCount?' · ใหม่ '.$newCount:''?></a></div>
<div class="notice contact-admin-notice">LINE ID และที่อยู่จะเผยแพร่เมื่อเปิดการแสดงและ Admin ยืนยันแล้วเท่านั้น หากแก้ค่าใหม่ ระบบจะถอนการยืนยันเดิมจนกว่าจะยืนยันอีกครั้ง</div>
<?php if($error!==''): ?><p class="error" role="alert"><?=e(t($error))?></p><?php endif ?>
<form method="post" class="contact-settings-form"><?=csrf()?>
<?php foreach(contact_channel_definitions() as $code=>$definition):$channel=$channels[$code];$value=$_SERVER['REQUEST_METHOD']==='POST'?post($code):$channel['value'];$enabled=$_SERVER['REQUEST_METHOD']==='POST'?isset($_POST['show_'.$code]):(bool)$channel['enabled'];$critical=in_array($code,['line','address'],true); ?>
<section class="panel contact-setting-card"><div class="contact-setting-heading"><?=contact_icon($code)?><h2><?=e($definition['label'])?></h2><?php if($critical): ?><span class="contact-state <?=contact_channel_confirmed($channel)?'is-confirmed':''?>"><?=contact_channel_confirmed($channel)?'ยืนยันแล้ว':'ยังไม่ยืนยัน · ยังไม่เผยแพร่'?></span><?php endif ?></div>
<label><?=e($definition['label'])?><?php if($code==='address'): ?><textarea name="<?=$code?>" maxlength="<?=$definition['limit']?>" rows="3"><?=e($value)?></textarea><?php else: ?><input type="<?=$code==='email'?'email':($code==='phone'?'tel':'text')?>" name="<?=$code?>" maxlength="<?=$definition['limit']?>" value="<?=e($value)?>"><?php endif ?></label>
<label class="checkbox"><input type="checkbox" name="show_<?=$code?>" <?=$enabled?'checked':''?>> เปิดแสดงช่องทางนี้บนเว็บไซต์</label>
<?php if($critical): ?><div class="contact-verification"><p><?=$code==='line'?'ค่า @babyproducts.th เป็นเพียงข้อเสนอ ยังไม่ได้สร้างหรือยืนยันบัญชี LINE จริง':'ที่อยู่เริ่มต้นเป็นข้อมูลสมมติ ต้องแก้เป็นที่อยู่จริงก่อนยืนยัน'?></p><label class="checkbox"><input type="checkbox" name="confirm_<?=$code?>"> <?=$code==='line'?'ฉันยืนยันว่า LINE ID ที่กรอกเป็นของร้านและใช้งานได้จริง':'ฉันยืนยันว่าที่อยู่ที่กรอกเป็นที่อยู่จริงของร้าน'?></label><?php if(contact_channel_confirmed($channel)): ?><label class="checkbox"><input type="checkbox" name="revoke_<?=$code?>"> ถอนการยืนยันและซ่อนจากหน้าร้าน</label><small>ยืนยันล่าสุด <?=e($channel['verified_at'])?></small><?php endif ?></div><?php endif ?>
</section><?php endforeach ?>
<div class="contact-settings-actions"><button type="submit">บันทึกข้อมูลติดต่อ</button><a href="<?=url('contact.php')?>">ดูหน้าติดต่อเรา</a></div></form>
<?php admin_footer(); ?>
