<?php
declare(strict_types=1);
function valid_delivery_map(string $value): bool {
    if(strlen($value)>2000||!filter_var($value,FILTER_VALIDATE_URL)||preg_match('/[\x00-\x20\x7f]/',$value))return false;
    $parts=parse_url($value);
    if(($parts['scheme']??'')!=='https'||isset($parts['user'])||isset($parts['pass'])||isset($parts['port']))return false;
    $host=strtolower($parts['host']??'');$path=$parts['path']??'';
    if($host==='maps.app.goo.gl')return strlen(trim($path,'/'))>0;
    if($host==='goo.gl')return str_starts_with($path,'/maps/');
    if(in_array($host,['maps.google.com','maps.google.co.th'],true))return $path!==''||!empty($parts['query']);
    return in_array($host,['google.com','www.google.com','google.co.th','www.google.co.th'],true)&&($path==='/maps'||str_starts_with($path,'/maps/'));
}
function delivery_location_html(?string $value): string {
    $value=$value??'';
    if(!valid_delivery_map($value))return e($value);
    return '<a href="'.e($value).'" target="_blank" rel="noopener noreferrer" class="delivery-map-link">เปิดแผนที่จัดส่ง ↗</a>';
}
