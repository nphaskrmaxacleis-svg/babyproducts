<?php
declare(strict_types=1);
function shipping_coordinates(array $input): array {
    $lat=trim(is_string($input['latitude']??null)?$input['latitude']:'');$lng=trim(is_string($input['longitude']??null)?$input['longitude']:'');
    if($lat===''&&$lng==='')return ['latitude'=>null,'longitude'=>null];
    if(!preg_match('/^-?[0-9]{1,3}(?:\.[0-9]{1,7})?$/D',$lat)||!preg_match('/^-?[0-9]{1,3}(?:\.[0-9]{1,7})?$/D',$lng)||(float)$lat< -90||(float)$lat>90||(float)$lng< -180||(float)$lng>180)throw new RuntimeException('พิกัดตำแหน่งไม่ถูกต้อง');
    return ['latitude'=>number_format((float)$lat,7,'.',''),'longitude'=>number_format((float)$lng,7,'.','')];
}
