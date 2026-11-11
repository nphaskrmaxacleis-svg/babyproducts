<?php
require 'includes/functions.php';auth();header('Content-Type: application/json');header('Cache-Control: private, no-store');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('{"ok":false}');}verify_csrf();
try{
    $query=post('query');if(mb_strlen($query)<3||mb_strlen($query)>120)throw new RuntimeException('กรอกชื่อสถานที่ 3–120 ตัวอักษร');
    $hash=hash('sha256',$query);$cached=q('SELECT response FROM map_search_cache WHERE query_hash=? AND expires_at>NOW()',[$hash])->fetchColumn();if($cached){echo $cached;exit;}
    $lock=fopen(__DIR__.'/storage/geocoder.lock','c+');if(!$lock||!flock($lock,LOCK_EX))throw new RuntimeException('บริการค้นหายังไม่พร้อม');
    try{
        $last=(float)stream_get_contents($lock);if(microtime(true)-$last<1){http_response_code(429);throw new RuntimeException('กรุณารอสักครู่ก่อนค้นหาอีกครั้ง');}
        rewind($lock);ftruncate($lock,0);fwrite($lock,(string)microtime(true));fflush($lock);
        $endpoint=getenv('PHOTON_ENDPOINT')?:'https://photon.komoot.io/api/';
        if(!filter_var($endpoint,FILTER_VALIDATE_URL)||(!str_starts_with($endpoint,'https://')&&!preg_match('#^http://(?:127\.0\.0\.1|localhost)(?::[0-9]+)?/#',$endpoint)))throw new RuntimeException('การตั้งค่าผู้ให้บริการค้นหาไม่ถูกต้อง');
        $c=curl_init($endpoint.'?'.http_build_query(['q'=>$query,'limit'=>5,'lang'=>'en']));curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>10,CURLOPT_USERAGENT=>'BabyProductsLocation/1.0',CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS|CURLPROTO_HTTP,CURLOPT_FOLLOWLOCATION=>false]);$body=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);if($status!==200||!is_string($body))throw new RuntimeException('ค้นหาไม่สำเร็จ สามารถกรอกที่อยู่หรือปักหมุดเองได้');
        $data=json_decode($body,true);$places=[];foreach(array_slice($data['features']??[],0,5) as $feature){$point=$feature['geometry']['coordinates']??[];if(count($point)<2)continue;$coords=shipping_coordinates(['latitude'=>number_format((float)$point[1],7,'.',''),'longitude'=>number_format((float)$point[0],7,'.','')]);$p=$feature['properties']??[];$label=implode(', ',array_filter([$p['name']??'',$p['city']??'',$p['state']??'',$p['country']??'']));$places[]=array_merge($coords,['label'=>mb_substr($label,0,300)]);}
        $response=json_encode(['ok'=>true,'places'=>$places],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);q('INSERT INTO map_search_cache(query_hash,response,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 1 DAY)) ON DUPLICATE KEY UPDATE response=VALUES(response),expires_at=VALUES(expires_at)',[$hash,$response]);echo $response;
    }finally{flock($lock,LOCK_UN);fclose($lock);}
}catch(Throwable $ex){if(http_response_code()!==429)http_response_code(422);echo json_encode(['ok'=>false,'message'=>$ex instanceof PDOException?t('ค้นหาไม่สำเร็จ'):t($ex->getMessage())],JSON_UNESCAPED_UNICODE);}
