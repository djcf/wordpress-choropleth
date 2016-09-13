<script>
    //** Specify map settings from plugin data **//

    var series = [ <?php echo implode(",", $area_series_js); ?> ];

    // Datamaps expect data in format:
    // { "USA": { "fillColor": "#42a844", numberOfWhatever: 75},
    //   "FRA": { "fillColor": "#8dc386", numberOfWhatever: 43 } }
    var dataset = {};

    // We need to colorize every country based on "numberOfWhatever"
    // colors should be uniq for every value.
    // For this purpose we create palette(using min/max series-value)
    var onlyValues = series.map(function(obj){ return obj[1]; });
    var minValue = Math.min.apply(null, onlyValues),
            maxValue = Math.max.apply(null, onlyValues);

    // create color palette function
    // color can be whatever you wish
    var paletteScale = d3.scale.linear()
            .domain([minValue,maxValue])
            .range(["#d9cb8f","#714e0f"]); // blue color

    // fill dataset in appropriate format
    series.forEach(function(item){ //
        // item example value ["USA", 70]
        var iso = item[0],
            value = item[1];
        dataset[iso] = { commodities: value, fillColor: paletteScale(value) };
    });

    tooltips = <?php echo json_encode($area_series_nav, JSON_PRETTY_PRINT); ?>;
    countries = <?php echo json_encode($countries, JSON_PRETTY_PRINT); ?>;
    // Create map navigation button elements
    var ctrlButtons = jQuery("<div>", {
      id: 'ctrlButtons',
      class: 'ctrlButtonPanel'
    }).appendTo("#commodities-map");

    var zoomInBtn = jQuery("<a>", {
      id: 'zoomInBtn',
      href: '#',
      html: '+'
    }).appendTo(ctrlButtons);

    var zoomOutBtn = jQuery("<a>", {
      id: 'zoomOutBtn',
      href: '#',
      html: '-'
    }).appendTo(ctrlButtons);

    var width = 800,
        height = 367,
        center = [width / 2, height / 2];



    // render map
    var wmap = new Datamap({
        element: document.getElementById('commodities-map'),
        projection: 'mercator', // big world map
        // countries don't listed in dataset will be painted with this color
        fills: { defaultFill: '#F5F5F5' },
        data: dataset,
        setProjection: function(element) {
          var projection = d3.geo.equirectangular()
              .center([19, 10])
              .rotate([4.4, 0])
              .scale(170)
              .translate([element.offsetWidth / 2, element.offsetHeight / 2]);
          var path = d3.geo.path()
              .projection(projection);
          return {path: path, projection: projection};
        },
        geographyConfig: {
            fontFamily: "Garamond",
            borderColor: '#d9cb8f',
            highlightBorderWidth: 2,
            highlightFillColor: function(geo) {
                return geo['fillColor'] || '#d8e4ca';
            },
            // only change border
            highlightBorderColor: '#B7B7B7'
        },
        done: map_loaded
    });

    // Add labels to the map
    var labelsdata = {
        <?php echo implode(", ", $area_series_labels); ?>
    }

    wmap.labels({"fontSize": 10, labelColor: "#222", 'customLabelText': labelsdata});

    // Arrange the labels so they don't collide
    arrangeLabels();
</script>

<style>
/**
 * All of the CSS for your public-facing functionality should be
 * included in this file.
 */

.ctrlButtonPanel {
    position: absolute;
    top: 0px;
    left: 0px;
    background: beige none repeat scroll 0% 0%;
    margin: 10px;
    border: 1px solid olive;
    box-shadow: 1px 1px 5px #888888;
 }

 #ctrlButtons a {
    font-size: 14px !important;
    display: block;
    margin: 5px 10px 5px 10px;
}

#ctrlButtons a:hover {
  text-decoration: none;
}

#zoomOutBtn {
  padding-left: 2.5px;
}
</style>
