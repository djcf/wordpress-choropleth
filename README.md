=== Simple Choropleth ===
* Contributors: daniel
* Donate link: http://djcf.github.com
* Sponsored by: SynBioWatch (www.synbiowatch.org)
* Tags: mapping, chloropleth
* Requires at least: 4.3
* Tested up to: 4.3
* Stable tag: 4.3
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Prints a clickable chlorpleth navigation aid using the [commodites-map] shortcode.

== Description ==

This plug in adds a chloropleth to your wordpress using a shortcode. 

A chloropleth is a special kind of map where each region is colour-coded according to some value. For example, if you were charting world populations, you could make more populous countries appear darker.

== Installation ==

Clone this repository to your wordpress plugins area, or upload it to your server using FTP. If you download this repository as a zip archive, you must extract the contents before using the plugin. Then click "Activate Plugin" from the Wordpress plugins area.

== Usage ==

You should now have a new custom post type, the "Commodity". Create a new "Commodity" and add the custom attribute "country_type" below the content. You can now associate custom posts to countries; the most custom posts you associate with a particular country, the darker that country will appear on the map.

To insert the map, use the shortcode [commodities_map] from within any post type.
