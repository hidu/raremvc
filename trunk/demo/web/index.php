<?php
define("PROD", 0);
error_reporting(0);
include '../../rare/rareMVC.class.php';
include '../../rare/dump.php';
rareContext::createApp()->run();