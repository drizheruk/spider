<?php
include 'ScreenShot.php';
$s = new ScreenShot;

//show image
header('Content-Type: image/jpg');
echo $s->snap('http://google.com');