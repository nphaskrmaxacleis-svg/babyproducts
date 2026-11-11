<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Bangkok');
ini_set('display_errors','0');
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax','path'=>'/babyproducts/']);
session_start();
require_once __DIR__.'/i18n.php';
header('Content-Language: '.current_language());
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/product_images.php';
require_once __DIR__.'/store_contact.php';
require_once __DIR__.'/delivery_map.php';
require_once __DIR__.'/payments.php';
require_once __DIR__.'/addresses.php';
require_once __DIR__.'/commerce.php';
require_once __DIR__.'/promptpay.php';
require_once __DIR__.'/inventory.php';
require_once __DIR__.'/coordinates.php';
require_once __DIR__.'/taxes.php';
require_once __DIR__.'/documents.php';
set_exception_handler(function(Throwable $e){error_log('Unhandled '.get_class($e).' code '.$e->getCode().' at '.basename($e->getFile()).':'.$e->getLine());http_response_code(500);echo '<meta charset="utf-8"><h1>ยังไม่สามารถทำรายการได้</h1><p>กรุณาตรวจสอบการติดตั้งฐานข้อมูลตาม README หรือลองใหม่ภายหลัง</p>';});
function q(string $sql,array $args=[]): PDOStatement {$s=db()->prepare($sql);$s->execute($args);return $s;}
function e(mixed $v): string {return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');}
function url(string $path=''): string {return '/babyproducts/'.$path;}
function go(string $path): never {header('Location: '.url($path));exit;}
function flash(string $message): void {$_SESSION['flash']=$message;}
function csrf(): string {$_SESSION['csrf']??=bin2hex(random_bytes(32));return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function verify_csrf(): void {if(!is_string($_POST['csrf']??null)||!hash_equals($_SESSION['csrf']??'',$_POST['csrf'])){http_response_code(403);exit(t('CSRF: กรุณาเปิดหน้าใหม่แล้วลองอีกครั้ง'));}}
function user(): ?array {static $loaded=false,$u=null;if(!$loaded){$loaded=true;if(isset($_SESSION['uid']))$u=q('SELECT id,name,email,role,phone,address FROM users WHERE id=? AND active=1',[$_SESSION['uid']])->fetch()?:null;}return $u;}
function auth(bool $admin=false): array {$u=user();if(!$u)go('login.php');if($admin&&$u['role']!=='admin'){http_response_code(403);exit(t('ไม่มีสิทธิ์เข้าถึง'));}return $u;}
function money(mixed $n): string {return '฿'.number_format((float)$n,2);}
function cents(mixed $v): int {if(is_int($v))return $v*100;if(is_float($v))$v=number_format($v,2,'.','');$text=(string)$v;if(!preg_match('/^(-?)([0-9]+)(?:\.([0-9]{1,2}))?$/D',$text,$m))throw new RuntimeException('จำนวนเงินไม่ถูกต้อง');return ($m[1]==='-'?-1:1)*((int)$m[2]*100+(int)str_pad($m[3]??'',2,'0'));}
function post_raw(string $key): string {return is_string($_POST[$key]??null)?$_POST[$key]:'';}
function post(string $key): string {return trim(post_raw($key));}
function field(string $name,string $label,string $type='text',mixed $value='',bool $required=true): void {echo '<label>'.e(t($label)).'<input name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.($required?'required':'').' maxlength="'.($type==='password'?'72':'190').'"></label>';}
function cart_items(): array {$items=[];foreach($_SESSION['cart']??[] as $key=>$qty){[$id,$variant]=cart_identity($key);$p=q('SELECT * FROM products WHERE id=? AND active=1',[$id])->fetch();if($p){$p['cart_key']=$key;$p['quantity']=$qty;$p['variant_id']=$variant?:null;if($variant){$v=q('SELECT * FROM product_variants WHERE id=? AND product_id=? AND active=1',[$variant,$id])->fetch();if(!$v)continue;$p['stock']=$v['stock'];$p['variant_color']=$v['color'];$p['variant_size']=$v['size'];}$items[]=$p;}}return $items;}
function card(array $p): void {
    if(!empty($p['variants_enabled'])){echo '<article class="product modern-product"><a href="'.url('product_detail.php?id='.$p['id']).'">'.product_image_html($p).'<h3>'.e(localized_name($p)).'</h3><p>'.money($p['price']).'</p><span class="button">'.e(t('เลือกสี / ไซซ์')).'</span></a></article>';return;}
    $path=url('product_detail.php?id='.$p['id']);
    $image=product_image_path($p);$note=t(product_image_note($p));
    $payload=json_encode(['id'=>(int)$p['id'],'name'=>localized_name($p),'description'=>localized_description($p),'price'=>money($p['price']),'stock'=>(int)$p['stock'],'image'=>url($image),'image_note'=>$note,'url'=>$path],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    echo '<article class="product modern-product"><div class="product-image"><a href="'.e($path).'">'.product_image_html($p).'</a><span class="product-tag">'.t($p['featured']?'คัดสรรพิเศษ':'ของใช้ประจำวัน').'</span><button class="quick-view" type="button" data-quick-view="'.e($payload).'" aria-label="ดูสินค้า '.e(localized_name($p)).' แบบย่อ">'.e(t('ดูแบบรวดเร็ว ↗')).'</button></div><div class="product-body"><small class="photo-note">'.e($note).'</small><h3><a href="'.e($path).'">'.e(localized_name($p)).'</a></h3><div class="product-purchase"><div><span class="price">'.money($p['price']).'</span><small class="stock-note">'.t($p['stock']>0?'พร้อมให้เลือกสรร':'สินค้าหมด').'</small></div><form method="post" action="'.url('cart.php').'" data-quick-add>'.csrf().'<input type="hidden" name="id" value="'.(int)$p['id'].'"><input type="hidden" name="quantity" value="1"><input type="hidden" name="action" value="add"><button class="quick-add" type="submit" aria-label="เพิ่ม '.e(localized_name($p)).' ลงตะกร้า" '.($p['stock']<1?'disabled':'').'>+</button></form></div></div></article>';
}
const STATUSES=['pending'=>'รอยืนยันคำสั่งซื้อ','confirmed'=>'ยืนยันคำสั่งซื้อแล้ว','preparing'=>'กำลังเตรียมสินค้า','shipped'=>'จัดส่งสินค้าแล้ว','delivered'=>'จัดส่งสำเร็จ','cancelled'=>'ยกเลิกคำสั่งซื้อ'];
