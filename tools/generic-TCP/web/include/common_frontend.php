<?php
/* 
  Common funtion for visualizer for iSpindle using genericTCP with mySQL 
  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com
 
  common functions for layout are defined in here.
 
  For the original project itself, see: https://github.com/universam1/iSpindel  

  kiki, July 7 2017
*/

// ****************************************************************************
// check of parameter varlist 
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
// template for a part of the linechart
// ****************************************************************************
function lchartTmpl($txtDE, $Einheit, $var) {
  $tmpl = "
        ,yAxis: [{
          labels: {
            formatter: function(){
              return this.value +'" . $Einheit . "';
            }
          }
          ,title: {
            text :''
          }          
          
        }],
        tooltip:{
          formatter: function(){
            if(this.series.name === '" . $txtDE . "') {
              return '<b>" . $txtDE . "</b> um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y + '" . $Einheit . "';
            }
          }
        },
        title: {
            text :'" . $txtDE . "'
        }"
                
        ;
  return $tmpl;
}
// ****************************************************************************
// template for a part of the linechart
// ****************************************************************************
function lchartTmplyAxis($txtDE, $Einheit, $var, $index) {
global $lColor;
if ($index > 0) {$add = ', opposite: true';} else {$add = '';}
if (1) {$addColor = ",style: {color :'" .  $lColor[$index] . "'} ";} else {$addColor = '';}

  $tmpl = "
          {
          labels: {
            formatter: function(){
              return this.value +'" . $Einheit . "';
            }
          "
          .$addColor
          ."
            
          },
          
          title: {
            text :'" . $txtDE . "'
          "
          .$addColor
          ."
          }
          "
          .$add
          ."
          }
        "
        ;
  return $tmpl;
}


// ****************************************************************************
// template for a part of the linechart
// ****************************************************************************
function lchartTmplSeries($StxtDE, $Svar, $index) {
global $lColor;
if ($index > 0) {$add = ' yAxis: ' . $index . ',';} else {$add = '';}

  $tmpl = "{data: [" . $Svar . "],
            name: '" . $StxtDE . "',
            color: '" .$lColor[$index] . "',
            "
            .$add
            ."
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
           }
          ";
  return $tmpl;
}

// ****************************************************************************
// template for a part of the linechart
// ****************************************************************************
function chartOptions() {
  global $dictArray;
  $varkeys = array_keys($dictArray);
  
  $add = '';
  for ($i = 0; $i < count($dictArray); $i++) {
    $add = $add 
      ."  if(this.series.name === '" . $dictArray[$varkeys[$i]]["txtDE"] . "') {
        return '<b>" . $dictArray[$varkeys[$i]]["txtDE"] . "</b> um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y + '" . $dictArray[$varkeys[$i]]["Einheit"] . "';
      }";   
  }

  $tmpl = "
            global: {
              timezone: 'Europe/Berlin'
            },
            lang: {
              shortMonths: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']
            },
            title: {
              align: 'left'
            },
            xAxis: {
              type: 'datetime',
              gridLineWidth: 1
            },
            tooltip: {
              crosshairs: [true, true], 
              formatter: function(){
              "
              . $add
              ."
              }
            },

            yAxis: [
              {startOnTick: true,endOnTick: true,gridLineWidth: 1,labels: {  align: 'left',  x: 3,  y: 16},showFirstLabel: false}            
            ]
            , credits: {
                enabled: false
              }
         ";
  return $tmpl;
}
?>