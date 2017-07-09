<?php

// Show the Density/Temperature chart
// GET Parameters:
// hours = number of hours before now() to be displayed
// name = iSpindle name
 
// ****************************************************************************
// ToDo: Plato4-Berechnung in DB verlagern und Redirect auf lchart.php
// ****************************************************************************
 
include_once("include/common_db.php");
include_once("include/common_db_query.php");
include_once("include/config_frontend.php");

// Check GET parameters (for now: Spindle name and Timeframe to display) 
if(!isset($_GET['hours'])) $_GET['hours'] = defaultTimePeriod; else $_GET['hours'] = $_GET['hours'];
if(!isset($_GET['name'])) $_GET['name'] = defaultName; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['reset'])) $_GET['reset'] = defaultReset; else $_GET['reset'] = $_GET['reset'];

list($isCalib, $dens, $temperature, $angle) = getChartValuesPlato4($_GET['name'], $_GET['hours'], $_GET['reset']);

?>

<!DOCTYPE html>
<html>
<head>
  <title>iSpindle Data</title>
  <meta http-equiv="refresh" content="120">
  <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
  <meta name="Description" content="iSpindle Fermentation Chart">
  <script src="include/jquery-3.1.1.min.js"></script>
  <script src="include/moment.min.js"></script>
  <script src="include/moment-timezone-with-data.js"></script>

  <link rel="stylesheet" href="./css/fonts.css" type="text/css"/>

  <style>
    html {
      font-family: "Open Sans", "Lucida Grande", "Lucida Sans Unicode", Arial, Helvetica, sans-serif;
      background:#5e5e5e;
      color: #5e5e5e;
    }
    #header {
      font-size: 25px;
      margin-top: 10px;
      margin-left: 1%;
      margin-right: 1%;
      height:50px;    
      padding-top: 10px;
      padding-left: 10px;
      background:#93E579;
      border-radius: 7px;
    }
    #footer {
      font-size: 25px;
      margin-top: 10px;
            margin-left: 1%;
            margin-right: 1%;
            height:50px;    
            padding-top: 10px;
            padding-left: 10px;
            background:#93E579;
      border-radius: 7px;
    }
    #container {
      font-size: 15px;
      margin-top: 10px;
      margin-left: 1%;
      margin-right: 1%;
      width:96%;
      height:86%;
      position:absolute;
    }

    .highcharts-root{
      border-radius: 7px;}

    .highcharts-title {
      fill: #5e5e5e  !Important;
    }
  
  </style>
  


<script type="text/javascript">
$(function () 
{
  var chart;
 
  $(document).ready(function() 
  {
                    
    if ('<?php echo $isCalib;?>' == '0')
    {
        document.write('<h2>iSpindel \'<?php echo $_GET['name'];?>\' ist nicht kalibriert.</h2>');
    }
    else
    {
        Highcharts.setOptions({
              global: {
                  timezone: 'Europe/Berlin'
              },
             lang: {
                  shortMonths: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',  'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']
             }
          });
                
        chart = new Highcharts.Chart(
        {
            chart:
            {
                renderTo: 'container'
            },
            title:
            {
                text: '<?php echo $_GET['name'];?>',
                align: 'left'
            },
            /*subtitle:
            {
              text: ' <?php               
                         if($_GET['reset']) 
                         {     
                            echo 'Temperatur und Restextrakt seit dem letzten Reset';
                         }
                         else
                         {
                            echo 'Temperatur und Restextrakt den letzten '.  $_GET['hours'] .  ' Stunden';
                         }
                      ?>',
              align: 'left'
            },*/
            xAxis:
            {
                type: 'datetime',
                gridLineWidth: 1,
                title:
            {
                text: 'Uhrzeit'
            }
            },
            yAxis: [
                {
                    startOnTick: false,
                    endOnTick: false,
                    /*min: 0,
                    max: 25,*/
                    title:
                    {
                        text: 'Extrakt %w/w'
                    },
                    labels:
                    {
                        align: 'left',
                        x: 3,
                        y: 16,
                        formatter: function()
                        {
                            return this.value + '°P'
                        }
                    },
                    showFirstLabel: false
                    },{
                    // linkedTo: 0,
                    startOnTick: false,
                    endOnTick: false,
                    /*min: -5,
                    max: 35,*/
                    gridLineWidth: 0,
                    opposite: true,
                    title: {
                        text: 'Temperatur'
                    },
                    labels: {
                        align: 'right',
                        x: -3,
                        y: 16,
                        formatter: function() 
                        {
                            return this.value +'°C'
                        }
                    },
                    showFirstLabel: false
                }
            ],
            tooltip:
            {
                crosshairs: [true, true],
                formatter: function() 
                {
                    if(this.series.name == 'Temperatur') {
                        return '<b>'+ this.series.name +' </b>um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y +'°C';
                    } else {
                        return '<b>'+ this.series.name +' </b>um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ Math.round(this.y * 100) / 100 +'°P';
                    }
                }
            },  
            legend: 
            {
                enabled: true
            },
            credits:
            {
                enabled: false
            },
            series:
            [
                {
                    name: 'Extrakt',
                    color: '#FF0000',
                    data: [<?php echo $dens;?>],
                    marker: 
                    {
                        symbol: 'square',
                        enabled: false,
                        states: 
                        {
                            hover:
                            {
                                symbol: 'square',
                                enabled: true,
                                radius: 8
                            }
                        }
                    }
                },
                {
                    name: 'Temperatur',
                    yAxis: 1,
                    color: '#0000FF',
                    data: [<?php echo $temperature;?>],
                    marker: 
                        {
                            symbol: 'square',
                            enabled: false,
                            states: 
                            {
                                hover:
                                {
                                symbol: 'square',
                                enabled: true,
                                radius: 8
                                }
                            }
                        }

                }
            ] //series      
            });
    }
  });
});
</script>
</head>
<body>
 
  <div id="header">
    <img src="./img/iSpindel.svg" height="40px">
          iSpindel: DIY elektronische Bierspindel
    </div>    

<div id="wrapper">
  <script src="include/highcharts.js"></script>
  <div id="container"></div>
</div>
 
</body>
</html>
