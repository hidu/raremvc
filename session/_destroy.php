<?php
/**
 *销毁指定的session 
 */
$sessionName=$_SERVER['argv'][1];
$sessionID=$_SERVER['argv'][2];
session_name($sessionName);
session_id($sessionID);
session_start();
session_destroy();