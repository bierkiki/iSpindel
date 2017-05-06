<?php
/* 
  Common funtion, definitions for visualizer for iSpindle using genericTCP with mySQL 
  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com
 
  common functions for layout are defined in here.
 
  For the original project itself, see: https://github.com/universam1/iSpindel  

  kiki, May 06 2017
*/

// ****************************************************************************
// ToDo: $knownVar aus $dictArray bestimmen
// ****************************************************************************

// ****************************************************************************
// array of known fields and their names, formats, ...:
// ****************************************************************************
// ToDo: $knownVar aus $dictArray bestimmen
$knownVar = array('Angle', 'Temperature', 'Battery', 'Gravity');
$dictArray = array(
  'Angle' => array(
      'txtDE' => 'Winkel [°]',
      'Einheit' => '°'
  ),
  'Temperature' => array(
      'txtDE' => 'Temperatur [°C]',
      'Einheit' => '°C'
  ),
  'Battery' => array(
      'txtDE' => 'Batteriespannung [V]',
      'Einheit' => 'V'
  ),
  'Gravity' => array(
          'txtDE' => 'Restextrakt [°P]',
          'Einheit' => '°P'
  )
);


// ****************************************************************************
//
// ****************************************************************************
function check_known_variable($checkVar) {
  global $knownVar, $dictArray;
  // if new, add variable to $dictArray and $knownVar  :
  if (!in_array($checkVar, $knownVar, true) and $checkVar != '') {
    $newDict1 = array(
        $checkVar => array(
            'txtDE' => $checkVar,
            'Einheit' => ''
        )
    );
    $dictArray = $dictArray + $newDict1;
    array_push($knownVar, $checkVar);
  };
}
// ****************************************************************************
//
// ****************************************************************************
function lchartTmpl($renderTo, $txtDE, $Einheit, $var) {
  $tmpl = "
      $('#" . $renderTo . "').highcharts({
        chart:{
          renderTo: '" . $renderTo . "'
        },
        yAxis: [{
          title:{
            text :'" . $txtDE . "'
          },
          labels: {
            formatter: function(){
              return this.value +'" . $Einheit . "'
            }
          }
        }],
        tooltip:{
          formatter: function(){
            if(this.series.name == '" . $txtDE . "') {
              return '<b>" . $txtDE . "</b> um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y + '" . $Einheit . "';
            }
          }
        },
        series:[{
          name: '" . $txtDE . "'   ,
          data: [" . $var . "],
          color: '#C31028',
          marker:{
            symbol: 'square',
            enabled: false,
            states:{
              hover:{
                symbol: 'square',
                enabled: true,
                radius: 8
              }
            }
          }
        }]
      });
      ";
  return $tmpl;
}

// ****************************************************************************
//
// ****************************************************************************

?>