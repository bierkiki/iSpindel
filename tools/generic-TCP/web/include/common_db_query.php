<?php
/* 
  Visualizer for iSpindle using genericTCP with mySQL 
  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com
 
  Data access via mySQL for the charts is defined in here.
 
  For the original project itself, see: https://github.com/universam1/iSpindel  
  
  Tozzi (stephan@sschreiber.de), Mar 15 2017
  kiki, May 10 2017
*/

// ****************************************************************************
// ToDo: Abfrage auf reset nicht richtig, wenn default = true (boolean <-> gef�llt)
// ToDo: function getChartValues, getChartValuesPlato entfernen
// ToDo: function getCurrentValues parametergesteuert
// ToDo: Plato4-Berechnung in DB verlagern und getValues verwenden
// ****************************************************************************

// ****************************************************************************
//
// ****************************************************************************
// remove last character from a string
function delLastChar($string="")
{
  $t = substr($string, 0, -1);
  return($t);
}
// ****************************************************************************
//
// ****************************************************************************
// Get values from database for selected spindle, between now and timeframe in hours ago
function getValues($iSpindleID=defaultName, $timeFrameHours=defaultTimePeriod, $reset=defaultReset, $date='', $var1='Angle', $var2='', $var3='')
{
   if ($date != '') {$reset = 0;}
 
   if ($reset==1) {
   $where_part = "AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '".$iSpindleID."')";
   }
   else if ($date != '') {
   $where_part = "AND Timestamp  > (Select max(Timestamp) FROM Data WHERE 
     unix_timestamp(Timestamp) < unix_timestamp(STR_TO_DATE('".$date."', '%d.%m.%Y')) AND ResetFlag = true AND Name = '".$iSpindleID."')
     AND Timestamp  < (Select min(Timestamp) FROM Data  WHERE 
     unix_timestamp(Timestamp) > unix_timestamp(STR_TO_DATE('".$date."', '%d.%m.%Y')) AND ResetFlag = true AND Name = '".$iSpindleID."')";
   }
   else if ($timeFrameHours != '') {
   $where_part = "AND Timestamp >= date_sub(NOW(), INTERVAL ". $timeFrameHours ." HOUR) 
                  and Timestamp <= NOW()";                  
   }
   else {$where_part="";}
   
                         
   $var2 != '' ? $var2sel = ','.$var2 : $var2sel = '';
   $var3 != '' ? $var3sel = ','.$var3 : $var3sel = '';
   
   $q_sql = mysql_query("SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, $var1" . $var2sel . $var3sel
                         ." FROM Data "
                         . "WHERE Name = '".$iSpindleID."' "
                         . $where_part
                         ." ORDER BY Timestamp ASC") or die(mysql_error());
  // retrieve number of rows
  $rows = mysql_num_rows($q_sql);
  if ($rows > 0)
  {
    $val1 = '';
    $val2 = '';
    $val3 = '';
    
    // retrieve and store the values as CSV lists for HighCharts
    while($r_row = mysql_fetch_array($q_sql))
    {
      $jsTime = $r_row['unixtime'] * 1000;
      $val1         .= '['.$jsTime.', '.$r_row[$var1].'],';
      if ($var2 != '') {
        $val2         .= '['.$jsTime.', '.$r_row[$var2].'],';
      }
      if ($var3 != '') {
        $val3         .= '['.$jsTime.', '.$r_row[$var3].'],';
      }
    }
    
    // remove last comma from each CSV
    $val1         = delLastChar($val1);
    $val2         = delLastChar($val2);
    $val3         = delLastChar($val3);
    
    if ($var2 != '' and $var3 == '') {
      return array($val1, $val2);
    }
    else if ($var2 != '' and $var3 != '') {
      return array($val1, $val2, $val3);
    }
    else {
       return array($val1);
    }   
  }
}
 
// ****************************************************************************
//
// ****************************************************************************
// Get values from database for selected spindle, between now and timeframe in hours ago
function getChartValues($iSpindleID=defaultName, $timeFrameHours=defaultTimePeriod, $reset=defaultReset)
{
   if ($reset)
   {
   $where="WHERE Name = '".$iSpindleID."' 
                  AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '".$iSpindleID."')";
   }  
   else
   {
  $where ="WHERE Name = '".$iSpindleID."' 
            AND Timestamp >= date_sub(NOW(), INTERVAL ".$timeFrameHours." HOUR) 
            and Timestamp <= NOW()";
   }  
   $q_sql = mysql_query("SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle
                         FROM Data " 
                         .$where 
                         ." ORDER BY Timestamp ASC") or die(mysql_error());
                         
  // retrieve number of rows
  $rows = mysql_num_rows($q_sql);
  if ($rows > 0)
  {
    $valAngle = '';
    $valTemperature = '';
    
    // retrieve and store the values as CSV lists for HighCharts
    while($r_row = mysql_fetch_array($q_sql))
    {
      $jsTime = $r_row['unixtime'] * 1000;
      $valAngle         .= '['.$jsTime.', '.$r_row['angle'].'],';
      $valTemperature   .= '['.$jsTime.', '.$r_row['temperature'].'],';
    }
    
    // remove last comma from each CSV
    $valAngle         = delLastChar($valAngle);
    $valTemperature   = delLastChar($valTemperature);
    return array($valAngle, $valTemperature);
  }
}
// ****************************************************************************
//
// ****************************************************************************
// Get values from database including gravity (Fw 5.0.1 required) for selected spindle, between now and timeframe in hours ago
function getChartValuesPlato($iSpindleID=defaultName, $timeFrameHours=defaultTimePeriod, $reset=defaultReset)
{
   if ($reset)
   {
   $where="WHERE Name = '".$iSpindleID."' 
                  AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '".$iSpindleID."')";
   }  
   else
   {
  $where ="WHERE Name = '".$iSpindleID."' 
            AND Timestamp >= date_sub(NOW(), INTERVAL ".$timeFrameHours." HOUR) 
            and Timestamp <= NOW()";
   }  
  $q_sql = mysql_query("SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, gravity
                          FROM Data " 
                         .$where 
                         ." ORDER BY Timestamp ASC") or die(mysql_error());
  // retrieve number of rows
  $rows = mysql_num_rows($q_sql);
  if ($rows > 0)
  {
    $valAngle = '';
    $valTemperature = '';
    $valGravity = '';
    // retrieve and store the values as CSV lists for HighCharts
    while($r_row = mysql_fetch_array($q_sql))
    {
      $jsTime = $r_row['unixtime'] * 1000;
      $valAngle         .= '['.$jsTime.', '.$r_row['angle'].'],';
      $valTemperature   .= '['.$jsTime.', '.$r_row['temperature'].'],';
      $valGravity       .= '['.$jsTime.', '.$r_row['gravity'].'],';
    }
    // remove last comma from each CSV
    $valAngle         = delLastChar($valAngle);
    $valTemperature   = delLastChar($valTemperature);
    $valGravity       = delLastChar($valGravity);
    return array($valAngle, $valTemperature, $valGravity);
  }
}
// ****************************************************************************
//
// ****************************************************************************
// Get current values (angle, temperature, battery)
function getCurrentValues($iSpindleID=defaultName)
{
   $q_sql = mysql_query("SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, battery
                FROM Data
                WHERE Name = '".$iSpindleID."'
                ORDER BY Timestamp DESC LIMIT 1") or die (mysql_error());
  
  $rows = mysql_num_rows($q_sql);                                                                                         
  if ($rows > 0)                                                                                                          
  {
    $r_row = mysql_fetch_array($q_sql);
    $valTime = $r_row['unixtime'];
    $valTemperature = $r_row['temperature'];
    $valAngle = $r_row['angle'];
    $valBattery = $r_row['battery'];
    return array($valTime, $valTemperature, $valAngle, $valBattery);  
  }
}
                        
// ****************************************************************************
//
// ****************************************************************************
// Get calibrated values from database for selected spindle, between now and [number of hours] ago
// Old Method for Firmware before 5.x
function getChartValuesPlato4($iSpindleID=defaultName, $timeFrameHours=defaultTimePeriod, $reset=defaultReset)
{
    $isCalibrated = 0;  // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
   if ($reset)
   {
   $where="WHERE Name = '".$iSpindleID."' 
                  AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '".$iSpindleID."')";
   }  
   else
   {
  $where ="WHERE Name = '".$iSpindleID."' 
            AND Timestamp >= date_sub(NOW(), INTERVAL ".$timeFrameHours." HOUR) 
            and Timestamp <= NOW()";
   }  
   
   $q_sql = mysql_query("SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle
                           FROM Data " 
                           .$where 
                          ." ORDER BY Timestamp ASC") or die(mysql_error());
                     
    // retrieve number of rows
    $rows = mysql_num_rows($q_sql);
    if ($rows > 0)
    {
     // get unique hardware ID for calibration
     $u_sql = mysql_query("SELECT ID FROM Data WHERE Name = '".$iSpindleID."' ORDER BY Timestamp DESC LIMIT 1") or die(mysql_error());
     $rowsID = mysql_num_rows($u_sql);
     if ($rowsID > 0)
     {
        // try to get calibration for iSpindle hardware ID
        $r_id = mysql_fetch_array($u_sql);
        $uniqueID = $r_id['ID'];
        $f_sql = mysql_query("SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysql_error());
        $rows_cal = mysql_num_rows($f_sql);
        if ($rows_cal > 0)
        {
            $isCalibrated = 1;
            $r_cal = mysql_fetch_array($f_sql);
            $const1 = $r_cal['const1'];
            $const2 = $r_cal['const2'];
            $const3 = $r_cal['const3'];
        }
     }
     // retrieve and store the values as CSV lists for HighCharts
     while($r_row = mysql_fetch_array($q_sql))
     {
         $jsTime = $r_row['unixtime'] * 1000;
         $angle = $r_row['angle'];
         $dens = $const1 * $angle ** 2 - $const2 * $angle + $const3;   // complete polynome from database
                         
         $valAngle         .= '['.$jsTime.', '.$angle.'],';
         $valDens          .= '['.$jsTime.', '.$dens.'],';
         $valTemperature   .= '['.$jsTime.', '.$r_row['temperature'].'],';
     }
     // remove last comma from each CSV
     $valAngle         = delLastChar($valAngle);
     $valTemperature   = delLastChar($valTemperature);
     return array($isCalibrated, $valDens, $valTemperature, $valAngle);
    }
 }
?>