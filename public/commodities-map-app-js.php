<script>
    script_start = performance.now();
    //** Specify map settings from plugin data **//
    if (is_high_res()) {
        map_has_loaded = false;
        window.setInterval(slow_script_timer, 1000);

        jQuery("#commodities-map").html("<div id='hires-warning'>It looks like the map is taking a long time to load. <a href='" + window.location.href.split("?")[0] + "?lores'>Click here to see the low-resolution map instead.</a></div>");
    }

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

    var commodities =  <?php echo json_encode($commodities, JSON_PRETTY_PRINT); ?>;

    tooltips = <?php echo json_encode($area_series_nav, JSON_PRETTY_PRINT); ?>;
    //countries = <?php //echo json_encode($countries, JSON_PRETTY_PRINT); ?>;

        // Define data for the map labels
    labelsdata = {
        <?php echo implode(", ", $area_series_labels); ?>
    }

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
        geographyConfig: get_geography_config(),
        done: map_loaded
    });
</script>