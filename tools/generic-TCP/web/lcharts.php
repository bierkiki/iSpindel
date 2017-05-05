<?php
// Show up to 3 linecharts
// GET Parameters:
// hours = number of hours before now() to be displayed
// name = iSpindle name
// reset = true, false defines start of timeline
// var1  = Name of first variable for first chart
// var2  = Name of variable for second chart
// var3  = Name of variable for third chart

include_once("include/common_db.php");
include_once("include/common_db_query.php");

// Check GET parameters
if (!isset($_GET['hours'])) {$_GET['hours'] = defaultTimePeriod;} else {$_GET['hours'] = $_GET['hours'];}
if (!isset($_GET['name']))  {$_GET['name'] = defaultName;}        else {$_GET['name']  = $_GET['name'];}
if (!isset($_GET['reset'])) {$_GET['reset'] = defaultReset;}      else {$_GET['reset'] = $_GET['reset'];}
if (!isset($_GET['var1']))  {$_GET['var1'] = defaultVar;}         else {$_GET['var1']  = $_GET['var1'];}
if (!isset($_GET['var2']))  {$_GET['var2'] = '';}                 else {$_GET['var2']  = $_GET['var2'];}
if (!isset($_GET['var3']))  {$_GET['var3'] = '';}                 else {$_GET['var3']  = $_GET['var3'];}

// Number of linecharts:
// check var1, default is Angle
if ($_GET['var1'] == '') {
  $_GET['var1'] = 'Angle';
}

// check var2 and var3 no default
if ($_GET['var2'] == '') {
  $varNo = 1;
} else {
  if ($_GET['var3'] == '') {
    $varNo = 2;
  } else {
    $varNo = 3;
  }
}

// check variables:
// array of known fields and their names, formats, ...:
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
// if new, add variable to $dictArray :
$knownVar = array('Angle', 'Temperature');
if (!in_array($_GET['var1'], $knownVar, true)) {
  $newDict1 = array(
      $_GET['var1'] => array(
          'txtDE' => $_GET['var1'],
          'Einheit' => ''
      )
  );
  $dictArray = $dictArray + $newDict1;
};
if ($varNo > 1) {
  if (!in_array($_GET['var2'], $knownVar, true) and $_GET['var1'] != $_GET['var2']) {
    $newDict2 = array(
        $_GET['var2'] => array(
            'txtDE' => $_GET['var2'],
            'Einheit' => ''
        )
    );
    $dictArray = $dictArray + $newDict2;
  }
}
if ($varNo > 2) {
  if (!in_array($_GET['var3'], $knownVar, true) and $_GET['var1'] != $_GET['var3'] and $_GET['var2'] != $_GET['var3']) {
    $newDict3 = array(
        $_GET['var3'] => array(
            'txtDE' => $_GET['var3'],
            'Einheit' => ''
        )
    );
    $dictArray = $dictArray + $newDict3;
  }
}

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

// Database query:
if ($varNo == 3) {
  list($var1, $var2, $var3) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['var1'], $_GET['var2'], $_GET['var3']);
} else
if ($varNo == 2) {
  list($var1, $var2) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['var1'], $_GET['var2']);
} else {
  list($var1) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['var1']);
}
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
    <script src="include/highcharts.js"></script>

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
      .wrapper {
        padding-top: 1%;
        margin-left: 1%;
        margin-right: 1%;
        height:100%;
        position:relative;
      }
      .spacer {
        height: 10px;
      }

      .highcharts-root{
        border-radius: 7px;
      }
      .highcharts-title {
        fill: #5e5e5e  !Important;
      }
    </style>


    <script type="text/javascript">
      $(function ()
      {
        var chart;

        $(document).ready(function ()
        {
          Highcharts.setOptions({
            global: {
              timezone: 'Europe/Berlin'
            },
            lang: {
              shortMonths: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez']
            },
            title: {
              align: 'left',
              text: '<?php echo $_GET['name']; ?>',
            },

            xAxis: {
              type: 'datetime',
              gridLineWidth: 1,
              title: {
                text: 'Uhrzeit'
              }
            },

            yAxis: [{
                startOnTick: false,
                endOnTick: false,
                /*min: 0,
                 max: 90,*/
                labels: {
                  align: 'left',
                  x: 3,
                  y: 16
                },
                showFirstLabel: false
              }],
            tooltip: {
              crosshairs: [true, true]
            },
            legend: {
              enabled: true
            },
            credits: {
              enabled: false
            }
          });

          <?php
          if ($varNo > 0) {
            echo lchartTmpl($renderTo = "chart1", $txtDE = $dictArray[$_GET['var1']]["txtDE"], $Einheit = $dictArray[$_GET['var1']]["Einheit"], $var = $var1);
          }
          if ($varNo > 1) {
            echo lchartTmpl($renderTo = "chart2", $txtDE = $dictArray[$_GET['var2']]["txtDE"], $Einheit = $dictArray[$_GET['var2']]["Einheit"], $var = $var2);
          }

          if ($varNo > 2) {
            echo lchartTmpl($renderTo = "chart3", $txtDE = $dictArray[$_GET['var3']]["txtDE"], $Einheit = $dictArray[$_GET['var3']]["Einheit"], $var = $var3);
          }
          ?>
        });
      });
    </script>
  </head>
  <body>

    <div id="header">
      <img src="./img/iSpindel.svg" height="40px">
      iSpindel: DIY elektronische Bierspindel
    </div>

    <?php
    for ($i = 1; $i <= 3; $i++) {
      if ($varNo >= $i) {
        echo '<div class="wrapper">
                <div id="chart' . $i . '" ></div>
              </div>
              <div class="spacer"></div>
              ';
      }
    }
    ?>
  </body>
</html>