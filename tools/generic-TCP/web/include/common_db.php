<?php
// configuration for database and defaults for charts  
//
//  For the original project itself, see: https://github.com/universam1/iSpindel  
//  
//  Tozzi (stephan@sschreiber.de), Mar 15 2017
//  kiki, May 19 2017

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
 
// **************************************************************************** 
// configure defaults for charts here:
// **************************************************************************** 
define("defaultTimePeriod", 24);      // Timeframe for chart
define("defaultReset",  0);           // Flag for Timeframe for chart 
define("defaultName", 'iSpindel000'); // Name of iSpindle
define("defaultVar", 'Angle');        // Variable to be displayed
define("defaultBox", 1);              // Flag, whether boxes are displayed
define("defaultTab", 0);              // Flag, whether data table is displayed
?>
