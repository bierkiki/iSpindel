<?php
// configuration for database of iSpindel
//
//  For the original project itself, see: https://github.com/universam1/iSpindel  
//  
//  Tozzi (stephan@sschreiber.de), Mar 15 2017
//  kiki, July 7 2017

// **************************************************************************** 
// configure your database connection here:
// **************************************************************************** 

define('DB_SERVER',"localhost");
define('DB_NAME',"iSpindle");
define('DB_USER',"iSpindle");
define('DB_PASSWORD',"ohyeah");
 
 
$conn = mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD);
if(is_resource($conn))
{
  mysql_select_db(DB_NAME, $conn);
  mysql_query("SET NAMES 'ascii'", $conn);
  mysql_query("SET CHARACTER SET 'ascii'", $conn);
}
?>