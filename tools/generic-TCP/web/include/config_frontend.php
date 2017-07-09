<?php
/* 
  configuration for visualizer for iSpindle using genericTCP with mySQL 
  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com
 
  layout is cinfigured here.
 
  For the original project itself, see: https://github.com/universam1/iSpindel  

  kiki, July 7 2017
*/

// ****************************************************************************
// idears: 
// $knownVar aus $dictArray bestimmen
// ****************************************************************************

// **************************************************************************** 
// configure defaults for charts here:
// **************************************************************************** 
define("defaultTimePeriod", 24);      // Timeframe for chart
define("defaultReset",  0);           // Flag for Timeframe for chart 
define("defaultName", 'iSpindel000'); // Name of iSpindle
define("defaultVar", 'Angle');        // Variable to be displayed
define("defaultBox", 1);              // Flag, whether boxes are displayed
define("defaultTab", 0);              // Flag, whether data table is displayed
define("defaultMaxis", 0);            // Flag, whether multiple axis are displayed



// ****************************************************************************
// definition of colors for the charts created by dashboard.php
// ****************************************************************************
$lColor = array(
      0 => '#C31028', // rot
      1 => '#0000FF', // blau
      2 => '#32CD32', // limegreen
      3 => '#ba4a00', // hellblau
      4 => '#708090', // slategrey
      5 => '#FF9900', // orange
      6 => '#FF1493', // deeppink
);
// ****************************************************************************
// array of known fields and their names, formats, ...:
// ****************************************************************************
$knownVar = array('Angle', 'Temperature', 'Battery', 'Gravity');
$dictArray = array(
  'Angle' => array(
      'txtDE' => 'Winkel',
      'Einheit' => '°'
  ),
  'Temperature' => array(
      'txtDE' => 'Temperatur',
      'Einheit' => '°C'
  ),
  'Battery' => array(
      'txtDE' => 'Batteriespannung',
      'Einheit' => 'V'
  ),
  'Gravity' => array(
      'txtDE' => 'Restextrakt',
      'Einheit' => '°P'
          
  ),
  'unixtime' => array(
      'txtDE' => 'Timestamp',
      'Einheit' => ''
  )
);
?>