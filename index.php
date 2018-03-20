<?php 
  // Set up the weather data for javascript

  //Auto set page to Dublin in case this page is loaded first
  if (empty($_POST['county'])){
    $county='Dublin';
  }
  else{
    // Get county from previous page
    $county= $_POST['county'];
  }

  // Get relevant file and encode in JavaScript
  $file= file('data/csv/'.$county.'.csv');
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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, user-scalable=false;">
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  </head>
  
  <body style="background-color: #EAEAEA">
    <nav class="navbar navbar-default" style="background-color: #0277bd">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" style="color: white" href="#"><?php echo $county ?></a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-right">
            <li><a style="color: white" href="data/rdf/<?php echo $county?>.rdf" download="<?php echo $county?>.rdf">Download RDF</a></li>
            <li><a style="color: white" href="data/csv/<?php echo $county?>.csv" download="<?php echo $county?>.csv">Download CSV</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container-fluid">
      <div id="dashboard">
        <div class="row">
         <div class="col-lg-4">
          <div style="margin: 2%; padding: 2%; background-color: white">
            <form class="form-inline" action="index.php" method="POST">
              <div class="form-group">
                <select class="form-control" name="county">
                  <option value="Cavan">Cavan</option>
                  <option value="Cork">Cork</option>
                  <option value="Donegal">Donegal</option>
                  <option value="Dublin">Dublin</option>
                  <option value="Galway">Galway</option>
                  <option value="Roscommon">Roscommon</option>
                </select>
                <button type="submit" class="btn btn-default">Change County</button>
              </div>
            </form>
            <hr>
            <div class="form">
              <div class="form-group">
                <label for="low_date">Low Date</label>
                <input type="date" class="form-control" id="low_date">
              </div>
              <div class="form-group">
                <label for="high_date">High Date</label>
                <input type="date" class="form-control" id="high_date">
              </div>
              <button onclick="setDate()" class="btn btn-default">Filter</button>
            </div>
           
            <!-- Not showing daste slider, but need it to be able to control dashboard -->
            <div style="display: none" id="filter_div"></div> 
            </div>
          </div>
          <div class="col-lg-4">
            <div style="margin: 2%; background-color: white">
              <div id="temp"></div>
              <p class="text-center" id="avg_temp"></p>
            </div>
          </div>
            <div class="col-lg-4">
                <div style="margin: 2%; background-color: white">
                <div id="pressure"></div>
                <p class="text-center" id="avg_pressure"></p>
              </div>
          </div>
        </div>

        <br>

        <div class="row">
          <div class="col-lg-4">
            <div style="margin: 2%; background-color: white">
              <div id="wind_speed"></div>
              <p class="text-center" id="avg_wind_speed"></p>
            </div>
          </div>
            <div class="col-lg-4">
              <div style="margin: 2%; background-color: white">
                <div id="rain"></div>
                <p class="text-center" id="avg_rain"></p>
              </div>
          </div>
            <div class="col-lg-4">
              <div style="margin: 2%; background-color: white"> 
                <div id="humidity"></div>
                <p class="text-center" id="avg_humidity"></p>
              </div>
          </div>
        </div>
      </div>
    </div>
  </body>


  <script type="text/javascript">
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
        data = new google.visualization.arrayToDataTable(fullDataset);
        var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));

        // Create a range slider to filter date (note this is hidden in favour of date form)
        var dateRangeSlider = new google.visualization.ControlWrapper({
            'controlType': 'DateRangeFilter',
            'containerId': 'filter_div',
            'options': {
              'filterColumnLabel': 'date',
              'ui': {
                'label': '',
                'labelStacking': 'vertical',
                'format': new google.visualization.DateFormat({formatType: 'long'})
              }
            }
          });

        // Create a different chart for each variable
        var tempChart= createChart("temp", ["date", "temp"], "°C", "Temperature");
        var pressureChart= createChart("pressure", ["date", "pressure"],"hpa", "Pressure");
        var windSpeedChart= createChart("wind_speed", ["date", "wind_speed"],"knots", "Wind Speed");
        var humidityChart= createChart("humidity", ["date", "humidity"], "%", "Humidity")
        var rainChart= createChart("rain", ["date", "rain"],"mm", "Rainfall")  

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
      function createChart(divId, columns, yAxisLabel, title) {
        var options= {
          width: '100%',
          height: '250',
          title: title,
           titleTextStyle: {
              color: "#0277bd",
              align: 'center',
              fontSize: 16,
          },
          hAxis: {
            title: 'Date',
            textStyle: {fontSize: 10},
            
          },
          vAxis: {
            title: yAxisLabel
          },
          legend: 'none',
          backgroundColor: "transparent",
        }

        // Create the line chart
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

      // Function to calculate the average of each of the columns between two dates
      function showAverages(lowDate, highDate) {
        var temp = 0;
        var rain = 0;
        var pressure = 0;
        var windSpeed = 0;
        var humidity = 0;

        // Use Date objects for easier comparison
        var maxDate = new Date(highDate);
        var minDate = new Date(lowDate);

        // Start at 1 as first row is column names
        var i = 1;
        var numDatesCounted = 0;
        while (i<fullDataset.length) {
          var currentDate = fullDataset[i][0]; 

          // Stop once reached max date
          if (currentDate > maxDate) {
            break;
          }

          // Only count those dates larger than the min date
          if (currentDate >= lowDate) {
            temp += fullDataset[i][1];
            rain += fullDataset[i][2];
            pressure += fullDataset[i][3];
            windSpeed += fullDataset[i][4];
            humidity += fullDataset[i][5];
            numDatesCounted++;
          }
          i++;
        }

        // Update the <p> tags which hold the averages
        document.getElementById("avg_temp").innerHTML = "Average Temp: " + (temp / numDatesCounted).toFixed(2) + " °c";
        document.getElementById("avg_rain").innerHTML = "Average Rain: " + (rain / numDatesCounted).toFixed(2) + " mm";
        document.getElementById("avg_wind_speed").innerHTML = "Average Wind Speed: " + (windSpeed / numDatesCounted).toFixed(2) + " knotts";
        document.getElementById("avg_pressure").innerHTML = "Average Pressure: " +  (pressure / numDatesCounted).toFixed(2) + " hPa";
        document.getElementById("avg_humidity").innerHTML = "Average Humidity: " + (humidity / numDatesCounted).toFixed(2) + " %";
      }

      // Sets the date filter range for the charts
      // This actually sets the slider's (which is hidden) boundaries, which in turn filters the graphs
      function setDate() {
        // Get the dates from the date fields of the form
        var lowDate = new Date(document.getElementById("low_date").value);
        var highDate = new Date(document.getElementById("high_date").value);

        // Update the slider and redraw the graphs
        dateRangeSlider.setState({lowValue: lowDate, highValue: highDate})
        dateRangeSlider.draw();

        // Recalculate averages with new date range
        showAverages(lowDate, highDate);
      }

      // Initially show averages for entire dataset.
      showAverages(fullDataset[1][0], fullDataset[fullDataset.length - 1][0]);
    </script>
</html>