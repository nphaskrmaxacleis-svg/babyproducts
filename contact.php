<?php
require 'includes/functions.php';
$title='ติดต่อเรา — Baby Products';
$error='';$values=['name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>''];
$_SESSION['contact_form_token']??=bin2hex(random_bytes(32));
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    foreach($values as $field=>$value)$values[$field]=post($field);
    $values['email']=normalize_login_email($values['email']);
    try{
        $token=post('submission_token');$sender=hash('sha256',session_id());
        if(!preg_match('/^[a-f0-9]{64}$/D',$token))throw new RuntimeException('แบบฟอร์มหมดอายุ กรุณาเปิดหน้าใหม่แล้วลองอีกครั้ง');
        if(q('SELECT id FROM contact_messages WHERE submission_token=? AND sender_hash=?',[$token,$sender])->fetchColumn()){
            flash('ส่งข้อความถึงร้านค้าเรียบร้อยแล้ว');go('contact.php');
        }
        if(!hash_equals($_SESSION['contact_form_token'],$token))throw new RuntimeException('แบบฟอร์มหมดอายุ กรุณาเปิดหน้าใหม่แล้วลองอีกครั้ง');
        foreach(['name'=>120,'email'=>190,'subject'=>160,'message'=>5000] as $field=>$limit){
            if($values[$field]===''||mb_strlen($values[$field])>$limit)throw new RuntimeException('กรุณากรอกชื่อ อีเมล หัวข้อ และข้อความให้ครบตามความยาวที่กำหนด');
        }
        if(!filter_var($values['email'],FILTER_VALIDATE_EMAIL))throw new RuntimeException('กรุณากรอกอีเมลให้ถูกต้อง');
        if(mb_strlen($values['phone'])>30||($values['phone']!==''&&!contact_phone_valid($values['phone'])))throw new RuntimeException('รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง หรือเว้นว่างได้');
        if((int)q('SELECT COUNT(*) FROM contact_messages WHERE sender_hash=? AND created_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)',[$sender])->fetchColumn()>=5){http_response_code(429);throw new RuntimeException('ส่งข้อความครบจำนวนที่กำหนดแล้ว กรุณารอ 15 นาที ก่อนส่งอีกครั้ง');}
        $account=user();
        q('INSERT INTO contact_messages(submission_token,sender_hash,user_id,name,email,phone,subject,message,privacy_notice_version) VALUES (?,?,?,?,?,?,?,?,?)',[$token,$sender,$account['id']??null,$values['name'],$values['email'],$values['phone']!==''?$values['phone']:null,$values['subject'],$values['message'],CONTACT_PRIVACY_VERSION]);
        $_SESSION['contact_form_token']=bin2hex(random_bytes(32));
        flash('ส่งข้อความถึงร้านค้าเรียบร้อยแล้ว');go('contact.php');
    }catch(PDOException $exception){error_log('Contact form database error: '.$exception->getCode());$error='ยังไม่สามารถบันทึกข้อความได้ กรุณาลองใหม่ภายหลัง';}
    catch(RuntimeException $exception){$error=$exception->getMessage();}
}
$channels=public_contact_channels();
require 'includes/header.php';
?>
<div class="contact-page">
<div class="contact-heading"><p class="boutique-kicker"><?=e(t('ติดต่อ Baby Products'))?></p><h1><?=e(t('ติดต่อเรา'))?></h1><p><?=e(t('ยินดีดูแลทุกคำถามของคุณ ไม่ว่าจะเป็นข้อมูลสินค้า การสั่งซื้อ หรือการจัดส่ง'))?><br><?=e(t('ติดต่อผ่านข้อมูลด้านล่าง หรือฝากข้อความถึงร้านค้าได้โดยตรง'))?></p></div>
<?php if($channels): ?><section class="contact-card-grid" aria-label="ข้อมูลติดต่อร้านค้า"><?php foreach($channels as $code=>$channel): ?><article class="contact-info-card"><?=contact_icon($code)?><h2><?=e($channel['label'])?></h2><p class="contact-value" dir="<?=$code==='email'||$code==='phone'||$code==='line'?'ltr':'auto'?>"><?=e($channel['value'])?></p></article><?php endforeach ?></section><?php endif ?>
<div class="contact-form-layout"><section class="contact-form-panel"><p class="boutique-kicker"><?=e(t('ฝากข้อความถึงเรา'))?></p><h2><?=e(t('เรายินดีรับฟังคุณ'))?></h2><p class="contact-form-intro"><?=e(t('กรอกข้อมูลเพื่อให้ร้านค้าติดต่อกลับ ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอก'))?></p>
<?php if($error!==''): ?><p class="error contact-error" role="alert"><?=e(t($error))?></p><?php endif ?>
<form method="post" action="<?=url('contact.php')?>" class="customer-contact-form">
<?=csrf()?><input type="hidden" name="submission_token" value="<?=e($_SESSION['contact_form_token'])?>">
<div class="contact-field-grid"><label><?=e(t('ชื่อผู้ติดต่อ *'))?><input type="text" name="name" autocomplete="name" maxlength="120" value="<?=e($values['name'])?>" required></label><label><?=e(t('อีเมล *'))?><input type="email" name="email" autocomplete="email" maxlength="190" value="<?=e($values['email'])?>" required></label></div>
<label><?=e(t('เบอร์โทรศัพท์'))?> <span class="field-optional"><?=e(t('(ไม่บังคับ)'))?></span><input type="tel" name="phone" autocomplete="tel" maxlength="30" value="<?=e($values['phone'])?>"></label>
<label><?=e(t('หัวข้อการติดต่อ *'))?><input type="text" name="subject" maxlength="160" value="<?=e($values['subject'])?>" required></label>
<label><?=e(t('รายละเอียดข้อความ *'))?><textarea name="message" rows="7" maxlength="5000" required><?=e($values['message'])?></textarea><small><?=e(t('ไม่เกิน 5,000 ตัวอักษร'))?></small></label>
<p class="contact-privacy-note"><?=e(t('ชื่อ อีเมล เบอร์โทรศัพท์ และข้อความจะใช้เพื่อรับเรื่อง ตรวจสอบ และติดต่อกลับเกี่ยวกับคำถามของคุณ โดยจำกัดการเข้าถึงเฉพาะผู้ดูแลร้านค้า อ่านรายละเอียดใน'))?> <a href="<?=url('privacy.php')?>"><?=e(t('นโยบายความเป็นส่วนตัว'))?></a> <?=e(t('กรุณาไม่ส่งรหัสผ่านหรือข้อมูลการชำระเงินผ่านแบบฟอร์มนี้'))?></p>
<button type="submit"><?=e(t('ส่งข้อความ'))?></button>
</form></section>
<aside class="contact-help-panel"><h2><?=e(t('ให้เราช่วยดูแล'))?></h2><p><?=e(t('ข้อความของคุณจะถูกส่งเข้าระบบของร้านค้า ผู้ดูแลสามารถตรวจสอบและติดต่อกลับตามข้อมูลที่คุณให้ไว้'))?></p><dl><div><dt><?=e(t('ข้อมูลเกี่ยวกับคำสั่งซื้อ'))?></dt><dd><?=e(t('หากมีเลขที่คำสั่งซื้อ ระบุไว้ในข้อความเพื่อช่วยให้ตรวจสอบได้สะดวกขึ้น'))?></dd></div><div><dt><?=e(t('ข้อมูลการจัดส่ง'))?></dt><dd><?=e(t('ตรวจบริการขนส่ง ค่าจัดส่ง และสิทธิ์ส่งฟรีจากยอดหลังส่วนลดใน Checkout'))?></dd></div></dl><a href="<?=url('terms.php#shipping')?>"><?=e(t('อ่านข้อมูลการจัดส่ง'))?></a><a href="<?=url('terms.php#ordering')?>"><?=e(t('ดูวิธีการสั่งซื้อ'))?></a></aside></div>
</div>
<?php require 'includes/footer.php'; ?>
