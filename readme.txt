=== church_admin ===
Contributors: andymoyle
Donate link: http://www.themoyles.co.uk/
Tags: church admin, sms, smallgroups, rota, email, address list, calendar
Requires at least: 2.7.0
Tested up to: 4.0
Stable tag: 0.603

A church admin plugin with calendar,address book, small group categories,sunday rota and bulk sms and mailshot facilities. 

== Description ==

This plugin is for church wordpress site - it adds an easy to use address directory and you can email different groups of people.

*   Small Groups - add, edit and delete

*   Members - add, edit and delete

*   Email- send an email to members, parents or small group leaders. Now has a template - make sureyou update your settings to include Facebook page and twitter if you use them!

* Directory syncs to Mailchimp (not back yet)

*   SMS - send bulk sms to members using www.bulksms.co.uk account (not just UK!)

*   Sunday Rota - create and show rotas for your volunteers.

*   Attendance tracking 

*   Ministries - people can have different ministries they are involved in and be sent SMS or email by role, other functions coming soon.

*   Google map integrations for small groups and directories

*   Calendar - month to view, agenda view and nth day recurring events (eg 3rd Sunday)
*  Facilities - manage facilities like rooms and equipment and their bookings.
*   The calendar now includes that most powerful of planning tools - the year planner!

== Installation ==

1. Upload the `church_admin` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Place [church_admin type=address-list member_type_id=# map=1 photo=1] on the page you want the address book displayed, member_type=1 for members, map=1 toshow map for geocoded addresses. The member_type_id can be comma separated  e.g. member_type_id=1,2,3 
4. Place [church_admin type=small-groups-list] on the page you want the small group list displayed
5. Place [church_admin type=small-groups ] on the page you want the list of small groups and their members displayed
6. Place [church_admin type=rota] on the page you want the rota displayed
7. Place [church_admin type=calendar category=# weeks=#] on the page you want a page per month calendar displayed
8. Place [church_admin type=calendar-list] on the page you want a agenda view calendar - option category and weeks options pastable from category admin page
9. There is a calendar widget with customisable title, how many events you want to show and an option for it to look like a post-it note
10. Place [church_admin_map member_type_id=#] to show a map of colour coded small groups - need to set a service venue first to centre map and geolocate member's addresses by editing them.
We recommend password protecting the pages - if it is password protected, a link is provided to logout
The # should be replaced with which member types you want displayed as a comma separated list e.g. member_type=1,2

== Frequently Asked Questions ==

http://www.themoyles.co.uk/church-admin-wordpress-plugin/plugin-support

== Screenshots ==
1. Adding a category to the calendar section
2. Rolling average attendance graph
3. Send a text message


== Changelog ==
= 0.607 =
* Initial Install bug fix for calendar table
= 0.605 =
* Calendar List bug fix
= 0.604 =
* Fix date table for new installs
= 0.603 =
* Directory - display household ministries fix
* Added hope team to edit people
= 0.601 =
* Sermon mp3 file edit path bug fix
= 0.600 =
* Fixed font size on post it notes
* People can be in more then one small group
* ssl proof the plugin, by using better formed include paths and uris
* Fixed directory edit people bugs
= 0.5970 =
* Added icon and banner
= 0.5969 =
* Added empty index.php to email cache directory
= 0.5968 = 
* Obfuscate backup filename
= 0.5965 = 
* Simplified calendar events database 
* Added image for calendar events which appears as thumbnail on widget and popups
* Added Hope Team - practical ministries

= 0.5962 = 
* Attendance Graph Updated
* Added Hope team - practical helps
= 0.5961 =
* Address list pdf line space bug
* Hope team
* Fix pdf nonces
= 0.5957 =
* Latest Sermons widget Control
* Calender remove empty class warning
* Directory remove empty class warning
* Fix Calendar Postit style width
= 0.5955 = 
* Bug fixes to directory
= 0.5952 =
* Made audio tag valid html5
* Fixed rota pdf issue 
= 0.5951 =
* Fix repeated itunes link on sermon pages
= 0.5950 =
* Fix  permissions bug
= 0.5949 = 
* fix small group map and addres list map bugs
= 0.5948 = 
* Add small group attendance indicators to directory edit pages and pdf
= 0.5946 = 
* Tidy up sermon podcast display page
= 0.5945 =
* Remove console.log() from maps js for old IE versions
= 0.5944 =
* Correct links in sermon widget and itunes links
= 0.5943 = 
* Users can edit own entry 
* Address list uses microdata and valid HTML5
= 0.5941 =
* US and Aus translations added
= 0.5940 =
* Rota display bugs fixed on screen and pdf
= 0.5937 =
* Fix mobile linking on single mobile households
= 0.5936 =
* Hyperlinking within address list pdf
= 0.5935 = 
* Address list display and PDF tweaks
= 0.5934 =
* Display multiple surname for a household, still sorted alphabetically by first surname in directory
= 0.5933 =
* Closing div fix when not using google maps in address list
= 0.5931 = 
* Put first name against mobile numbers where more than one in a household
* Made implementation of recaptcha not clash with other plugins
* Put all scripts in footer and removed version number to improve load speed
* Put CSS in one file
= 0.5930 =
* Bug fixes - nonces for xmls for maps
* Ministries pdf, showing who is doing what
* Widget for latest sermons
= 0.5920 =
* Made protective nonces only valid for given member types
* Fixed address-xml link error
= 0.5910 =
* Changed Rota PDF to allow more text and initials instead of full names
= 0.5901 =
* Added nonces to all download links, to protect privacy
= 0.5858 =
* Make post editing screen for people and household clearer
= 0.5857 =
* Get rid of activation error by re-encoding as UTF8 without BOM
= 0.5851 = 
* Mailchimp sync with directory
= 0.5841 =
* Correct rota email pdf link
= 0.584 = 
* Email out service rota fix
= 0.583 =
* Calendar Series Edit Bug Fix
= 0.582 =
* Fix permissions bug
= 0.581 =
* Admin page css improvements for WP3.8x
* Drop Down date CSS fix
= 0.580 =
* Directly update who is in a ministry on the ministry page
= 0.579 =
* Add attachment page link for directory photos
* Add play counter for sermon mp3 played with <audio> and download, but not flash
= 0.575 =
* Fix for associating wordpress logins with directory people
= 0.574 =
* Fix vcard address missing
* Fix search form in people meta box after edit people
* Change donate message
= 0.573 =
* Move household - allow create new household with same address
* Search from edit people pages fix
= 0.572 =
* Birthdays bug fix
* Fix previous date shown in wrong format for editing people
= 0.571 =
* Fix people csv download bug
* Fix Date Picker CSS
= 0.570 =
* Birthdays Shortcode and widget
= 0.568 =
* Add individual user permissions
* Fix date picker to force ISO to internationalise
* Improve wordpress user functions when editing people
= 0.567 =
* People CSV spreadsheet download added into admin people meta box
* Fix diacritics (language accents) misprinting in pdfs
* Admin meta boxes only show for those with permissions
= 0.566 =
* Small group order made sortable
= 0.565 =
* Fixed front end registration with reCaptcha protection
= 0.564 =
* Updated Internationalisation
= 0.563 =
* Fix Follow up email not sending all information
= 0.562 =
* Fix Address label bug
* Fix creating new small group in edit people form not saving the person as a small group leader
= 0.561 =
* Add Word/PDF file upload to sermon podcasting
= 0.560 =
* Added Google metadata to events in calendar widget (event details should show in search results)
* Tidied up how autocomplete people are shown
* Fixed Itunes Category
* Added Itunes File Subtitle
* Fixed address not showing in follow up activity emails
= 0.559 =
* Add Subtitle to Itunes podcasts
= 0.558 =
* Tidy up the rota - no extra commas
= 0.557 =
* Dutch prefix support
= 0.556 =
* minor bug fixes
= 0.555 =
* Small Group geocoding
* More international friendly address storage and use
= 0.554 =
* Address list still displays if member_type_id=# is used!
= 0.553 =
* Image bug fix on address list display
= 0.552 = 
* Double shortcode bug fix
* Google small group max fix [church_admin_map member_type_id=#]
= 0.551 =
* Household Edit - old data displayed in form fix
= 0.550 =
* Option of Photos on address list shortcode
* List of all shortcodes on main admin page
= 0.542 = 
* Activation headers error on new installs bug fix
= 0.541 =
* Comms Setting cron instructions bug fix
* community.bulksms.co.uk fix
* Major rota bug fixes for upgrades
= 0.53 =
* Bug Fix
= 0.52 =
* Remove redundant chmod on old email cache directory
= 0.50 =
* Bug fixes for fresh installs
* Sermon Podcasting
* better rota handling (autocomplete) and ability to email weekly service rotas to participants
* Move email cache to uploads/church-admin-cache directory and handle redirect
= 0.4.91 =
* Updates rota table to new format
* Address list pdf and shortcode can have comma separated member_type_ids
= 0.4.8 =
* Podcasting and autocomplete for rota
= 0.4.73 =
* Install Member type table bug fix
= 0.4.72 =
* Tweak CSV support for rotas
= 0.4.71 =
* Added CSV download support for rotas
= 0.4.7 =
* Added Internationalisation
= 0.4.632 =
* Fix calendar link bugs
= 0.4.60 =
* Creating wp user for people fixed
= 0.4.59 =
* Address List pdf bug fixed
= 0.4.57 =
* Bug Fixes
* Departments/Roles renamed to ministries for clarity
= 0.4.56 =
* Bug Fixes
* Admin home screen tidied up
= 0.4.3 =
* Security vulnerability fixed
= 0.4.2 =
* Google map of small groups members [church_admin_map member_type_id=#]
= 0.4.1 =
* Bug fixes for rewrite
= 0.4.0 =
* Major Rewrite, especially how the directory is handled and stored
= 0.33.4.5 =
* Rota Gremlins fixed
= 0.33.4.4 =
* Apologies, your rota would have been duplicated. This fix  stops it happening on further upgrades.
= 0.33.4.3 =
Clear out files
= 0.33.4.0 =
* PDFs created dynamically
= 0.33.3.3 =
* UTF8 DB conversion
= 0.33.3.2 =
* Calendar Year planner added choices to main directory list
= 0.33.3.1 =
* Fixed add calendar event bug where details same as previous event not being saved.
= 0.33.3.0 =
* Email jquery no conflict wrappers
= 0.33.2.9 =
* calendar list format bug fix
= 0.33.2.8 =
* Fixed another calendar bug - next and previous
= 0.33.2.7 =
* Fixed calendar display dropdown menu year sticking.
= 0.33.2.6 =
* Fixed calendar display bug and calendar caching on editing or delete.
= 0.33.2.5 =
* Added more years to year planner caching
= 0.33.2.4 =
* Fix salutation missing from 1st email address for each family when sent instantly
= 0.33.2.3 =
* Attendance Graph Shortcodes
= 0.33.2.2 =
* Email cache directory change
= 0.33.2.1 = 
* Missing template file added
* Added ability to send immediately
= 0.33.1 =
* Non queued emails not being sent fixed
* Email template and view before sending
* Small group now shows current group on directory editing
= 0.32.9.6 =
* Minor CSS tweak on address-list display for non white backgrounds
= 0.32.9.5 =
* Error message if calendar event not saved!
= 0.32.9.4 = 
* Fixed calendar admin drop down menu bug
= 0.32.9.3 =
* Added category & weeks to calendar-list shortcode - copy and paste from Category subpage of Calendar menu
= 0.32.9.2 =
* Jquery conflict mode fix
= 0.32.9.1 =
* Fixed cron email issue
= 0.32.9 =
* Agenda View fix
= 0.32.8 =
* Calendar times use Wordpress Format Settings
* Calendar list view times and dates use Wordpress Format Settings
* PDF's now available in A4, Letter and Legal sizes
* Label options available
= 0.32.7 =
* Calendar errors showing again in red
= 0.32.5.1 =
* Calendar CSS tweak for WP20:20 theme
= 0.32.5 =
* Adjustable width calendar table from settings page
= 0.32.4 =
* DB fixes where prefix not wp_
* Improved calendar tooltips
* Cronemail.php not auto generated now!
* Calendar Widget compatable with most themes now!
= 0.32.3 =
* Formatting fixes
= 0.32.2 =
* Admin pages now all valid XHTML, and external CSS
= 0.32.1 =
* A4 Calendar added
= 0.31.4 =
* Calendar deletes added
* Improved calendar table
* Fixed jquery conflict bug on admin page
* Rota shows this Sunday rather than next Sunday
= 0.31.3 =
* Widget displays multple events per day and sorted by start date and time
= 0.31.2 =
* Oops install directory on wordpress.org is church-admin not church_admin
= 0.31 =
* Calendar functionality added
= 0.21 =
* Minor visitor fixes
= 0.2 =
* Minor bug fixes for small groups
= 0.1 =
* Initial release


* 0.566 required
== Credits: ==