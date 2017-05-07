<?php
/* 
  Show the linechart with 2 variables
  GET Parameters:
  hours = number of hours before now() to be displayed
  name = iSpindle name
  reset = true, false defines start of timeline
  var1  = Name of first variable for left y-axis
  var2  = Name of second variable for right y-axis, optional
  
  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com
 
  For the original project itself, see: https://github.com/universam1/iSpindel  

  kiki, May 06 2017
*/

// ****************************************************************************
// ToDo: style, html-header auslagern
// ToDo: kein chart, wenn Variable doppelt
// ToDo: Schleife für $var1, $var2, $var3
// ToDo: Superglobals ($_GET['hours'], ...) nicht zugreifen
// ToDo: Legende rechts oben, Punkte (vgl. Ubidots)
// ****************************************************************************
 
include_once("include/common_db.php");
include_once("include/common_db_query.php");
include_once("include/common_frontend.php");

// Check GET parameters (for now: Spindle name and Timeframe to display)
if (!isset($_GET['hours'])) {$_GET['hours'] = defaultTimePeriod;} else {$_GET['hours'] = $_GET['hours'];}
if (!isset($_GET['name']))  {$_GET['name'] = defaultName;}        else {$_GET['name']  = $_GET['name'];}
if (!isset($_GET['reset'])) {$_GET['reset'] = defaultReset;}      else {$_GET['reset'] = $_GET['reset'];}
if (!isset($_GET['var1']))  {$_GET['var1'] = defaultVar;}         else {$_GET['var1']  = $_GET['var1'];}
if (!isset($_GET['var2']))  {$_GET['var2'] = '';}                 else {$_GET['var2']  = $_GET['var2'];}

// check var1, default is Angle
if ($_GET['var1'] == '') {
  $_GET['var1'] = 'Angle';
}

// check var2 and var3 no default
if ($_GET['var2'] == '') {
  $varNo = 1;
} else {
    $varNo = 2;
  }

// check, whether variables are known:
check_known_variable($_GET['var1']); 
check_known_variable($_GET['var2']); 

// Database query:
if ($varNo > 1) list($var1, $var2) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'],$_GET['var1'],$_GET['var2']);
else list($var1) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'],$_GET['var1']);
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
    Highcharts.setOptions({
      global: {
        timezone: 'Europe/Berlin'
      },
      lang: {
        shortMonths: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']
      },
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
    max: 90,*/
    title: 
        {
           text :'<?php echo $dictArray[$_GET['var1']]["txtDE"];?>'    
        },      
    labels: 
        {
          align: 'left',
          x: 3,
          y: 16,
          formatter: function() 
          {
            return this.value +'<?php echo $dictArray[$_GET['var1']]["Einheit"];?>'    
          }
        },
    showFirstLabel: false
      }
    <?php if ($varNo > 1) {
       echo 
      ",{
         // linkedTo: 0,
     /*
     startOnTick: false,
     endOnTick: false,
     min: -5,
     max: 35,
     */
     gridLineWidth: 0,
         opposite: true,
         title: {
           text :'".$dictArray[$_GET['var2']]["txtDE"]."'
         },
         labels: {
            align: 'right',
            x: -3,
            y: 16,
          formatter: function() 
          {
            return this.value +'".$dictArray[$_GET['var2']]["Einheit"]."'    
          }
         },
    showFirstLabel: false
        }
    ";} ?>           
      ],
      
      tooltip: 
      {
    crosshairs: [true, true],
        formatter: function() 
        {
         if(this.series.name == '<?php echo $dictArray[$_GET['var1']]["txtDE"];?>') {
             return '<b><?php echo $dictArray[$_GET['var1']]["txtDE"];?></b> um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y + '<?php echo $dictArray[$_GET['var1']]["Einheit"];?>';
         } else {
             <?php if ($varNo > 1) {
             echo "return '<b>".$dictArray[$_GET['var2']]["txtDE"]."</b> um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y + '".$dictArray[$_GET['var2']]["Einheit"]."';";}?>
                
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
          name: '<?php echo $dictArray[$_GET['var1']]["txtDE"];?>'   ,  
      color: '#FF0000',
          data: [<?php echo $var1;?>],
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
  <?php if ($varNo > 1) {echo 
      ",
      {
          name: '".$dictArray[$_GET['var2']]["txtDE"]."'   ,  
      yAxis: 1,
      color: '#0000FF',
          data: [".$var2."],
        
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
  ";}?> 
      ] //series      
    });
  }); 
});
</script>
</head>
<body>
 
  <div id="header">
    <img src="./img/iSpindel.svg" height="40px">
          iSpindel: DIY elektronische Bierspindel
    </div>    
  <script src="include/highcharts.js"></script>
  <div id="container" ></div>

</body>
</html>