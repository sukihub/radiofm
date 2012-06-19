<?php

require_once 'lib/helpers/Testable.php';
require_once 'lib/radiofm/NowPlaying.php';

date_default_timezone_set("Europe/Bratislava");

$first = RadioFM\NowPlaying::get();
var_dump($first);

$second = RadioFM\NowPlaying::get($first);
var_dump($second);
var_dump($first === $second);

sleep(60*5);

$third = RadioFM\NowPlaying::get($first);
var_dump($third);
var_dump($first === $third);