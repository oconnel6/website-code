<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Dashboard</title>
<?php 

$county = $_POST['county'];
$file= file('data/'.$county.'.csv');

$i=0;
foreach ($file as $line) {
	$csv[] = str_getcsv($line);
}

// Skip first row as it contains column names
for($i=1; $i<sizeof($csv);$i++) {
	$row = $csv[$i];
	
	// Skip first column as it is a date
	for($j=1; $j<sizeof($row); $j++) {
		$csv[$i][$j] = (float)$row[$j];
	}
}	

$string = json_encode($csv);
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

  // Function that returns columns were interested in	
  function selectColumns(dataset, desiredColumns) {
    var columnNames = dataset[0];
    var resultingArray = [desiredColumns];
    for(var i=1; i<dataset.length; i++) {
      var currentRow = dataset[i];
      var resultingRow = [];
      
      for(var j=0; j<currentRow.length; j++) {
        var currentColumnName = columnNames[j];
        if(desiredColumns.includes(currentColumnName)) {
          resultingRow.push(currentRow[j]);
        }
      }
      
      resultingArray.push(resultingRow);
    }
    
    return resultingArray;
  }

  // Get variables from php
  var dataString = '<?php echo $string; ?>';
  var fullDataset = JSON.parse(dataString);
  var county = "<?php echo $county; ?>";

  // Parse Date objects from the strings
  for(var i=1; i<fullDataset.length; i++) {
    // var currentRow = fullDataset[i];
    // var dateString = currentRow[0];
    // var dateParts = dateString.split("-");
    // var dateObject = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
    // currentRow[0] = dateObject;
    fullDataset[i][0] = new Date(fullDataset[i][0]);
  }


  // Load the Visualization API and the controls package.
  google.charts.load('current', {'packages':['corechart', 'controls']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.charts.setOnLoadCallback(drawDashboard);

  function drawDashboard() {
    data = new google.visualization.arrayToDataTable(fullDataset);
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));

    // Create a range slider, passing some options
    var dateRangeSlider = new google.visualization.ControlWrapper({
        'controlType': 'DateRangeFilter',
        'containerId': 'filter_div',
        'options': {
        'filterColumnLabel': 'date'
        }
    });

    var tempChart = buildChart("temp", ["date", "temp"], "°C");
    var pressureChart = buildChart("pressure", ["date", "pressure"]);
    var windSpeedChart = buildChart("wind_speed", ["date", "wind_speed"]);
    var humidityChart = buildChart("humidity", ["date", "humidity"], "%")
    var rainChart = buildChart("rain", ["date", "rain"])  

    dashboard.bind(dateRangeSlider, tempChart);
    dashboard.bind(dateRangeSlider, pressureChart);
    dashboard.bind(dateRangeSlider, windSpeedChart);
    dashboard.bind(dateRangeSlider, humidityChart);
    dashboard.bind(dateRangeSlider, rainChart);

    // Draw the dashboard.
    dashboard.draw(data);

  }

  function buildChart(divId, columns, yAxisLabel) {
    var options = {
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

    var lineChart = new google.visualization.ChartWrapper({
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
</head>
  
  
  <body>
    
    <h1>Weather data for <?php echo($county) ?></h1>
    <div id="filter_div"></div>

    <div id="dashboard">
      <h3>Temperature</h3>
      <div id="temp"></div>
      <h3>Rain</h3>
      <div id="rain"></div>
      <h3>Pressure</h3>
      <div id="pressure"></div>
      <h3>Wind Speed</h3>
      <div id="wind_speed"></div>
      <h3>Humidity</h3>
      <div id="humidity"></div>
    </div>
  </body>
</html>
​