<?php
// Skript stellt die Funktionalität von plato.php nach
$host = htmlspecialchars($_SERVER['HTTP_HOST']);
$path = substr(htmlspecialchars($_SERVER['SCRIPT_NAME']), 1, strlen(htmlspecialchars($_SERVER['SCRIPT_NAME']))-11);
$parm = 'var1=Gravity&var2=Temperature';
if(isset($_GET['hours'])) {$parm = $parm .'&hours='. $_GET['hours'];}
if(isset($_GET['name'])) {$parm = $parm .'&name=' . $_GET['name'];}
if(isset($_GET['reset'])) {$parm = $parm .'&reset=' . $_GET['reset'];}
if(isset($_GET['box'])) {$parm = $parm .'&box=' . $_GET['box'];}
if(isset($_GET['date'])) {$parm = $parm .'&reset=' . $_GET['date'];}
header ('Location: http://' . $host . '/' . $path . '/lchart.php?' . $parm);
?>
