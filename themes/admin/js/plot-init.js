$(window).load(function() {

// plot config and init
  var sin = [], cos = [];
  for (var i = 0; i < 14; i += 0.5) {
      sin.push([i, Math.sin(i)]);
      cos.push([i, Math.cos(i)]);
  }

  var plot = $.plot($("#placeholder"),
         [ { data: sin, label: "sin(x)"}, { data: cos, label: "cos(x)" } ], {
             series: {
                 lines: { show: true },
                 points: { show: true }
             },
             grid: { hoverable: true, clickable: true },
             yaxis: { min: -1.2, max: 1.2 }
           });

  function showTooltip(x, y, contents) {
      $('<div id="tooltip">' + contents + '</div>').css( {
          position: 'absolute',
          display: 'none',
          top: y + 5,
          left: x + 5,
          border: '1px solid #000',
    color:  '#ccc',
    fontSize:  '14px',
          padding: '8px',
    'border-radius': '3px',
    '-moz-border-radius': '3px',
    '-webkit-border-radius': '3px',
          'background-color': '#111',
          opacity: 0.80
      }).appendTo("body").fadeIn(200);
  }

  var previousPoint = null;
  $("#placeholder").bind("plothover", function (event, pos, item) {
      $("#x").text(pos.x.toFixed(2));
      $("#y").text(pos.y.toFixed(2));

      if ($("#enableTooltip:checked").length > 0) {
          if (item) {
              if (previousPoint != item.dataIndex) {
                  previousPoint = item.dataIndex;
                  
                  $("#tooltip").remove();
                  var x = item.datapoint[0].toFixed(2),
                      y = item.datapoint[1].toFixed(2);
                  
                  showTooltip(item.pageX, item.pageY,
                              item.series.label + " of " + x + " = " + y);
              }
          }
          else {
              $("#tooltip").remove();
              previousPoint = null;            
          }
      }
  });

  $("#placeholder").bind("plotclick", function (event, pos, item) {
      if (item) {
          $("#clickdata").text("You clicked point " + item.dataIndex + " in " + item.series.label + ".");
          plot.highlight(item.series, item.datapoint);
      }
  });

});