<?php
require 'includes/functions.php';$u=auth();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();if(post('action')==='read_all')q('UPDATE notifications SET read_at=NOW() WHERE user_id=? AND read_at IS NULL',[$u['id']]);else q('UPDATE notifications SET read_at=NOW() WHERE id=? AND user_id=?',[(int)post('id'),$u['id']]);go('notifications.php');}
$count=(int)q('SELECT COUNT(*) FROM notifications WHERE user_id=?',[$u['id']])->fetchColumn();$pages=max(1,(int)ceil($count/30));$page=max(1,min($pages,(int)($_GET['page']??1)));$messages=q('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 30 OFFSET '.(($page-1)*30),[$u['id']])->fetchAll();require 'includes/header.php';
?>
<div class="page-heading"><h1><?=e(t('การแจ้งเตือน'))?></h1><p><?=e(t('ติดตามความคืบหน้าคำสั่งซื้อและการชำระเงินภายในเว็บไซต์'))?></p></div>
<form method="post"><?=csrf()?><button class="secondary" name="action" value="read_all"><?=e(t('ทำเครื่องหมายอ่านทั้งหมด'))?></button></form>
<?php if(!$messages): ?><p><?=e(t('ยังไม่มีการแจ้งเตือน'))?></p><?php endif;foreach($messages as $message): ?><article class="panel notification-card <?=$message['read_at']?'':'is-unread'?>"><time><?=e($message['created_at'])?></time><p><?=e(t_message($message['message']))?></p><a href="<?=url(($u['role']==='admin'?'admin/':'').'order_detail.php?id='.$message['order_id'])?>"><?=e(t('ดูคำสั่งซื้อ BP-'))?><?=sprintf('%06d',$message['order_id'])?></a><?php if(!$message['read_at']): ?><form method="post"><?=csrf()?><input type="hidden" name="id" value="<?=$message['id']?>"><button class="secondary"><?=e(t('อ่านแล้ว'))?></button></form><?php endif ?></article><?php endforeach ?>
<div class="pagination"><?php for($number=1;$number<=$pages;$number++): ?><a href="?page=<?=$number?>"><?=$number?></a><?php endfor ?></div>
<?php require 'includes/footer.php'; ?>
