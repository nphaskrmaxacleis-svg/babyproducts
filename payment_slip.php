<?php
require 'includes/functions.php';$u=auth();
$slip=q('SELECT s.*,o.user_id FROM order_slips s JOIN orders o ON o.id=s.order_id WHERE s.id=?',[(int)($_GET['id']??0)])->fetch();
if(!$slip||($u['role']!=='admin'&&(int)$slip['user_id']!==(int)$u['id'])||!preg_match('/^[a-f0-9]{48}\.png$/D',$slip['stored_name'])){http_response_code(404);exit('ไม่พบหลักฐาน');}
$path=__DIR__.'/storage/slips/'.$slip['stored_name'];if(!is_file($path)){http_response_code(404);exit('ไม่พบไฟล์หลักฐาน');}
header('Content-Type: image/png');header('Cache-Control: private, no-store, max-age=0');header('Pragma: no-cache');header('Content-Security-Policy: default-src \'none\'; sandbox');header('Content-Disposition: inline; filename="payment-slip-'.(int)$slip['id'].'.png"');header('Content-Length: '.filesize($path));readfile($path);
