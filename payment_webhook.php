<?php
require 'includes/functions.php';require_once 'includes/gateway.php';
header('Content-Type: application/json');header('Cache-Control: no-store');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');echo '{"error":"method_not_allowed"}';exit;}
$provider=is_string($_GET['provider']??null)?$_GET['provider']:'';$gateways=configured_payment_gateways();
if(!isset($gateways[$provider])){http_response_code(503);echo '{"error":"gateway_not_configured"}';exit;}
try{
    if((int)($_SERVER['CONTENT_LENGTH']??0)>1048576)throw new RuntimeException('Body too large');
    $body=file_get_contents('php://input',false,null,0,1048577);if(strlen($body)>1048576)throw new RuntimeException('Body too large');
    $event=$gateways[$provider]->verifyWebhook($body,function_exists('getallheaders')?getallheaders():[]);
    process_verified_gateway_event($provider,$event);echo '{"ok":true}';
}catch(Throwable $error){http_response_code(400);echo '{"error":"invalid_event"}';}
