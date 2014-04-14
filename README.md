gravityforms-remaining-entries-shortcode
========================================
Contributors: ctlt-dev
Tags: gravityforms, remaining-entries
Requires at least: 3.8
Tested up to: 3.8
Stable tag: 0.1
License: GPLv2 or later

Lets you display gravityform's remaining entries via shortcode.

NOTICE: only works when you limit entries by total entries.

== Description ==

To add tabs to you post or pages just add this shortcode:

`[gravityforms action="entries_left" format="decimal" id="your_form_id"]`

* action = (required) exactly as above so that gravity forms knows which shortcode to call
* format = (optional) either leave out for no separators, "decimal", "comma" eg. 4987 vs 4.987 vs 4,987
* id = (required) id of your form

== Installation ==

1. Download 'gravityforms-remaining-entries-shortcode.zip'
2. Extract the zip file into wp-content/plugins/ in your WordPress installation
3. Go to plugins page to activate
