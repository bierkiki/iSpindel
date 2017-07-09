<?php
/*
  Show up to 8 linecharts, boxes and table
  GET Parameters:
  hours  : number of hours before now() to be displayed
  name   : iSpindle name
  reset  : true, false defines start of timeline
  varlist: commaseperated list of variables ro displayed or * for all
  box    : 1 (with boxes with current value) or 0 (no boxes)
  tab    : 1 (with table, all data from selected timeline) or 0 (no table)
  date   : Datum (TT.MM.YYYY): timeline of chart is from max reset-timestamp < date to min reset-timestamp < date

  Shows mySQL iSpindle data on the browser as a graph via Highcharts:
  http://www.highcharts.com

  For the original project itself, see: https://github.com/universam1/iSpindel

  kiki, July 7 2017
 */

// ****************************************************************************
// ideas:
// Different iSpindles: one page with one chart for each ispindle
// avoid limit of max 8 variables
// optimize css
// validation of parameters
// instead of table Data a view with more columns (TRE, EVG, ...)
// labels also in English, ... 
// ****************************************************************************

include_once("include/common_db.php");
include_once("include/common_db_query.php");
include_once("include/common_frontend.php");
include_once("include/config_frontend.php");

// Check GET parameters
if (!isset($_GET['hours']))    {$_GET['hours']   = defaultTimePeriod;}
if (!isset($_GET['name']))     {$_GET['name']    = defaultName;}      
if (!isset($_GET['reset']))    {$_GET['reset']   = defaultReset;}     
if (!isset($_GET['varlist']))  {$_GET['varlist'] = defaultVar;}       
if (!isset($_GET['box' ]))     {$_GET['box']     = defaultBox;}       
if (!isset($_GET['tab' ]))     {$_GET['tab']     = defaultTab;}       
if (!isset($_GET['date' ]))    {$_GET['date']    = '';}               
if (!isset($_GET['maxis' ]))   {$_GET['maxis']   = defaultMaxis;}       

$star = 0;
if ($_GET['varlist'] == '*') {
  $_GET['tab'] = 1;
  $star = 1;
  $q_sql = mysql_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'iSpindle' and TABLE_NAME = 'Data'") or die(mysql_error());

  // retrieve number of rows
  $rows = mysql_num_rows($q_sql);
  $_GET['varlist'] = '';
  while ($r_row = mysql_fetch_array($q_sql)) {
    if ($r_row['COLUMN_NAME'] != 'Timestamp') {
      $_GET['varlist'] .= $r_row['COLUMN_NAME'] . ',';
    }
  }
  $_GET['varlist'] = substr($_GET['varlist'], 0, -1);
}

// variables to array
$varArray = explode(',', $_GET['varlist']);
$varNo = count($varArray);

// check, whether variables are known:
for ($i = 0; $i < $varNo; $i++) {
  check_known_variable($varArray[$i]);
  ;
}

// wg. list(...) Aufrufe
if ($varNo >= 8) {
  $varNo = 8;
}

// Database query:
if ($varNo == 8) {
  list($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 7) {
  list($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 6) {
  list($values[0], $values[1], $values[2], $values[3], $values[4], $values[5]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 5) {
  list($values[0], $values[1], $values[2], $values[3], $values[4] ) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 4) {
  list($values[0], $values[1], $values[2], $values[3]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 3) {
  list($values[0], $values[1], $values[2]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} elseif ($varNo == 2) {
  list($values[0], $values[1]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
} else {
  list($values[0]) = getValues($_GET['name'], $_GET['hours'], $_GET['reset'], $_GET['date'], $_GET['varlist']);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo 'iSpindel: ' . $_GET['name']; ?></title>
    <meta http-equiv="refresh" content="120">
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
    <meta name="Description" content="iSpindle Fermentation Chart">
    <script src="include/jquery-3.1.1.min.js"></script>
    <script src="include/moment.min.js"></script>
    <script src="include/moment-timezone-with-data.js"></script>

    <link rel="shortcut icon" href="http:./img/iSpindel.svg"/>
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
        font-size: 25px;
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
         font-size: 25px;
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

      #wrappert , table{
        padding-top: 25px;
        padding-left: 1%;   
        margin-top: 1%;
        margin-left: 1%;
        margin-right: 1%;
        padding-bottom:25px;
        height: <?php if ($star != 1) {echo '250px;';} else {echo '450px;';} ?>
          width:98%;
        background: white;
        font-size: 15px;
        color: #5e5e5e;
        border-radius: 7px;
      }
      table {
        width: 98%;
        border-spacing: 0;
        border-collapse: collapse;
        font-size: 20px;
      }
      thead, tbody, tr, th, td { display: block;       
      }
      thead tr {
        width: 97%;
        width: -webkit-calc(98% - 16px);
        width:    -moz-calc(98% - 16px);
        width:         calc(98% - 16px);
      }
      tr:after { 
        content: ' ';
        display: block;
        visibility: hidden;
        clear: both;
      }
      tbody {
        width:98%;
        max-height: <?php if ($star != 1) {echo '250px;';} else {echo '450px;';} ?>
          overflow-y: auto;
        overflow-x: hidden;
      }
      tbody td, thead th {
        width: <?php echo floor(98 / ($varNo + 1) - 1) ?>%;  
        float: left;
      }
      thead th {
        font-weight:normal;
        background:#888888;
        color:white;
      }
      tr:nth-child(even){background-color: #dddddd;}

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
          <?php
            echo chartOptions(); 
          ?>
          });
          
          <?php
      
            if ($_GET['maxis'] == 0){
              for ($i = 0; $i < $varNo; $i++) {
                echo "$('#chart".$i."').highcharts({"
                  .'    chart:{'
                  ."      renderTo: 'chart".$i."'"
                  .'}';
              
              
                echo lchartTmpl($txtDE = $dictArray[$varArray[$i]]["txtDE"], $Einheit = $dictArray[$varArray[$i]]["Einheit"], $var = $values[$i] );
                echo ',series:[';
                echo lchartTmplSeries($txtDE = $dictArray[$varArray[$i]]["txtDE"], $var = $values[$i], $index = 0);
                echo ']'; // series
                echo '});';
              }
            }
            else {
              echo "$('#chart0').highcharts({"
                .'    chart:{'
                ."      renderTo: 'chart0'"
                .'}'
                ;
              echo ",title: {
                        text :'" . $_GET['name'] . "'
                     }";
              
              //*** yAxis ***   
              echo ' ,yAxis: [ ';
              for ($i = 0; $i < $varNo; $i++) {
                echo lchartTmplyAxis($txtDE = $dictArray[$varArray[$i]]["txtDE"], $Einheit = $dictArray[$varArray[$i]]["Einheit"], $var = $values[$i], $index = $i);
                 if ($i < $varNo - 1){echo ',';}
              }   
              echo ']'; //yAxis
              
              echo ',series:[';
              for ($i = 0; $i < $varNo; $i++) {
                echo lchartTmplSeries($txtDE = $dictArray[$varArray[$i]]["txtDE"], $var = $values[$i], $index = $i);
                 if ($i < $varNo - 1){echo ',';}
              }   
              echo ']'; // series
              echo '});';
            }
          ?>
        });
      });


      // Update the count down every 1 second
      var x = setInterval(function () {

      // Find the distance between now an the count down date
      var distance = new Date().getTime()
              - <?php 
                  $last = substr($values[0], strrpos($values[0], "["), 20);
                  echo substr($last, 1, strrpos($last, ",") - 1);
                ?>;
      // Time calculations for days, hours, minutes and seconds
      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      var vorText = 'vor: ';
      if (days > 0) {vorText = vorText + days + " T ";}
      if (hours > 0) {vorText = vorText + hours + " h ";}
      if (distance > 60000 && days == 0) {vorText = vorText + minutes + " min ";}
      if (days == 0) {vorText = vorText + seconds + "s <br/>";}

      <?php
        if ($_GET['box'] == true) {
          for ($i = 0; $i < $varNo; $i++) {
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
    <!-- **************
         *** header ***
        ************** -->
      <div id="header">
      <img src="./img/iSpindel.svg" height="40px"/>
      iSpindel: <?php echo $_GET['name']; ?>
    </div>

    <script src="include/highcharts.js"></script>

    <?php
    if ($star != 1) {
      /*************
       *** boxes ***
       *************/
      if ($_GET['box'] == 1) {
        echo '<div class="boxwrapper">';
        $value = array(
            0 => str_replace(']', '', substr($values[0], strrpos($values[0], ",") + 2, 10))
        );
        for ($i = 1; $i < $varNo; $i++) {
          $value = $value + array($i => str_replace(']', '', substr($values[$i], strrpos($values[$i], ",") + 2, 10)));
          ;
        }

        for ($i = 0; $i < $varNo; $i++) {
          echo
          '<div class="box">
           <div class="box_upper">'
          . $dictArray[$varArray[$i]]["txtDE"]
          . '<br/>'
          . '<div class="value">'
          . $value[$i]
          . $dictArray[$varArray[$i]]["Einheit"]
          . '</div>
           </div>
           <p id="since' . $i . '"></p>
         </div>';
        }
        echo '</div>
          </div>
          <div class="spacer"></div>';
      }

      /**************
       *** charts ***
       **************/
      if ($_GET['maxis'] == 1) {
        $ChartsCount = 1;
      }
      else {
        $ChartsCount = $varNo;
      }
      for ($i = 0; $i < $ChartsCount; $i++) {
        echo '<div class="wrapper">
                <div id="chart' . $i . '" ></div>
            </div>
            <div class="spacer"></div>';
      }
    } // $star==0
    
    /*************
     *** table ***
     *************/
    if ($_GET['tab'] == 1) {
      $tabArray = array();

      for ($i = 0; $i < $varNo; $i++) {
        $tabArray[$i] = explode('],[', substr(substr($values[$i], 1), 0, -1));
      }

      echo '<div class="wrappert">
              <table>    
                <thead>
                  <tr>
                    <th> Timestamp
                    </th>';
      for ($i = 0; $i < count($varArray); $i++) {
        echo '<th>' . $dictArray[$varArray[$i]]["txtDE"] . '</th>';
      }
      echo ' </tr>
           </thead>
         <tbody>';
      $date = date_create();
      $i = 0;
      for ($r = count($tabArray[1]) - 1; $r >= 0; $r--) {
        // Sonderlocke wg. Formatierung für Timestamp:
        echo '<tr>
                <td>' . date("d.m.Y     H:i:s", (int) substr($tabArray[0][$r], 0, strpos($tabArray[0][$r], ',') - 3)) . '</td>';

        for ($i = 0; $i < $varNo; $i++) {
          echo '<td>' . substr($tabArray[$i][$r], strpos($tabArray[$i][$r], ',') + 1) . ' '
          . $dictArray[$varArray[$i]]["Einheit"]
          . '&nbsp; '
          . '</td>';
        }
        echo '</tr>';
      }
      echo '    
          </tbody>
        </table>    
      </div>
      <div class="spacer"></div>';
    }
    ?>
    
    <!-- **************
         *** footer ***
         ************** -->
    <div id="footer">
      iSpindel: DIY elektronische Bierspindel <a href="https://github.com/universam1/iSpindel">https://github.com/universam1/iSpindel</a>
    </div>

  </body>
</html>