<?php
header("Content-Type: image/png");
$im = @imagecreate(400, 400);
$background = imagecolorallocate($im, 255, 255, 255);

$litere="ABCDEFGHIKLMNOPQRSTVXYZabcdefghijklmnopqrstvxyz0123456789";

$rand_r = rand(0,230);
$rand_g = rand(0,230);
$rand_b = rand(0,230);
$text_color = imagecolorallocate($im, $rand_r, $rand_g, $rand_b);
$x = 50;
$cod_captcha = "";
for($i=0;$i<9;$i++){
$poz = rand(1,56);
$rand_f = rand(16,18);
$rand_i = rand(20,40);
imagestring($im, $rand_f, $x, 200-$rand_i, $litere[$poz], $text_color);
$cod_captcha .= $litere[$poz];
$x += 35;
}
session_start();
$_SESSION["cod"] = $cod_captcha;
$rand_r = rand(0,230);
$rand_g = rand(0,230);
$rand_b = rand(0,230);
$pol_color = imagecolorallocate($im, $rand_r, $rand_g, $rand_b);
//                      1                       2                           3                          4                            5                          6                        7                           8
imagepolygon($im,array(rand(1,100),rand(1,200),rand(200,300),rand(200,400),rand(300,400),rand(200,400),rand(100,200),rand(200,400),rand(200,300),rand(1,200),rand(1,100),rand(200,400),rand(100,200),rand(1,200),rand(300,400),rand(200,400)),8,$pol_color);

imagepng($im);
imagedestroy($im);
?>