# Diagramme vom eigenen Server 

[English Version](README_en.md)

OK, der eigene Server ist im Einsatz und funktioniert, jetzt wollen wir auch die Daten darstellen können, notfalls auch ohne Internetverbindung.     

Man kann zwar jederzeit per Excel eine CSV Datei importieren oder auch über ODBC auf die Datenbank zugreifen, aber das ist ein wenig umständlich.
Also musste noch eine einfache Methode her, um direkt mit dem Browser Diagramme anzeigen zu können.

Wir haben Apache2 und MySQL auf dem Raspi (oder einem anderen Server) installiert, quasi Overkill, haben also jede Menge Möglichkeiten.      

Ich habe mich für eine Lösung auf Basis von Highcharts entschieden (www.highcharts.com), welches für den Privatgebrauch kostenlos ist und eine schier unglaubliche Menge an Darstellungen bietet.
Da habe ich bisher nur an der äußersten Schicht gekratzt.       
Weiterhin verwende ich jQuery (jquery.com), um eventuell noch eine richtig gute AJAX Implementierung hinzubekommen, sowie moment.js (momentjs.com) für die Zeitzonen Problematik.      
Die Sommerzeit Umstellung sollte also automatisch berücksichtigt werden, werden wir ja bald sehen ob das gut funktioniert so.

Ansonsten immer noch treu dem Motto, so einfach aber effektiv wie möglich... ;)      
Es steckt nach wie vor wahrlich kein Hexenwerk dahinter.

Folgende Diagramme stehen zur Verfügung:   
**lchart.php**: bis zu zwei Variablen in einer Liniengrafik, ähnlich zu bisherigen angle.php und plato.php   
**dashboard.php**:Liniengrafike je Variable (bis zu 7) , letzte Messwerte (optional), Tabelle (optional)  
**plato4.php**: Restextrakt **nach alter Methode kalibrierter iSpindel** (Firmware 4.x der iSpindel)   
**status.php**: aktueller Status der Batterie, Temperatur und Winkel als Messuhren   


Der Aufruf der Diagramme wird flexibel über die Parameter-Übergabe gesteuert:   
**name**: Name der iSpindel   
**varlist** (bei dashboard.php): Kommagetrennte Liste der anzuzeigenden Variablen, z.B. Temperature, Battery und Gravity (erst ab Version 5 der iSpindel), Angle  und ResetFlag   
**var1** und **var 2** (bei lchart,php): Erste und zweite anzuzeigende Variablen   
**box** (bei dashboard.php): [0 oder 1], Boxen mit dem zuletzt gemessenen Wert werden angezeigt  

Zur Definition der Zeitachse stehen folgende Paramerter zur Verfügung   
**hours**: Es werden die letzten x Stunden angezeigt   
**reset**: [0 oder 1]: Es werden die Werte seit dem letzten ResetFlag angezeigt   
**date** : (TT.MM.YYYY): Es werden die Werte zwischen 2 ResetFlags angezeigt.    
So kann man, wenn man am Anfang und am Ende der Gärung das reset_now.php aufgerufen hat, auch vergangene Sude anzeigen, wenn man ein Datum, das zwischen dem Anfang und dem Ende der Gärung liegt, angibt. Das ResetFlag kann man auch im Nachhinein über http://meinraspi/phpmyadmin editieren.  


Um das entsprechende Diagramm aufzurufen werden die Parameter per GET Methode übergeben.
Beispiele:

* http://meinraspi/iSpindle/dashboard.php?name=mybier&varlist=Angle,Temperature,Battery&box=1&reset=1
* http://meinraspi/iSpindle/dashboard.php?varlist=Angle,Temperature,Battery
* http://meinraspi/iSpindle/dashboard.php?name=mybier&varlist=Gravity,Temperature&box=0&date=5.5.2017
* http://meinraspi/iSpindle/lchart.php?var1=Angle&var2=Temperature&date=5.5.2017
* http://meinraspi/iSpindle/status.php?hours=24


In der Datei index_MUSTER.html sind weitere Beispielaufrufe aufgeführt. 
Man kann in dieser Datei seine individuellen Aufrufe unten eintragen und die Datei unter index.html bei sich abspeichern. Dann genügt als Aufruf http://meinraspi/iSpindle/ , um zu der individuellen Einstiegsseite zu gelangen.


Mit **reset_now** kann man einen Zeitstempel (Beginn oder Ende der Gärung) festlegen und bei der Grafik alle Werte nach diesem Zeitstempel anzeigen, oder eine vergangene Gäung mit Angabe des Parameters date:
* http://meinraspi/iSpindle/reset_now.php?name=MeineSpindel2

Ich hoffe, damit einen sinnvollen Grundstein gelegt zu haben, auf dem Ihr aufbauen könnt.
Für mich persönlich genügt das jetzt erst mal so wie es ist, aber es gibt natürlich eine Menge Verbesserungspotenzial.
Gerne beantworte ich auch Eure Fragen, sollte irgendwas nicht ausreichend kommentiert oder sonstwie unklar sein.

Auf Eure Weiterentwicklungen (die ja normalerweise aus eigenen Bedürfnissen entstehen) freue ich mich sehr.
Um Sam da zu entlasten, würde ich Euch aber bitten, dafür mein [Repository](https://github.com/DottoreTozzi/iSpindel) zu klonen und Eure Pull Requests zuerst an mich zu schicken.
Ich teste das dann und gebe die Änderungen an Sam weiter.

Die externen Libraries habe ich mit hier aufgenommen, um die Daten auch ohne Internet darstellen zu können.

### Noch ein paar Hinweise zur Installation:
#### Apache2:
Damit Apache die Diagramme "sehen" kann, müssen sie irgendwie in **/var/www/html** (oder einem Unterverzeichnis dort) zu finden sein.
Ich mache das mittels Symlink auf mein GIT Arbeitsverzeichnis, somit ist der Webserver nach GIT PULL sofort auf dem neuesten Stand:

      cd /var/www/html    
      sudo ln -s ~/iSpindel/tools/genericTCP/web/ iSpindle

#### Datenbankschnittstelle:
Um die Verbindung zur Datenbank herzustellen, muss die Datei include/common_db.php editiert werden:

      // configure your database connection here:
      define('DB_SERVER',"localhost");
      define('DB_NAME',"iSpindle");
      define('DB_USER',"iSpindle");
      define('DB_PASSWORD',"xxxx");

#### Kalibrierung nach "alter" Methode (Firmware < 5.0.1) 
Um plato4.php nutzen zu können, und den umgerechneten Restextrakt auszugeben, muss für jede iSpindel ein Datensatz hinterlegt werden.    
Die Verknüpfung erfolgt nicht über den Namen der Spindel, sondern über ihre Hardware ID, bleibt also erhalten wenn man die iSpindel umbenennt.    

Dazu brauchen wir erst eine neue Tabelle in der Datenbank.    
Ein Skript für beide Tabellen findet sich [hier](../MySQL_CreateTables.sql).    

Das Schema der neuen Tabelle:
     
     CREATE TABLE `Calibration` (
     `ID` varchar(64) COLLATE ascii_bin NOT NULL,
     `const1` double NOT NULL,
     `const2` double NOT NULL,
     `const3` double NOT NULL,
     PRIMARY KEY (`ID`)
     ) 
     ENGINE=InnoDB DEFAULT CHARSET=ascii 
     COLLATE=ascii_bin COMMENT='iSpindle Calibration Data';


ID ist die Hardware ID der Spindel. Diese wird mit übermittelt und gespeichert.     
const1, 2 und 3 sind die drei Koeffizienten des ermittelten Polynoms:

const1 * winkel<sup>2</sup> - const2 * winkel + const3

Am besten die Werte mit phpMyAdmin eintragen, oder:

    INSERT INTO Calibration (ID, const1, const2, const3)
    VALUES ('123456', 0.013355798, 0.776391729, 11.34675255);

Die Spindel Hardware ID kann aus der Daten Tabelle ermittelt werden, sie wird von der Spindel mitgeschickt.    

Viel Spaß,     
Tozzi       
<stephan@sschreiber.de>
