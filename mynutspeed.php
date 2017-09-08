<html>
<?php
	date_default_timezone_set("Asia/Bangkok");
	$servername = "127.0.0.1";
	$username = "root";
	$password = "chang%";
	$dbname = "mynut";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$result = $conn->query("select `local_host` from speedtest group by `local_host`");
	$ip_count = $result->num_rows;
	// echo "There are " . $ip_count . " rows in my table.<br>";

	$ip_address = array();

	$i = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$ip_address[$i] = $row["local_host"];
			$i++;
		}
		// echo json_encode($ip_address) . ",\n";
	}

	$sql = "SELECT * FROM speedtest order by `epoch`";
	$result = $conn->query($sql);
	$result_array = Array();
	$Rows_Array =  Array(Array());
	$firsttime = "";
	$time = 0;
	if ($result->num_rows > 0) {
		// output data of each row
		$index_rows = 0;	// index rows for Rows_Array
		while($row = $result->fetch_assoc()) {
			//echo "From: " . $row["start.connected.local_host"]. " - Time: " . $row["start.timestamp.time"]. " bits per second: " . $row["end.sum_received.bits_per_second"]. "<br>";
			
			//$result_array[] = $row["start.timestamp.time"] . ", " . floatval($row["end.sum_received.bits_per_second"]);

			$offset = get_timezone_offset('UTC',  'Asia/Bangkok') / 60 / 60;
			$epoch = $row["epoch"];
            $dt = new DateTime("@$epoch");
	    	$Y = $dt->format('Y');
	    	// Note: JavaScript counts months starting at zero: January is 0, February is 1, and December is 11. 
	    	// If your calendar chart seems off by a month, this is why.
	    	$m = $dt->format('m') - 1;
	    	$d = $dt->format('d');
	    	$H = $dt->format('H') + $offset;
	    	$i = $dt->format('i');
			$s = $dt->format('0'); // fill second = 00
			$starttime = "Date(" . $Y . "," . $m . "," . $d . "," . $H . "," . $i . "," . $s . ")";
			// $starttime = "Date(" . $Y . "," . $m . "," . $d . "," . $H . "," . $i . "," . "00" . ")";
			$check_date = $Y.$m.$d.$H.$i;
			

			// $epoch - $time < 240 
			if (abs(($epoch - $time)) < 240) {
			//if (strcmp($firsttime, $check_date) == 0) {
				$time = $epoch;
				$firsttime = $check_date;
				if (strcmp($row["dl"],"Download") == 0 ) {
					//$dl_speed = $row["dl"];
					$Rows_Array[$index_rows][1] =  floatval($row["mbps"]);
				}
				else if (strcmp($row["dl"],"Upload") == 0 ) {
					//$ul_speed = $row["dl"];
					$Rows_Array[$index_rows][2] =  floatval($row["mbps"]);
				}
				
			}
			else {
				// time == 0 or firsttime == "" do nothing
				
				if ($time >  0) {
					$index_rows++;
				}
				
				$time = $epoch;
				$firsttime = $check_date;
				
				if (strcmp($row["dl"],"Download") == 0 ) {

					$Rows_Array[$index_rows][1] =  floatval($row["mbps"]);
				}
				else if (strcmp($row["dl"],"Upload") == 0 ) {

					$Rows_Array[$index_rows][2] =  floatval($row["mbps"]);
				}

			}
			$Rows_Array[$index_rows][0] = $starttime;
            
	


			$result_array[] = array("Date('" . $starttime . "')", floatval($row["mbps"])) ;


		}
	} else {
		echo "0 results";
	}
	// Debug
	// echo "<br><br>";
	// var_dump($result_array);
	// echo "<br><br>";
	// echo json_encode($result_array);
	// echo "<br><br>";
	// echo json_encode($Rows_Array);
	// echo "\n";

	
	$conn->close();

/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/
function get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

?>
  
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      // Load the Visualization API and the controls package.
      google.charts.load('current', {'packages':['corechart', 'controls']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the line chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.arrayToDataTable([
			[{type: 'datetime', label: 'Date Time'}, {type: 'number', label: 'Download'}, {type: 'number', label: 'Upload'}],

			<?php
				foreach($Rows_Array as list($v1, $v2, $v3)) {
					$d = $v1;
					$i = $v2;
					$o = $v3;
					echo "[\"" . $d . "\"" . "," . $i . "," . $o . "],";
					echo "\n";
				}
			?>
		[new Date(),null,null]]

		);

	

        // Set chart options
        var options = {'title':'How Much my Speed : bps',
                       'width':1200,
                       'height':500,
					   vAxis: {
						viewWindowMode:'explicit',
							viewWindow: {
								max:2,
								min:0
							}
						}
					   
					}

		// Create a dashboard.
        var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));
		
	// Create a range slider, passing some options
        var dateRangeSlider = new google.visualization.ControlWrapper({
          'controlType': 'ChartRangeFilter',
          'containerId': 'filter_div',
          'options': {
            'filterColumnLabel': 'Date Time',
	     'ui': {
          	'chartType': 'LineChart',
          	'chartOptions': {
            		'chartArea': {'width': '90%'},
            		'hAxis': {'baselineColor': 'none'}
          	}
		}
          }
        });
		
        // Instantiate and draw our chart, passing in some options.
        // var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

	var programmaticChart  = new google.visualization.ChartWrapper({
          'chartType': 'LineChart',
          'containerId': 'chart_div',
          'options': {
		'title':'How Much my Speed : bps',
                //'width':1200,
                //'height':500,
		'chartArea': {'height': '95%', 'width': '67%'},
                vAxis: {
                	viewWindowMode:'explicit',
                        viewWindow: {
                        	max:15,
                                min:0
                        }
                }        
          }

        });

		
	// Establish dependencies, declaring that 'filter' drives 'chart',
        // so that the line chart will only display entries that are let through
        // given the chosen slider range.
        dashboard.bind(dateRangeSlider, programmaticChart);
        dashboard.draw(data);
      }
    </script>
  </head>


  <body>
	
	<br>
	<!--Div that will hold the dashboard-->
    	<div id="dashboard_div">
      	<!--Divs that will hold each control and chart-->
	<center>
      	<div id="filter_div" style="width: 1000px; height: 60px;"></div>
	</center>
	<br><br>
	<div id="chart_div"></div>
    	</div>
	
	<p>
	<center>
	<!--
	<img src="img/pi3.jpg" width=30% height=30%></img>
	-->
	<br><br>
	Power by <a href="http://www.mybycat.com/th">my by CAT</a>, <a href="https://www.raspberrypi.org/products/raspberry-pi-3-model-b/">Raspberry Pi</a>. for more information please feel free to contact pornthep.n @ cattelecom dot com 
	</center>
  </body>
</html>
