<?php
require 'bootstrap.php';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
    $bank=post('bank_name');$name=post('account_name');$number=preg_replace('/[\s-]+/','',post('account_number'));$enabled=isset($_POST['enabled'])?1:0;
    if(mb_strlen($bank)>120||mb_strlen($name)>160||strlen($number)>40||($number!==''&&!preg_match('/^[0-9]{6,30}$/D',$number)))throw new RuntimeException('กรุณาตรวจชื่อธนาคาร ชื่อบัญชี และเลขบัญชี 6–30 หลัก');
    if($enabled&&($bank===''||$name===''||$number===''||post('real_account_confirm')!=='1'))throw new RuntimeException('ต้องกรอกบัญชีจริงครบถ้วนและยืนยันก่อนเปิดรับโอนเงิน');
    $current=bank_settings();$extra=$current;
    if(post('commerce_settings_present')==='1'){
        $min=commerce_money_input(post('cod_min'));$max=commerce_money_input(post('cod_max'));$areas=implode("\n",commerce_provinces(post('cod_provinces')));
        if($min>$max||mb_strlen($areas)>10000)throw new RuntimeException('ยอดหรือพื้นที่ COD ไม่ถูกต้อง');
        $ppEnabled=isset($_POST['promptpay_enabled'])?1:0;$ppType=post('promptpay_type');$ppNumber=post('promptpay_recipient');$ppName=post('promptpay_name');
        if(!in_array($ppType,['phone','national_id'],true)||mb_strlen($ppName)>160||($ppNumber!==''&&!promptpay_valid_recipient($ppType,$ppNumber)))throw new RuntimeException('ชื่อหรือหมายเลขพร้อมเพย์ไม่ถูกต้อง');
        if($ppEnabled&&($ppName===''||!promptpay_valid_recipient($ppType,$ppNumber)||post('promptpay_confirm')!=='1'))throw new RuntimeException('ต้องยืนยันผู้รับพร้อมเพย์จริงก่อนเปิดใช้งาน');
        $extra=array_merge($current,['cod_enabled'=>isset($_POST['cod_enabled'])?1:0,'cod_min'=>$min/100,'cod_max'=>$max/100,'cod_provinces'=>$areas?:null,'promptpay_enabled'=>$ppEnabled,'promptpay_type'=>$ppType,'promptpay_recipient'=>$ppNumber,'promptpay_name'=>$ppName]);
    }
    db()->beginTransaction();
    q('UPDATE payment_settings SET cod_enabled=?,cod_min=?,cod_max=?,cod_provinces=?,promptpay_enabled=?,promptpay_type=?,promptpay_recipient=?,promptpay_name=? WHERE id=1',[$extra['cod_enabled'],$extra['cod_min'],$extra['cod_max'],$extra['cod_provinces'],$extra['promptpay_enabled'],$extra['promptpay_type'],$extra['promptpay_recipient'],$extra['promptpay_name']]);
    q('UPDATE payment_settings SET bank_name=?,account_name=?,account_number=?,enabled=?,updated_by=? WHERE id=1',[$bank,$name,$number,$enabled,$admin['id']]);db()->commit();flash('บันทึกการตั้งค่าชำระเงินแล้ว');go('admin/payment_settings.php');
 }catch(RuntimeException $exception){if(db()->inTransaction())db()->rollBack();$error=$exception instanceof PDOException?'บันทึกไม่สำเร็จ กรุณาลองใหม่':$exception->getMessage();}
}
$bank=bank_settings();admin_header('ตั้งค่าการชำระเงิน');
?>
<div class="notice">ใส่บัญชีจริงของร้านเท่านั้น ระบบไม่ใส่บัญชีตัวอย่างให้ลูกค้า การเปลี่ยนบัญชีนี้ไม่มีผลต่อข้อมูลบัญชีที่บันทึกไว้ในคำสั่งซื้อเดิม</div>
<?php if($error): ?><p class="error" role="alert"><?=e(t($error))?></p><?php endif ?>
<form method="post" class="panel"><?=csrf()?><?php require '../includes/commerce_payment_settings.php'; ?><?php foreach(['bank_name'=>'ชื่อธนาคาร','account_name'=>'ชื่อบัญชี','account_number'=>'เลขบัญชี'] as $key=>$label)field($key,$label,'text',$_SERVER['REQUEST_METHOD']==='POST'?post($key):($bank[$key]??''),false); ?><label class="checkbox"><input type="checkbox" name="enabled" <?=($_SERVER['REQUEST_METHOD']==='POST'?isset($_POST['enabled']):!empty($bank['enabled']))?'checked':''?>> เปิดรับโอนเงินผ่านธนาคาร</label><label class="checkbox"><input type="checkbox" name="real_account_confirm" value="1"> ยืนยันว่าข้อมูลบัญชีนี้เป็นบัญชีจริงที่ร้านอนุญาตให้รับชำระเงิน</label><button>บันทึกการตั้งค่า</button></form>
<?php admin_footer(); ?>
