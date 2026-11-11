<?php
require 'includes/functions.php';
$homePage = true;
$categories = q('SELECT * FROM categories WHERE active=1 ORDER BY id')->fetchAll();
$categoryPhotos=[];
foreach(q('SELECT p.* FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1 ORDER BY p.id') as $photo) {
    $categoryPhotos[(int)$photo['category_id']]??=$photo;
}
$categoryImage=static function(int $category,string $class='',bool $lazy=true,string $sizes='160px') use($categoryPhotos):string {
    return product_image_html($categoryPhotos[$category]??['name'=>'ภาพหมวดหมู่สินค้า','category_id'=>$category,'image'=>''],$class,$lazy,$sizes);
};
$featured = q('SELECT p.* FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1 AND p.featured=1 ORDER BY p.id DESC LIMIT 4')->fetchAll();
$latest = q('SELECT p.* FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1 ORDER BY p.id DESC LIMIT 4')->fetchAll();
require 'includes/header.php';
require 'includes/boutique_home.php';
require 'includes/footer.php';
