var slow_script_timer = function() {
  if (!map_has_loaded) {
    if ((performance.now() - script_start) > 20000) {
      window.location.href = window.location.href.split("?")[0] + "?lores";
    }    
  }
}

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

  // have two functions: one which sorts one which doesn't?
  // split function here?
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

function get_geography_config() {
  return {
            fontFamily: "Garamond",
            borderColor: '#d9cb8f',
            highlightBorderWidth: 2,
            highlightFillColor: function(geo) {
                return geo['fillColor'] || '#d8e4ca';
            },
            // only change border
            highlightBorderColor: '#B7B7B7'
        };
}

function addLegend() {
  // Add "legend"
  var list = jQuery("<ul id='commodities-list' style='float: left;'></ul>").insertAfter("#commodities-map");
  //var legend = jQuery("<h3 class='commodities-map-legend'>Legend</h3>").insertAfter("#commodities-map");

  jQuery.each(labelsdata, function(id, value) {
    add_to_legend(id, get_country_name(id), list, is_high_res());
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

    // Add labels data to the map
    datamap.labels({"fontSize": 10, labelColor: "#222", 'customLabelText': labelsdata});

    // Arrange the labels so they don't collide
    arrangeLabels();

    // Add "legend"
    addLegend();


    if (is_high_res()) {
      map_has_loaded = true;
      jQuery("#hires-warning").remove();
    } else {
      jQuery("#commodities-map").after("<p style='font-size: smaller; float: left; font-style: italic;'>You are viewing a decreased resolution map. As a result, not all countries are shown on the map. <a href='" + window.location.href.split("?")[0] + "?hires'>Click here to load the full resolution map instead.</a></p>");
    }
    jQuery("#commodities-list").after("<p style='font-size: smaller; float: left; font-style: italic;'>Map loaded in " + ((performance.now() - script_start) / 1000) + " seconds</p>");
}

function get_country_name (id) {
  countries_list = get_country_list();
  if (id in countries_list) {
    return countries_list[id];
  }
  return "";
}

function get_country_list() {
  return {
    ABW: 'Aruba',
    AFG: 'Afghanistan',
    AGO: 'Angola',
    AIA: 'Anguilla',
    ALB: 'Albania',
    ALA: 'Åland Islands',
    AND: 'Andorra',
    ARE: 'United Arab Emirates',
    ARG: 'Argentina',
    ARM: 'Armenia',
    ASM: 'American Samoa',
    ATA: 'Antarctica',
    ATF: 'French Southern Territories',
    ATG: 'Antigua and Barbuda',
    AUS: 'Australia',
    AUT: 'Austria',
    AZE: 'Azerbaijan',
    BDI: 'Burundi',
    BEL: 'Belgium',
    BEN: 'Benin',
    BFA: 'Burkina Faso',
    BGD: 'Bangladesh',
    BGR: 'Bulgaria',
    BHR: 'Bahrain',
    BHS: 'Bahamas',
    BIH: 'Bosnia and Herzegovina',
    BLM: 'Saint Barthélemy',
    BLR: 'Belarus',
    BLZ: 'Belize',
    BMU: 'Bermuda',
    BOL: 'Bolivia, Plurinational State of',
    BRA: 'Brazil',
    BRB: 'Barbados',
    BRN: 'Brunei Darussalam',
    BTN: 'Bhutan',
    BWA: 'Botswana',
    CAF: 'Central African Republic',
    CAN: 'Canada',
    CHE: 'Switzerland',
    CHL: 'Chile',
    CHN: 'China',
    CIV: 'Côte d\'Ivoire',
    CMR: 'Cameroon',
    COD: 'Congo',
    COG: 'Congo',
    COK: 'Cook Islands',
    COL: 'Colombia',
    COM: 'Comoros',
    CPV: 'Cape Verde',
    CRI: 'Costa Rica',
    CUB: 'Cuba',
    CUW: 'Curaçao',
    CYM: 'Cayman Islands',
    northern_cyprus: 'Northern Cyprus',
    CYP: 'Cyprus',
    CZE: 'Czech Republic',
    DEU: 'Germany',
    DJI: 'Djibouti',
    DMA: 'Dominica',
    DNK: 'Denmark',
    DOM: 'Dominican Republic',
    DZA: 'Algeria',
    ECU: 'Ecuador',
    EGY: 'Egypt',
    ERI: 'Eritrea',
    ESP: 'Spain',
    EST: 'Estonia',
    ETH: 'Ethiopia',
    FIN: 'Finland',
    FJI: 'Fiji',
    FLK: 'Falkland Islands (Malvinas)',
    FRA: 'France',
    GUF: 'French Guiana',
    FRO: 'Faroe Islands',
    FSM: 'Micronesia, Federated States of',
    GAB: 'Gabon',
    GBR: 'United Kingdom',
    GEO: 'Georgia',
    GGY: 'Guernsey',
    GHA: 'Ghana',
    GIN: 'Guinea',
    GMB: 'Gambia',
    GNB: 'Guinea-Bissau',
    GNQ: 'Equatorial Guinea',
    GRC: 'Greece',
    GRD: 'Grenada',
    GRL: 'Greenland',
    GTM: 'Guatemala',
    GUM: 'Guam',
    GUY: 'Guyana',
    HKG: 'Hong Kong',
    HMD: 'Heard Island and McDonald Islands',
    HND: 'Honduras',
    HRV: 'Croatia',
    HTI: 'Haiti',
    HUN: 'Hungary',
    IDN: 'Indonesia',
    IMN: 'Isle of Man',
    IND: 'India',
    CCK: 'Cocos (Keeling) Islands',
    CXR: 'Christmas Island',
    IOT: 'British Indian Ocean Territory',
    IRL: 'Ireland',
    IRN: 'Iran',
    IRQ: 'Iraq',
    ISL: 'Iceland',
    ISR: 'Israel',
    ITA: 'Italy',
    JAM: 'Jamaica',
    JEY: 'Jersey',
    JOR: 'Jordan',
    JPN: 'Japan',
    KAZ: 'Kazakhstan',
    KEN: 'Kenya',
    KGZ: 'Kyrgyzstan',
    KHM: 'Cambodia',
    KIR: 'Kiribati',
    KNA: 'Saint Kitts and Nevis',
    KOR: 'Korea',
    kosovo: 'Kosovo',
    KWT: 'Kuwait',
    LAO: 'Lao',
    LBN: 'Lebanon',
    LBR: 'Liberia',
    LBY: 'Libya',
    LCA: 'Saint Lucia',
    LIE: 'Liechtenstein',
    LKA: 'Sri Lanka',
    LSO: 'Lesotho',
    LTU: 'Lithuania',
    LUX: 'Luxembourg',
    LVA: 'Latvia',
    MAC: 'Macao',
    MAF: 'Saint Martin (French part)',
    MAR: 'Morocco',
    MCO: 'Monaco',
    MDA: 'Moldova',
    MDG: 'Madagascar',
    MDV: 'Maldives',
    MEX: 'Mexico',
    MHL: 'Marshall Islands',
    MKD: 'Macedonia',
    MLI: 'Mali',
    MLT: 'Malta',
    MMR: 'Myanmar',
    MNE: 'Montenegro',
    MNG: 'Mongolia',
    MNP: 'Northern Mariana Islands',
    MOZ: 'Mozambique',
    MRT: 'Mauritania',
    MSR: 'Montserrat',
    MUS: 'Mauritius',
    MWI: 'Malawi',
    MYS: 'Malaysia',
    NAM: 'Namibia',
    NCL: 'New Caledonia',
    NER: 'Niger',
    NFK: 'Norfolk Island',
    NGA: 'Nigeria',
    NIC: 'Nicaragua',
    NIU: 'Niue',
    NLD: 'Netherlands',
    NOR: 'Norway',
    NPL: 'Nepal',
    NRU: 'Nauru',
    NZL: 'New Zealand',
    OMN: 'Oman',
    PAK: 'Pakistan',
    PAN: 'Panama',
    PCN: 'Pitcairn',
    PER: 'Peru',
    PHL: 'Philippines',
    PLW: 'Palau',
    PNG: 'Papua New Guinea',
    POL: 'Poland',
    PRI: 'Puerto Rico',
    PRK: 'Korea',
    PRT: 'Portugal',
    PRY: 'Paraguay',
    PSE: 'Palestinian Territories',
    PYF: 'French Polynesia',
    QAT: 'Qatar',
    ROU: 'Romania',
    RUS: 'Russian Federation',
    RWA: 'Rwanda',
    ESH: 'Western Sahara',
    SAU: 'Saudi Arabia',
    SDN: 'Sudan',
    SSD: 'South Sudan',
    SEN: 'Senegal',
    SGP: 'Singapore',
    SGS: 'South Georgia and the South Sandwich Islands',
    SHN: 'Saint Helena, Ascension and Tristan da Cunha',
    SLB: 'Solomon Islands',
    SLE: 'Sierra Leone',
    SLV: 'El Salvador',
    SMR: 'San Marino',
    somaliland: 'Somaliland',
    SOM: 'Somalia',
    SPM: 'Saint Pierre and Miquelon',
    SRB: 'Serbia',
    STP: 'Sao Tome and Principe',
    SUR: 'Suriname',
    SVK: 'Slovakia',
    SVN: 'Slovenia',
    SWE: 'Sweden',
    SWZ: 'Swaziland',
    SXM: 'Sint Maarten (Dutch part)',
    SYC: 'Seychelles',
    SYR: 'Syrian Arab Republic',
    TCA: 'Turks and Caicos Islands',
    TCD: 'Chad',
    TGO: 'Togo',
    THA: 'Thailand',
    TJK: 'Tajikistan',
    TKM: 'Turkmenistan',
    TLS: 'Timor-Leste',
    TON: 'Tonga',
    TTO: 'Trinidad and Tobago',
    TUN: 'Tunisia',
    TUR: 'Turkey',
    TWN: 'Taiwan',
    TZA: 'Tanzania',
    UGA: 'Uganda',
    UKR: 'Ukraine',
    URY: 'Uruguay',
    USA: 'United States',
    UZB: 'Uzbekistan',
    VCT: 'Saint Vincent and the Grenadines',
    VEN: 'Venezuela',
    VGB: 'Virgin Islands, British',
    VIR: 'Virgin Islands, U.S.',
    VNM: 'Viet Nam',
    VUT: 'Vanuatu',
    WLF: 'Wallis and Futuna',
    WSM: 'Samoa',
    YEM: 'Yemen',
    ZAF: 'South Africa',
    ZMB: 'Zambia',
    ZWE: 'Zimbabwe',
    SJM: 'Svalbard and Jan Mayen',
    BES: 'Bonaire, Sint Eustatius and Saba',
    MYT: 'Mayotte',
    MTQ: 'Martinique',
    REU: 'Réunion',
    VAT: 'Holy See (Vatican City State)',
    TKL: 'Tokelau',
    TUV: 'Tuvalu',
    BVT: 'Bouvet Island',
    GIB: 'Gibraltar',
    GLP: 'Guadeloupe',
    UMI: 'United States Minor Outlying Islands'
  };
}