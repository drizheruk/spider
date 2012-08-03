<?php
include 'ScreenShot.php';
$s = new ScreenShot;

//do not show "blank" image
header('Content-Type: image/jpg');
$image = $s->snap('http://googleoo.cm');
if (!$image) readfile('blank.jpg');