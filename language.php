<?php
require 'includes/functions.php';$language=is_string($_GET['lang']??null)?$_GET['lang']:'th';if(!in_array($language,['th','en'],true)){http_response_code(400);exit;}
$_SESSION['language']=$language;$return=is_string($_GET['return']??null)?$_GET['return']:'/babyproducts/';
if(!str_starts_with($return,'/babyproducts/')||str_contains($return,'\\')||preg_match('/[\x00-\x20\x7f]/',$return)||str_contains($return,'..'))$return='/babyproducts/';
header('Location: '.$return);exit;
