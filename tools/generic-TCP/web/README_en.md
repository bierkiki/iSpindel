# Charts From Own Server 

While exporting CSVs or directly accessing the database via ODBC from, for example, Excel, is fine for data analysis, we'll definitely also want a quick way to take a glance at the current fermentation.
So, here are a few essential charts, developed using [highcharts](http://www.highcharts.com), browser accessible.
Especially nice in Firefox fullscreen mode on a Raspi touch display or via smartphone. Just put some bookmarks on the Raspi Desktop.

We'll need a working [install](../INSTALL_en.md) of the backend, including mySQL and Apache2.

My goal was to implement a solution as simple yet effective as possible.

Following charts are available:  
**lchart.php**: One or two variables in a linechart, similar to previous  angle.php and plato.php   
**dashboard.php**: Line charts for up to 7 variables, last measurement (optional), data table (optional)  
**plato4.php**: - deprecated as per firmware 5.x - gravity and temperature over the past x hours (calibration record required as explained below)  
**status.php**: - battery, tilt and temperature of the specified iSpindle


Following parameters are available:  
**name**: Name of iSpindle     
**varlist** (for dashboard.php): comma separated list of variables to be displayed, e.g. Temperature, Battery and Gravity (need firmware 5.x), Angle and ResetFlag   
**var1** and **var 2** (for lchart,php): First and second variable to be displayed  
**box** (for dashboard.php): [0 or 1], boxes with last measurement are displayed
**maxis** (for dashboard.php): [0 or 1], 0: one chart for each variable; 1: one chart with multiple y-axis

For definition of selected time line:  
**hours**: Last x hours will be displayed  
**reset**: [0 or 1]: Timeline starts at timestamp of last ResetFlag  
**date** : (TT.MM.YYYY): Timeline is between two ResetFlags around the given date.  
If you call reset_now.php at start and end of fermentation, with date you can show past fermentations, which were around the given date. If you forgot the call of reset_now.php you can edit the Resetflags via http://meinraspi/phpmyadmin.    


In order to show these charts we pass arguments via GET in order to be able to bookmark the URLs:

* http://raspi/iSpindle/dashboard.php?name=mybier&varlist=Angle,Temperature,Battery&box=1&reset=1
* http://raspi/iSpindle/dashboard.php?varlist=Angle,Temperature,Battery
* http://raspi/iSpindle/dashboard.php?name=mybier&varlist=Gravity,Temperature&box=0&date=5.5.2017
* http://raspi/iSpindle/dashboard.php?name=mybier&varlist=Gravity,Temperature&box=0&date=5.5.2017&maxis=0
* http://raspi/iSpindle/lchart.php?var1=Angle&var2=Temperature&date=5.5.2017
* http://raspi/iSpindle/status.php?hours=24


The file index_MUSTER.html contains more examples. 
At the bottom of index_MUSTER.html you can edit your individual calls. If you save your edited file as index.html it's sufficient to request http://raspi/iSpindle/ to get your start page.

reset_now defines a timestamp (start of fermentation) and the graph shows only the entries after this timestamp:
* http://meinraspi/iSpindle/reset_now.php?name=MeineSpindel2
* http://meinraspi/iSpindle/angle.php?name=MeineSpindel2&reset=true

#### Defaults for parameter of the scrips for charts:
You can define defaults for some parameters for the charts in the file include/config_frontend.php:

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



I hope I've built sort of a foundation with templates for lots of future enhancements.
I am aware that there's probably a ton of things I could have solved more elegantly and there's room for improvement galore.     
Contributions are by all means welcome. Looking forward!


### A Few Hints Regarding Installation:
#### Apache2:
In order for apache to "see" the charts, they'll have to be somewhere in **/var/www/html**.
(This might vary in distributions other than Raspbian).
I achieve that by simply creating a symlink there, pointing towards my work directory.

      cd /var/www/html    
      sudo ln -s ~/iSpindel/tools/genericTCP/web/ iSpindle

#### Database Interface:
You'll need to configure the database connection, found in include/common_db.php, so edit this file section:

      // configure your database connection here:
      define('DB_SERVER',"localhost");
      define('DB_NAME',"iSpindle");
      define('DB_USER',"iSpindle");
      define('DB_PASSWORD',"password");

#### Calibration (Angle:Gravity)
Note: This is deprecated as per firmware 5.0.1.      
The iSpindle now has its own algorithm for density/gravity output.      
The following applies if you are still using an older firmware version.      

Before you can use plato4.php to display the calculated gravity (%w/w) in Plato degrees, you'll need enter the [calibration results](../../../docs/Calibration_en.md) and add them to the database.      
The reference being used is the spindle's unique hardware id, stored as "ID" in the 'Data' table.    
First, if you haven't done that before, you'll need to create a second table now:
     
     CREATE TABLE `Calibration` (
     `ID` varchar(64) COLLATE ascii_bin NOT NULL,
     `const1` double NOT NULL,
     `const2` double NOT NULL,
     `const3` double NOT NULL,
     PRIMARY KEY (`ID`)
     ) 
     ENGINE=InnoDB DEFAULT CHARSET=ascii 
     COLLATE=ascii_bin COMMENT='iSpindle Calibration Data';

ID is the iSpindel's unique hardware ID as shown in the 'Data' table.
const1, 2, 3 are the three coefficients of the polynom you have got as a result of calibrating your iSpindel:

gravity = const1 * tilt<sup>2</sup> - const2 * tilt + const3

You could enter these using phpMyAdmin, or on a mysql prompt, you'd do:

    INSERT INTO Calibration (ID, const1, const2, const3)
    VALUES ('123456', 0.013355798, 0.776391729, 11.34675255);

Have Fun,     
Tozzi (stephan@sschreiber.de)
and
kiki