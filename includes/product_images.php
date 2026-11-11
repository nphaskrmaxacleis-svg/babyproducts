<?php
declare(strict_types=1);

function product_image_path(array $product): string
{
    $path = is_string($product['image'] ?? null) ? $product['image'] : '';
    $root = realpath(__DIR__.'/..');
    $resolved = $path !== '' ? realpath(__DIR__.'/../'.$path) : false;
    if ($resolved && is_file($resolved) && !preg_match('~(?:^|/)\.\.(?:/|$)~',$path) && strncasecmp($resolved, $root.DIRECTORY_SEPARATOR, strlen($root)+1) === 0
        && preg_match('~^(?:uploads/|assets/images/)[a-zA-Z0-9_./-]+\.(?:webp|jpe?g|png|svg)$~i', $path)) return $path;
    $category = max(1, (int)($product['category_id'] ?? 1));
    $fallback = 'assets/images/category-'.$category.'.svg';
    return is_file(__DIR__.'/../'.$fallback) ? $fallback : 'assets/images/product-unavailable.svg';
}

function product_image_note(array $product): string
{
    $path = product_image_path($product);
    if (str_contains($path, '-studio-ai-')) return 'ภาพสร้างด้วย AI สำหรับสินค้าจำลอง ไม่ใช่ภาพสินค้าจริง';
    if (str_starts_with($path, 'assets/images/')) return 'ภาพตัวแทนหมวดหมู่ — ยังไม่มีภาพเฉพาะสินค้า';
    return 'ภาพที่อัปโหลดโดยร้านค้า';
}

function product_image_html(array $product, string $class = '', bool $lazy = true, string $sizes = '(max-width: 700px) 45vw, 280px'): string
{
    $path = product_image_path($product);
    $escape = static fn(string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $base = '/babyproducts/';
    $attributes = '';
    if (preg_match('/\.webp$/i', $path)) {
        $small = substr($path, 0, -5).'-480.webp';
        $medium = substr($path, 0, -5).'-800.webp';
        if (is_file(__DIR__.'/../'.$small) && is_file(__DIR__.'/../'.$medium)) {
            $attributes = ' srcset="'.$escape($base.$small).' 480w, '.$escape($base.$medium).' 800w, '.$escape($base.$path).' 1200w" sizes="'.$escape($sizes).'"';
        }
    }
    $fallback = 'assets/images/category-'.max(1,(int)($product['category_id']??1)).'.svg';
    if (!is_file(__DIR__.'/../'.$fallback)) $fallback = 'assets/images/product-unavailable.svg';
    return '<img src="'.$escape($base.$path).'"'.$attributes.' width="1200" height="1200" class="'.$escape($class).'" alt="'.$escape((string)($product['name']??'สินค้าแม่และเด็ก')).'" loading="'.($lazy?'lazy':'eager').'" decoding="async" data-product-image data-fallback="'.$escape($base.$fallback).'">';
}

function write_product_webp(string $source, string $destination, int $size = 1200): void
{
    if (!extension_loaded('gd') || !function_exists('imagewebp')) throw new RuntimeException('ต้องเปิด GD ที่รองรับ WebP');
    $info = @getimagesize($source);
    if (!$info || !in_array($info['mime'], ['image/jpeg','image/png','image/webp'], true) || $info[0] > 4096 || $info[1] > 4096)
        throw new RuntimeException('รูปต้องเป็น JPEG PNG หรือ WebP ขนาดไม่เกิน 4096 × 4096');
    $image = match($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($source),
        'image/png' => @imagecreatefrompng($source),
        'image/webp' => @imagecreatefromwebp($source),
    };
    if (!$image) throw new RuntimeException('ไม่สามารถอ่านเนื้อหารูปภาพได้');
    if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $orientation = (int)(@exif_read_data($source)['Orientation']??1);
        if (in_array($orientation,[2,4,5,7],true)) imageflip($image, in_array($orientation,[4],true)?IMG_FLIP_VERTICAL:IMG_FLIP_HORIZONTAL);
        $angle = match($orientation) {3=>180,5,6=>-90,7,8=>90,default=>0};
        if ($angle) { $rotated = imagerotate($image,$angle,0); imagedestroy($image); $image=$rotated; }
    }
    $canvas = imagecreatetruecolor($size,$size);
    imagefill($canvas,0,0,imagecolorallocate($canvas,255,255,255));
    $width=imagesx($image);$height=imagesy($image);$ratio=min($size/$width,$size/$height);
    $outW=max(1,(int)round($width*$ratio));$outH=max(1,(int)round($height*$ratio));
    imagecopyresampled($canvas,$image,(int)(($size-$outW)/2),(int)(($size-$outH)/2),0,0,$outW,$outH,$width,$height);
    $ok=imagewebp($canvas,$destination,88);
    imagedestroy($image);imagedestroy($canvas);
    if (!$ok) throw new RuntimeException('ไม่สามารถบันทึก WebP ได้');
}

function store_product_upload(array $file): string
{
    if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || ($file['size']??0)>5242880)
        throw new RuntimeException('อัปโหลดไม่สำเร็จ หรือไฟล์เกิน 5 MB');
    $temporary=$file['tmp_name']??'';
    if (!is_uploaded_file($temporary)) throw new RuntimeException('แหล่งไฟล์อัปโหลดไม่ถูกต้อง');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    if (!in_array($mime,['image/jpeg','image/png','image/webp'],true)) throw new RuntimeException('รับเฉพาะภาพ JPEG PNG WebP');
    $directory=__DIR__.'/../uploads/products';
    if (!is_dir($directory) && !mkdir($directory,0755,true)) throw new RuntimeException('สร้างโฟลเดอร์ภาพไม่ได้');
    $path='uploads/products/'.bin2hex(random_bytes(20)).'.webp';
    foreach ([1200=>'',800=>'-800',480=>'-480'] as $size=>$suffix)
        write_product_webp($temporary,__DIR__.'/../'.substr($path,0,-5).$suffix.'.webp',$size);
    return $path;
}
