function get_toolbar_content(region) {
    region.name = region.properties.name;
    var title = '<h2>' + region.name + '</h2>';
    var body = "";
    //if (region.name in countries) {
    //    body = countries[region.name];
    //}
    var items = '';
    jQuery.each(tooltips[region.id], function(index, value) {
        link = "<a href='" + value.url + "'>" + value.title + "</a>";
        items = items + '<li>' + link + "</li>";
    });
    var list = "<ul>" + items + "</ul>";
    return title + body + list;
}

function arrangeLabels() {
  var move = 1;
  while(move > 0) {
      move = 0;
      d3.selectAll("text").each(function() {
           var that = this,
               a = this.getBoundingClientRect();
           d3.selectAll("text")
              .each(function() {
                if(this != that) {
                  var b = this.getBoundingClientRect();
                  if((Math.abs(a.left - b.left) * 2 < (a.width + b.width)) &&
                     (Math.abs(a.top - b.top) * 2 < (a.height + b.height))) {
                    // overlap, move labels
                    var dx = (Math.max(0, a.right - b.left) +
                             Math.min(0, a.left - b.right)) * 0.01,
                        dy = (Math.max(0, a.bottom - b.top) +
                             Math.min(0, a.top - b.bottom)) * 0.02,
                        tt = d3.transform(d3.select(this).attr("transform")),
                        to = d3.transform(d3.select(that).attr("transform"));
                    move += Math.abs(dx) + Math.abs(dy);

                    to.translate = [ to.translate[0] + dx, to.translate[1] + dy ];
                    tt.translate = [ tt.translate[0] - dx, tt.translate[1] - dy ];
                    d3.select(this).attr("transform", "translate(" + tt.translate + ")");
                    d3.select(that).attr("transform", "translate(" + to.translate + ")");
                    a = this.getBoundingClientRect();
                  }
                }
              });
         });
     }
}

function add_to_legend(country_code, country_name, list, sorting) {
	var country_name_li = jQuery("<li>")
	  .attr("data-name", country_name);

	if (sorting) {
	  var sorted = false;
	  jQuery.each(list.children(), function(index, item) {
		if (!sorted && (item.getAttribute("data-name") > country_name)) {
			country_name_li.insertBefore(item);
			sorted = true;
		}
	  });
	  if (!sorted) {
	     country_name_li.appendTo(list);
	  }
	} else {
	  country_name_li.appendTo(list);
	}

	if (list.children().length < 1) {
	   country_name_li.appendTo(list);
	}

  var country_name_link = jQuery("<a>")
    .attr("href", "#")
    .addClass("navhelper")
    .text(country_name)
    .appendTo(country_name_li)
    .click(function(event) {
        event.preventDefault();
        var commoditiesList = jQuery( this ).parent().children("ul");
        if (commoditiesList.hasClass("hidden")) {
          commoditiesList.slideDown();
          commoditiesList.removeClass("hidden");
        } else {
          commoditiesList.slideUp();
          commoditiesList.addClass("hidden");
	      }
	  });
	var country_commodities_list = jQuery("<ul>")
    .addClass("hidden")
    .appendTo(country_name_li);
    jQuery.each(tooltips[country_code], function(i, v) {
        var commodity_item = jQuery("<li>")
          .appendTo(country_commodities_list)
        var commodity_link = jQuery("<a>")
          .text(v.title)
          .attr("href", v.url)
          .appendTo(commodity_item)
    });
}

function addLegend() {
  // Add "legend"
  var list = jQuery("<ul id='commodities-list'></ul>").insertAfter("#commodities-map");
  //var legend = jQuery("<h3 class='commodities-map-legend'>Legend</h3>").insertAfter("#commodities-map");
  var legend = [];

  jQuery.each(wmap.worldTopo.objects.world.geometries, function( index, value ) {
      if (value.id in labelsdata) {
        add_to_legend(value.id, value.properties.name, list, true);
	      legend.push(value.id);
      }
  });
  
  jQuery.each({"COM": "Comoros", "PYF": "French Polynesia", "TON": "Tonga", "SGP": "Singapore", "REU": "Reunion"}, function (key, value) {
    if (!(key in legend) && (key in tooltips)) {
       add_to_legend(key, value, list, true);
	     legend.push(value.id);
    }
  });

  jQuery('.navhelper').click(function(event){
      event.preventDefault();
  });
}

add_tooltip = function(geography) {
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
            balloon: get_toolbar_content(geography),
        });
        jQuery.balloon.init();
    }
    //console.log(geography);
};

map_loaded = function(datamap) {
      // Redraw the map when a mouse scroll wheel is used
      function redraw() {
          datamap.svg.selectAll("g").attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
          //jQuery('.labels text').css("font-size: " + d3.event.scale + "%");
          console.log("redrew");
      }

      // Redraw the map when the zoom button is used
      function zoomed() {
          datamap.svg.selectAll("g").attr("transform", "translate(" + zoom.translate() + ")scale(" + zoom.scale() + ")");
          console.log("zoomed");
      }

      // Stop zooming if the extent is met
      function disableZoom(){
          datamap.svg.on("mousedown.zoom", null)
             .on("touchstart.zoom", null)
             .on("touchmove.zoom", null)
             .on("touchend.zoom", null);
      }

      // Add a zoom function to the zoom buttons
      function zoomButton(zoom_in){ // bool
          var scale = zoom.scale(),
              extent = zoom.scaleExtent(),
              translate = zoom.translate(),
              x = translate[0], y = translate[1],
              factor = zoom_in ? 1.25 : 1/1.25,
              target_scale = scale * factor;

          // If we're already at an extent, done
          //if (target_scale === extent[0] || target_scale === extent[1]) { return false; }
          // If the factor is too much, scale it down to reach the extent exactly
          var clamped_target_scale = Math.max(extent[0], Math.min(extent[1], target_scale));
          if (clamped_target_scale != target_scale){
              target_scale = clamped_target_scale;
              factor = target_scale / scale;
          }

          // Center each vector, stretch, then put back
          x = (x - center[0]) * factor + center[0];
          y = (y - center[1]) * factor + center[1];

          // Transition to the new view over 100ms
          d3.transition().duration(100).tween("zoom", function () {
              var interpolate_scale = d3.interpolate(scale, target_scale),
                  interpolate_trans = d3.interpolate(translate, [x,y]);
              return function (t) {
                  zoom.scale(interpolate_scale(t))
                      .translate(interpolate_trans(t));
                  zoomed();
              };
          }).each("end", function(){
              if (pressed) zoomButton(zoom_in);
          });
      }

    // Attach or reattach a tooltip with region information.
      datamap.svg.selectAll('.datamaps-subunit').on('click', add_tooltip);

      // Zoom the map when the mouse's scroll wheel is used
      datamap.svg.call(d3.behavior.zoom().on("zoom", redraw));

      // Zoom the map when the zoom buttons are pressed
      var zoom = d3.behavior.zoom()
          .scaleExtent([0.9, 8])
          .on("zoom", zoomed);

      // prevent the zoom buttons from running their normal handlers
      jQuery('.ctrlButtonPanel a').click(function(event){
          event.preventDefault();
      });

      // Zoom the viewport until the required zoom level is met
      var pressed = false;
      d3.selectAll('.ctrlButtonPanel a').on('mousedown', function(){
          pressed = true;
          disableZoom();
          zoomButton(this.id === 'zoomInBtn');
      }).on('mouseup', function(){
          pressed = false;
      }).on('mouseout', function(){
          pressed = false;
      })
      datamap.svg.on("mouseup", function(){datamap.svg.call(zoom)});


}
