<?php
/*
  Show up to 3 linecharts
  GET Parameters:
  hours : number of hours before now() to be displayed
  name  : iSpindle name
  reset : true, false defines start of timeline
  var1  : Name of first variable for first chart
  var2  : Name of variable for second chart
  var3  : Name of variable for third chart
  box   : 1 (with boxes with current value) or 0 (no boxes)
  date  : Datum (TT.MM.YYYY): timeline of chart is from max reset-timestamp < date to min reset-timestamp < date

  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com

  For the original project itself, see: https://github.com/universam1/iSpindel

  kiki, May 10 2017
 */

// ****************************************************************************
//
// erste Vorabversion !!!!
//
// ToDo: siehe lchart.php
// ****************************************************************************

include_once("include/common_db.php");
include_once("include/common_db_query.php");
include_once("include/common_frontend.php");

// Check GET parameters
if (!isset($_GET['hours'])) {$_GET['hours'] = defaultTimePeriod;} else {$_GET['hours'] = $_GET['hours'];}
if (!isset($_GET['name']))  {$_GET['name']  = defaultName;}       else {$_GET['name']  = $_GET['name'];}
if (!isset($_GET['reset'])) {$_GET['reset'] = defaultReset;}      else {$_GET['reset'] = $_GET['reset'];}
if (!isset($_GET['var1']))  {$_GET['var1']  = defaultVar;}        else {$_GET['var1']  = $_GET['var1'];}
if (!isset($_GET['var2']))  {$_GET['var2']  = '';}                else {$_GET['var2']  = $_GET['var2'];}
if (!isset($_GET['var3']))  {$_GET['var3']  = '';}                else {$_GET['var3']  = $_GET['var3'];}
if (!isset($_GET['box' ]))  {$_GET['box']   = defaultBox;}        else {$_GET['box']   = $_GET['box'];}
if (!isset($_GET['date' ])) {$_GET['date']  = '';}                else {$_GET['date']  = $_GET['date'];}

//
// ToDo: kein chart, wenn Variable doppelt
//
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

// check, whether variables are known:
check_known_variable($_GET['var1']);
check_known_variable($_GET['var2']);
check_known_variable($_GET['var3']);

// Database query:
if ($varNo == 3) {
  list($var1, $var2, $var3) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['var1'], $_GET['var2'], $_GET['var3']);
} else
if ($varNo == 2) {
  list($var1, $var2) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['var1'], $_GET['var2']);
} else {
  list($var1) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['var1']);
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

    <style>
      html {
        font-family: "Open Sans", "Lucida Grande", "Lucida Sans Unicode", Arial, Helvetica, sans-serif;
        background:#5e5e5e;
        color: #5e5e5e;
      }
      #header {
        margin-top: 10px;
        margin-left: 1%;
        margin-right: 1%;
        height:50px;
        font-size: 25px;
        padding-top: 10px;
        padding-left: 25px;
        background:#93E579;
        border-radius: 7px;
      }
      #footer {
        margin-top: 10px;
        margin-left: 1%;
        margin-right: 1%;
        font-size: 20px;
        padding: 7px 25px 7px 25px;
        background:#93E579;
        border-radius: 7px;        
      }
      .wrapper {
        padding-top: 1%;
        margin-left: 1%;
        margin-right: 1%;
        height:100%;

      }
      .spacer {
        height: 10px;
      }
      .boxwrapper {
        padding-top: 1%;
        margin-left: 1%;
        margin-right: 1%;
        width:99%;
        display: flex;
      }
      a {color:#5e5e5e;
         text-decoration: none;
         float:right;
      }
      .box {
        margin-top: 1%;
        height:200px;
        width:10px; /* Haupsache gleich, da flex-grow */
        background: white;
        font-size: 20px;
        color: #5e5e5e;
        flex-grow: 1;
        border-radius: 7px;
        margin-right: 1%;
      }

      .box_upper{
        background:#888888;
        color:white;
        padding: 25px 25px 0 25px;
        height:75px;
        border-radius: 7px  7px 0 0;
      }
      .box >p, .box_left>p, .box_right >p{
        margin-left: 25px;
      }

      .clear {
        clear: both;
      }
      .value{
        color: #C31028;
        font-weight:600;
        font-size: 30px;
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
              align: 'left'
            },

            xAxis: {
              type: 'datetime',
              gridLineWidth: 1
            },

            yAxis: [{
                startOnTick: true,
                endOnTick: true,
                gridLineWidth: 1,
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
              enabled: false
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


      // Update the count down every 1 second
      var x = setInterval(function () {

      // Find the distance between now an the count down date
      var distance = new Date().getTime()
              - <?php
$last = substr($var1, strrpos($var1, "["), 20);
echo substr($last, 1, strrpos($last, ",") - 1);
?>
      ;
              // Time calculations for days, hours, minutes and seconds
              var days = Math.floor(distance / (1000 * 60 * 60 * 24));
              var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
              var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
              var seconds = Math.floor((distance % (1000 * 60)) / 1000);
              var vorText = 'vor: ';
              if (days > 0) {
      vorText = vorText + days + " T ";
      }
      if (hours > 0) {
      vorText = vorText + hours + " h ";
      }
      if (distance > 60000 && days == 0) {
      vorText = vorText + minutes + " min ";
      }
      if (days == 0) {
      vorText = vorText + seconds + "s <br/>";
      }

    <?php
    if ($_GET['box'] == true) {
      for ($i = 1; $i <= $varNo; $i++) {
        echo 'document.getElementById("since' . $i . '").innerHTML = vorText;';
      }
      echo '}, 1000);';
    } else {
      echo '}, 1000000000000000);';
    }
    ?>
    </script>

  </head>
  <body>

    <div id="header">
      <img src="./img/iSpindel.svg" height="40px"/>
      iSpindel: <?php echo $_GET['name']; ?>
    </div>

    <script src="include/highcharts.js"></script>

    <?php
    if ($_GET['box'] == 1) {
      echo '<div class="boxwrapper">';
      $value = array(
          1 => str_replace(']', '', substr($var1, strrpos($var1, ",") + 2, 10))
      );
      if ($varNo >= 2) {
        $newval2 = array(2 => str_replace(']', '', substr($var2, strrpos($var2, ",") + 2, 10)));
        $value = $value + $newval2;
      }
      if ($varNo >= 3) {
        $newval3 = array(3 => str_replace(']', '', substr($var3, strrpos($var3, ",") + 2, 10)));
        $value = $value + $newval3;
      }

      for ($i = 1; $i <= $varNo; $i++) {
        echo
        '<div class="box">
               <div class="box_upper">'
        . $dictArray[$_GET['var' . $i]]["txtDE"]
        . '<br/>'
        . '<div class="value">'
        . $value[$i]
        . $dictArray[$_GET['var' . $i]]["Einheit"]
        . '</div>
               </div>
               <p id="since' . $i . '"></p>
             </div>';
      }
      echo '</div>
           </div>
           <div class="spacer"></div>
           	';
    }

    for ($i = 1; $i <= $varNo; $i++) {
      echo '<div class="wrapper">
                <div id="chart' . $i . '" ></div>
              </div>
              <div class="spacer"></div>
              ';
    }
    ?>

    <div id="footer">
      iSpindel: DIY elektronische Bierspindel <a href="https://github.com/universam1/iSpindel">github.com/universam1/iSpindel</a>
    </div>

  </body>
</html>
