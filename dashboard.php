<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Dashboard</title>
<?php 

//Auto set page to Dublin in case load page straight
if (empty($_POST['county'])){
	$county='Dublin';
	}
else{
// Get county from previous page
$county= $_POST['county'];
}

// Get relevant file and encode in JavaScript
$file= file('data/'.$county.'.csv');
$i=0;
foreach ($file as $line) {
	$csv[]= str_getcsv($line);
}
// Skip first row as it contains column names
for($i=1; $i<sizeof($csv);$i++) {
	$row = $csv[$i];
// Skip first column as it is a date
	for($j=1; $j<sizeof($row); $j++) {
		$csv[$i][$j] = (float)$row[$j];
	}
}	
$string= json_encode($csv);
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

  // Function that returns columns were interested in	
  function selectColumns(dataset, desiredColumns) {
    var columnNames= dataset[0];
    var resultingArray= [desiredColumns];
    for(var i=1; i<dataset.length; i++) {
      var currentRow= dataset[i];
      var resultingRow= [];
      
      for(var j=0; j<currentRow.length; j++) {
        var currentColumnName= columnNames[j];
        if(desiredColumns.includes(currentColumnName)) {
          resultingRow.push(currentRow[j]);
        }
      }
      
      resultingArray.push(resultingRow);
    }
    
    return resultingArray;
  }

  // Get variables from php
  var dataString= '<?php echo $string; ?>';
  var fullDataset= JSON.parse(dataString);
  var county= "<?php echo $county; ?>";

  // Parse Date objects from the strings
  for(var i=1; i<fullDataset.length; i++) {
    fullDataset[i][0]= new Date(fullDataset[i][0]);
  }


  // Load the Visualization API and controls package
  google.charts.load('current', {'packages':['corechart', 'controls']});

  // Set a callback to run when the Google Visualization API is loaded
  google.charts.setOnLoadCallback(drawDashboard);

  //Function that draws dashboard
  function drawDashboard() {
    data= new google.visualization.arrayToDataTable(fullDataset);
    var dashboard= new google.visualization.Dashboard(document.getElementById('dashboard_div'));

    // Create a range slider to control date
    var dateRangeSlider= new google.visualization.ControlWrapper({
        'controlType': 'DateRangeFilter',
        'containerId': 'filter_div',
        'options': {
        'filterColumnLabel': 'date'
        }
    });

    // Create a different chart for each variable
    var tempChart= buildChart("temp", ["date", "temp"], "°C");
    var pressureChart= buildChart("pressure", ["date", "pressure"],"hpa");
    var windSpeedChart= buildChart("wind_speed", ["date", "wind_speed"],"knots");
    var humidityChart= buildChart("humidity", ["date", "humidity"], "%")
    var rainChart= buildChart("rain", ["date", "rain"],"mm")  

    //Connect all charts to date controller
    dashboard.bind(dateRangeSlider, tempChart);
    dashboard.bind(dateRangeSlider, pressureChart);
    dashboard.bind(dateRangeSlider, windSpeedChart);
    dashboard.bind(dateRangeSlider, humidityChart);
    dashboard.bind(dateRangeSlider, rainChart);

    // Draw the dashboard.
    dashboard.draw(data);

  }

  // Function to create charts
  function buildChart(divId, columns, yAxisLabel) {
    var options= {
      width: '100%',
      height: 400,
      hAxis: {
        title: 'Date'
      },
      vAxis: {
        title: yAxisLabel
      },
      legend: 'none',
    }

    //Function to make a line chart
    var lineChart= new google.visualization.ChartWrapper({
      'chartType': 'LineChart',
      'containerId': divId,
      'options': options,
      view: {
        columns: columns
      }
    });
    return lineChart;
  }

  </script>
<style type="text/css">
.auto-style1 {
	text-align: center;
}
.auto-style2 {
	font-family: Arial, Helvetica, sans-serif;
}
.auto-style3 {
	font-family: Arial, Helvetica, sans-serif;
	text-align: center;
}
.auto-style4 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: medium;
}
.auto-style5 {
	font-family: Arial, Helvetica, sans-serif;
	text-align: center;
	font-size: x-large;
}
</style>
</head>
  
  
  <body>
    
    <div class="auto-style1">
    
    <h1 class="auto-style3">Weather Data for <?php echo($county) ?></h1>
    <div class="auto-style1">    
    <a href="data/<?php echo $county?>.csv" download="<?php echo $county?>.csv"><input name="Home" type="button" value="Download CSV" class="auto-style2" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="data/<?php echo $county?>.rdf" download="<?php echo $county?>.rdf"><input name="Home" type="button" value="Download RDF" class="auto-style2" /></a>
    </div>
    <br></br>
   	<br></br>
	<br></br>
    <div id="filter_div" class="auto-style1"></div>
    <div id="dashboard" >
      <h3 class="auto-style5" >Temperature</h3>
      <div id="temp" ></div>
      <h3 class="auto-style5" >Rain</h3>
      <div id="rain" ></div>
      <h3 class="auto-style5" >Pressure</h3>
      <div id="pressure" ></div>
      <h3 class="auto-style5" >Wind Speed</h3>
      <div id="wind_speed" ></div>
      <h3 class="auto-style5" >Humidity</h3>
      <div id="humidity" ></div>
    </div>
    <br></br>
	<a href="index.html"><input name="Home" type="button" value="Home" class="auto-style4" /></a>    
  </div>
  </body>
</html>
