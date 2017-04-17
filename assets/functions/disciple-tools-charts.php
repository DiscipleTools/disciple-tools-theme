<?php
/**
 * Disciple Tools Charts
 *
 * TODO: Move to Plugin. Core chart creation should live in plugin and be called from theme. Then these charts can be used in the Admin area as well.
 */


/**
 * Sample Wordtree Chart
 */
function dt_chart_wordtree () {
    echo '<div id="wordtree_basic" style="width: 900px; height: 500px;"></div>';
    add_action('wp_footer', 'dt_chart_wordtree_relationships');
}

function dt_chart_wordtree_relationships () {
    echo "
    <script type=\"text/javascript\">
        google.charts.load('current', {packages:['wordtree', 'corechart']});
        google.charts.setOnLoadCallback(drawChart);
    
        function drawChart() {
            var data = google.visualization.arrayToDataTable(
                [ ['Phrases'],
                    ['Tarik Mohammed'],
                    ['Tarik Mohammed Riki '],
                    ['Tarik Mohammed John '],
                    ['Tarik Mohammed John Jenny'],
                    ['Tarik Mohammed John Asheal'],
                    ['Tarik Mohammed John Lia'],
                    ['Tarik Mohammed John Colin'],
                    ['Tarik Abel'],
                    ['Tarik Nonri'],
                    ['Tarik Achmet'],
                    ['Tarik Achmet Zia'],
                    ['Tarik Achmet Kimmel'],
                    ['Tarik Achmet Sash'],
                ]
            );
    
            var options = {
                wordtree: {
                    format: 'implicit',
                    word: 'Tarik'
                }
            };
    
            var chart = new google.visualization.WordTree(document.getElementById('wordtree_basic'));
            chart.draw(data, options);
        }
    </script>
    ";
}

/**
 * Sample Bargraph Chart
 */
function dt_chart_bargraph () {
    echo '<div id="chart_div" style="width: 900px; height: 500px;" ></div>';
    echo '<div id="chart_bar_div" style="width: 900px; height: 500px;" ></div>';
    echo '<div id="chart_pie_div" style="width: 900px; height: 500px;" ></div>';
    echo '<div id="chart_dounut_div" style="width: 900px; height: 500px;" ></div>';
    echo '<div id="chart_line_div" style="width: 900px; height: 500px;" ></div>';
    echo '<div id="chart_combo_div" style="width: 900px; height: 500px;" ></div>';
    add_action('wp_footer', 'dt_chart_bargraph_script');
}

function dt_chart_bargraph_script () {
    //https://developers.google.com/chart/interactive/docs/gallery
    echo "
    <script type=\"text/javascript\">
      //google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart1);

      function drawChart1() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2013',  1000,      400],
          ['2014',  1170,      460],
          ['2015',  660,       1120],
          ['2016',  1030,      540]
        ]);

        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
        
        var chart = new google.visualization.BarChart(document.getElementById('chart_bar_div'));
        chart.draw(data, options);
        
        var chart = new google.visualization.PieChart(document.getElementById('chart_pie_div'));
        chart.draw(data, options);
        
        var chart = new google.visualization.LineChart(document.getElementById('chart_line_div'));
        chart.draw(data, options);
        
        var donutOption = {
            title: 'My Daily Activities',
            pieHole: 0.4,
        }
        
        var chart = new google.visualization.PieChart(document.getElementById('chart_dounut_div'));
        chart.draw(data, donutOption);
        
      }
      
      //https://developers.google.com/chart/interactive/docs/gallery/combochart
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
         ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
         ['2004/05',  165,      938,         522,             998,           450,      614.6],
         ['2005/06',  135,      1120,        599,             1268,          288,      682],
         ['2006/07',  157,      1167,        587,             807,           397,      623],
         ['2007/08',  139,      1110,        615,             968,           215,      609.4],
         ['2008/09',  136,      691,         629,             1026,          366,      569.6]
      ]);
    
        var options = {
          title : 'Monthly Coffee Production by Country',
          vAxis: {title: 'Cups'},
          hAxis: {title: 'Month'},
          seriesType: 'bars',
          series: {5: {type: 'line'}}
        };
    
        var chart = new google.visualization.ComboChart(document.getElementById('chart_combo_div'));
        chart.draw(data, options);
      }
    </script>
    ";
}