<?php
declare(strict_types=1);
function current_language():string{return ($_SESSION['language']??'th')==='en'?'en':'th';}
function translation_dictionary():array{static $dictionary;return $dictionary??=array_merge(require __DIR__.'/../lang/en.php',require __DIR__.'/../lang/en-extra.php',require __DIR__.'/../lang/en-policies.php',require __DIR__.'/../lang/en-validation.php');}
function t(string $text):string{if(current_language()==='th')return $text;return translation_dictionary()[$text]??$text;}
function localized_name(array $row):string{return current_language()==='en'&&!empty($row['name_en'])?$row['name_en']:(string)($row['name']??'');}
function localized_description(array $row):string{return current_language()==='en'&&!empty($row['description_en'])?$row['description_en']:(string)($row['description']??'');}
function t_message(string $message):string{if(current_language()==='th')return $message;$parts=explode(' · ',$message);return implode(' · ',array_map(fn($part)=>t($part),$parts));}
