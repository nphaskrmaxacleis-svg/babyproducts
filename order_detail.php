<?php
require 'includes/functions.php';$u=auth();$id=(int)($_GET['id']??0);$error='';$stored=null;
$o=q('SELECT * FROM orders WHERE id=? AND user_id=?',[$id,$u['id']])->fetch();
if(!$o){http_response_code(404);require 'includes/header.php';echo '<h1>ไม่พบคำสั่งซื้อ</h1>';require 'includes/footer.php';exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $stored=store_payment_slip($_FILES['slip']??[]);db()->beginTransaction();$locked=locked_order($id);
        if((int)$locked['user_id']!==(int)$u['id'])throw new RuntimeException('ไม่พบคำสั่งซื้อ');
        attach_payment_slip($locked,(int)$u['id'],$stored);db()->commit();$stored=null;
        flash('อัปโหลดสลิปสำเร็จ · รอตรวจสอบการชำระเงิน');go('order_detail.php?id='.$id);
    }catch(Throwable $exception){if(db()->inTransaction())db()->rollBack();discard_payment_slip($stored);$error=$exception instanceof PDOException?'ไม่สามารถบันทึกสลิปได้ กรุณาลองใหม่':$exception->getMessage();}
}
require 'includes/header.php';require 'includes/order_view.php';
?>
<?php if($error): ?><p class="error" role="alert"><?=e(t($error))?></p><?php endif ?>
<?php if(in_array($o['payment_method'],['BANK','PROMPTPAY'],true)&&$o['status']!=='cancelled'&&in_array($o['payment_status'],['unpaid','rejected'],true)): ?><section class="panel"><h2><?=$o['payment_status']==='rejected'?'อัปโหลดหลักฐานใหม่':'แจ้งชำระเงิน'?></h2><p><?=e(t('ยอดที่ต้องชำระ'))?> <?=money($o['total'])?> <?=e(t('อัปโหลดหลักฐานแล้วผู้ดูแลจะตรวจสอบยอดเข้าบัญชีจริง'))?></p><form method="post" enctype="multipart/form-data" data-slip-form><?=csrf()?><?php require 'includes/slip_input.php'; ?><button><?=e(t('ส่งหลักฐานการชำระเงิน'))?></button></form></section><?php endif ?>
<?php require 'includes/footer.php'; ?>
