<?php
declare(strict_types=1);
// Aggregate requests only: no IP, cookies, identity, URL query or user-agent stored.
function analytics_store_path(): string {return __DIR__.'/../storage/pageviews.json';}
function analytics_record_page(string $page): void {
    if(($_SERVER['REQUEST_METHOD']??'')!=='GET'||http_response_code()>=400||!in_array($page,['index.php','products.php','product_detail.php','contact.php','privacy.php','terms.php'],true))return;
    $file=@fopen(analytics_store_path(),'c+');if(!$file)return;
    try{
        if(!flock($file,LOCK_EX))return;
        $raw=stream_get_contents($file);$data=$raw!==''?json_decode($raw,true):[];
        if(!is_array($data))return;
        $day=date('Y-m-d');$data[$day][$page]=(int)($data[$day][$page]??0)+1;
        $cutoff=date('Y-m-d',strtotime('-89 days'));
        foreach(array_keys($data) as $date)if($date<$cutoff)unset($data[$date]);
        $json=json_encode($data,JSON_THROW_ON_ERROR);rewind($file);ftruncate($file,0);fwrite($file,$json);fflush($file);
    }catch(Throwable $error){/* Statistics must never interrupt a customer request. */}
    finally{flock($file,LOCK_UN);fclose($file);}
}
function analytics_read(): array {
    $file=@fopen(analytics_store_path(),'r');if(!$file)return [];
    try{if(!flock($file,LOCK_SH))return [];return json_decode(stream_get_contents($file),true)?:[];}
    finally{flock($file,LOCK_UN);fclose($file);}
}
