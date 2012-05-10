(function ($) {

  var mdcharts = {};

  /**
   * Given a data array with a "date" key that contains an integer UTC timestamp, fill in the blanks and convert values as needed
   * @param Array data an array of objects to chart
   * @param integer period the number of seconds that should appear between items (for filling in blanks)
   * @return AmCharts.DataSet the AmCharts dataset for the filled-in data
   */
  mdcharts.fillData = function (data, period) {
    if (!data.length) return null;
    period = period || 3600;

    var filled = [];
    var data_keys = [];
    for (var k in data[0]) {
      if (k != 'date') data_keys.push(k);
    }

    $.each(data, function (i, item) {
      var item_time = item.date;
      item.date = new Date(item.date * 1000);

      //If there are any gaps in the timeline, fill in zero data
      while (filled.length && filled[filled.length - 1].date.getTime() / 1000 < item_time - period) {
        var empty_item = {date:new Date(filled[filled.length - 1].date.getTime() + period * 1000)};
        $.each(data_keys, function (i, key) {
          empty_item[key] = 0;
        });

        filled.push(empty_item);
      }

      filled.push(item);
    });

    var ds = new AmCharts.DataSet();
    ds.dataProvider = filled;
    ds.fieldMappings = [];

    $.each(data_keys, function (i, key) {
      ds.fieldMappings.push({fromField:key, toField:key});
    });

    ds.categoryField = 'date';

    return ds;
  };

  /**
   * Return a new Stock Chart, with defaults that match what we generally want with stock charts in Mandrill
   * @param AmCharts.DataSet ds the dataset to use to build the chart
   * @param string scrollbar_field the name of the field to use for the scrollbar (usually should be the biggest value)
   * @return AmCharts.AmStockChart the stock chart
   */
  mdcharts.getStockChart = function (ds, scrollbar_field) {
    var chart = new AmCharts.AmStockChart();
    chart.pathToImages = '/js/amcharts/images/';

    var catAxes = new AmCharts.CategoryAxesSettings();
    catAxes.minPeriod = 'hh';
    catAxes.groupToPeriods = ['hh', 'DD', 'WW', 'MM'];
    catAxes.dateFormats = [
      {period:"YYYY", format:"YYYY"},
      {period:"MM", format:"MMM"},
      {period:"WW", format:"MMM D, YYYY"},
      {period:"DD", format:"MMM DD, YYYY"},
      {period:"hh", format:"L:NN A"},
      {period:"mm", format:"L:NN A"},
      {period:"ss", format:"L:NN:SS A"},
      {period:"fff", format:"L:NN:SS A"}
    ];
    catAxes.gridColor = '#111111';
    chart.categoryAxesSettings = catAxes;

    var valAxes = new AmCharts.ValueAxesSettings();
    valAxes.inside = true;
    valAxes.gridColor = '#444444';
    valAxes.dashLength = 3;
    chart.valueAxesSettings = valAxes;

    var panelsSettings = new AmCharts.PanelsSettings();
    panelsSettings.marginLeft = 1;
    panelsSettings.marginRight = 1;
    panelsSettings.marginTop = 5;
    chart.panelsSettings = panelsSettings;

    var cursorSettings = new AmCharts.ChartCursorSettings();
    cursorSettings.cursorColor = "#148db6";
    cursorSettings.categoryBalloonDateFormats = [
      {period:"YYYY", format:"YYYY"},
      {period:"MM", format:"MMM"},
      {period:"WW", format:"MMM D, YYYY"},
      {period:"DD", format:"MMM DD, YYYY"},
      {period:"hh", format:"L:NN A"},
      {period:"mm", format:"L:NN A"},
      {period:"ss", format:"L:NN:SS A"},
      {period:"fff", format:"L:NN:SS A"}
    ];
    chart.chartCursorSettings = cursorSettings;

    chart.dataSets = [ds];

    var panel = new AmCharts.StockPanel();
    panel.fontSize = 13;
    panel.fontFamily = "'Helvetica Neue',Helvetica,Arial,sans-serif";
    panel.colors = ["#a3a3a3", "#f09d3e", "#da1e3f", "#faa537", "#31b9ea"];

    var stockLegend = new AmCharts.StockLegend();
    stockLegend.align = "right";
    stockLegend.equalWidths = false;
    stockLegend.markerBorderThickness = 2;
    stockLegend.markerSize = 20;
    stockLegend.markerType = "line";
    stockLegend.rollOverGraphAlpha = .7;
    //stockLegend.valueWidth = "1px";
    //stockLegend.valueTextRegular = " ";
    //stockLegend.valueTextComparing = " ";
    panel.stockLegend = stockLegend;

    var scrollbar = new AmCharts.ChartScrollbarSettings();
    scrollbar.backgroundColor = "#ffffff";
    scrollbar.selectedBackgroundColor = "#eeeeee";
    scrollbar.color = "#333333";
    scrollbar.fontSize = 0;
    scrollbar.enabled = true;

    $.each(ds.fieldMappings, function (i, map) {
      var graph = new AmCharts.StockGraph();
      graph.valueField = map.toField;
      graph.type = 'line';
      graph.lineThickness = 2;
      graph.bullet = 'round';
      graph.bulletBorderColor = '#ffffff';
      graph.fillAlphas = .2;
      graph.title = map.toField.substr(0, 1).toUpperCase() + map.toField.substr(1).replace('_', ' ');
      graph.useDataSetColors = false;
      graph.periodValue = 'Sum';
      if (map.toField == scrollbar_field) {
        scrollbar.graph = graph;
      }
      panel.addStockGraph(graph);
    });

    chart.chartScrollbarSettings = scrollbar;

    chart.panels = [panel];
    return chart;
  };

  /**
   * Write out the given chart to a new location
   * @param AmCharts.AmChart chart the chart to render
   * @param string to_id the id of the element to write to
   */
  mdcharts.render = function (chart, to_id) {
    var el = $('#' + to_id);
    if (!el.css('height') || el.css('height') == '0px') el.css('height', '450px');
    if (!el.css('width') || el.css('width') == '0px') el.css('width', '100%');
    chart.write(to_id);
  };

Drupal.behaviors.mandrill_reports = {
  attach: function (context, settings) {
    console.log(settings.mandrill_reports.engagement);
    var volume_data = mdcharts.fillData($.extend([], settings.mandrill_reports.volume));

    if (volume_data) {
      var volume_chart = mdcharts.getStockChart(volume_data, 'delivered');
      mdcharts.render(volume_chart, 'volume-chart');
    }

    var engage_data = mdcharts.fillData(
      $.extend([], settings.mandrill_reports.engagement),
      86400
    );

    if (engage_data) {
      var engage_chart = mdcharts.getStockChart(engage_data, 'open_rate');
      //Turn off grouping since the Sum grouping doesn't work with percents
      engage_chart.categoryAxesSettings.minPeriod = 'DD';
      engage_chart.categoryAxesSettings.groupToPeriods = ['DD'];

      mdcharts.render(engage_chart, 'engage-chart');
    }
  }
}

})(jQuery);
