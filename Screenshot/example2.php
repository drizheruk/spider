<?php
include 'ScreenShot.php';
$s = new ScreenShot;

//save image
$s->snap('http://google.com', 'thumb.jpg');