<?php

$css = 'html{margin-top:10px;}'."\n".' body{float:none;margin-right:auto;margin-left:auto;display:block;}';
if(isset($_GET['xeditorcss'])) $css = $_GET['xeditorcss'] . $css;
if(isset($_GET['ckeditor']) && $_GET['ckeditor']) $css = str_replace('body', 'body.cke_show_borders', $css);
//file_put_contents('./extratest.txt',$css);
header('Content-Type: text/css');
echo $css;

?>
