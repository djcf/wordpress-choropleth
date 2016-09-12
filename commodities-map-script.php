<script>
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

    function get_nav_content(region) {
        region.name = region.properties.name;
        var title = '<h2>' + region.name + '</h2>';
        var body = "";
        if (region.name in countries) {
            body = countries[region.name];
        }
        var items = '';
        jQuery.each(tooltips[region.id], function(index, value) {
            link = "<a href='" + value.url + "'>" + value.title + "</a>";
            items = items + '<li>' + link + "</li>";
        });
        var list = "<ul>" + items + "</ul>";
        return title + body + list;
    }

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
        done: function(datamap) {
            datamap.svg.selectAll('.datamaps-subunit').on('click', function(geography) {
                jQuery("#balloon").remove();

                if (geography.id in tooltips) {
                    var bubbly = jQuery("<div id='balloon'></div>");
                    jQuery('body').append(bubbly);

                    bubbly.css({
                        position:"absolute",
                        top: d3.event.pageY,
                        left: d3.event.pageX,
                    });
                    jQuery(bubbly).showBalloon(null,{
                        balloon: get_nav_content(geography),
                    });
                    jQuery.balloon.init();
                }
                //console.log(geography);
            });
            datamap.svg.call(d3.behavior.zoom().on("zoom", redraw));
            function redraw() {
                datamap.svg.selectAll("g").attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
                jQuery('.labels text').css("font-size: " + d3.event.scale + "%");
            }
        }
    });

    var labelsdata = {
        <?php echo implode(", ", $area_series_labels); ?>
    }

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

    wmap.labels({"fontSize": 10, labelColor: "#222", 'customLabelText': labelsdata});

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
    width: 50px;
    height: 100px;
    background: beige none repeat scroll 0% 0%;
    margin: 10px;
    border: 1px solid olive;
    box-shadow: 1px 1px 5px #888888;
 }

 #ctrlButtons a {
    font-size: 32px !important;
    display: block;
    margin: 15px;
}

#ctrlButtons a {
  text-decoration: none;
}

#zoomOutBtn {
  padding-left: 3.5px;
}
</style>
