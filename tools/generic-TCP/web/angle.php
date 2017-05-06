<?php
// Skript stellt die Funktionalität von angle.php nach
$host = htmlspecialchars($_SERVER['HTTP_HOST']);
$path = substr(htmlspecialchars($_SERVER['SCRIPT_NAME']), 1, strlen(htmlspecialchars($_SERVER['SCRIPT_NAME']))-11);
$parm = 'var1=Angle&var2=Temperature';
if(isset($_GET['hours'])) {$parm = $parm .'&hours='. $_GET['hours'];}
if(isset($_GET['name'])) {$parm = $parm .'&name=' . $_GET['name'];}
if(isset($_GET['reset'])) {$parm = $parm .'&reset=' . $_GET['reset'];}
header ('Location: http://' . $host . '/' . $path . '/lchart.php?' . $parm);
?>