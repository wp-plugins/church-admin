=== church_admin ===
Contributors: andymoyle
Donate link: http://www.themoyles.co.uk/
Tags: church admin, sms, smallgroups, rota, email, address list, calendar
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 0.33.2.2

A church admin plugin with calendar,address book, small group categories,sunday rota and bulk sms and mailshot facilities. 

== Description ==

This plugin is for church wordpress site - it adds an easy to use address directory and you can email different groups of people.

*   Small Groups - add, edit and delete

*   Members - add, edit and delete

*   Email- send an email to members, parents or small group leaders. Now has a template - make sureyou update your settings to include Facebook page and twitter if you use them!

*   SMS - send bulk sms to members using www.bulksms.co.uk account (not just UK!)

*   Sunday Rota - create and show rotas for your volunteers.

*   Attendance tracking and graphs

*   Visitor tracking and follow up

*   Calendar - month to view, agenda view and nth day recurring events (eg 3rd Sunday)

*   The calendar now includes that most powerful of planning tools - the year planner!

== Installation ==

1. Upload the `church_admin` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Place [church_admin type=address-list] on the page you want the address book displayed. 
4. Place [church_admin type=small-groups-list] on the page you want the small group list displayed
5. Place [church_admin type=small-groups] on the page you want the list of small groups and their members displayed
6. Place [church_admin type=rota] on the page you want the rota displayed
7. Place [church_admin type=calendar] on the page you want a page per month calendar displayed
8. Place [church_admin type=calendar-list] on the page you want a agenda view calendar - option category and weeks options pastable from category admin page
9. There is a calendar widget with customisable title, how many events you want to show and an option for it to look like a post-it note

We recommend password protecting the pages - if it is password protected a link is provided to logout


== Frequently Asked Questions ==

http://www.themoyles.co.uk/church-admin-wordpress-plugin/plugin-support

== Screenshots ==
1. Adding a category to the calendar section
2. Rolling average attendance graph
3. Send a text message


== Changelog ==
= 0.1 =
* Initial release
= 0.2 =
* Minor bug fixes for small groups
= 0.21 =
* Minor visitor fixes
= 0.31 =
* Calendar functionality added
= 0.31.2 =
* Oops install directory on wordpress.org is church-admin not church_admin
= 0.31.3 =
* Widget displays multple events per day and sorted by start date and time
= 0.31.4 =
* Calendar deletes added
* Improved calendar table
* Fixed jquery conflict bug on admin page
* Rota shows this Sunday rather than next Sunday
= 0.32.1 =
* A4 Calendar added
= 0.32.2 =
* Admin pages now all valid XHTML, and external CSS
= 0.32.3 =
* Formatting fixes
= 0.32.4 =
* DB fixes where prefix not wp_
* Improved calendar tooltips
* Cronemail.php not auto generated now!
* Calendar Widget compatable with most themes now!
= 0.32.5 =
* Adjustable width calendar table from settings page
= 0.32.5.1 =
* Calendar CSS tweak for WP20:20 theme
= 0.32.7 =
* Calendar errors showing again in red
= 0.32.8 =
* Calendar times use Wordpress Format Settings
* Calendar list view times and dates use Wordpress Format Settings
* PDF's now available in A4, Letter and Legal sizes
* Label options available
= 0.32.9 =
* Agenda View fix
= 0.32.9.1 =
* Fixed cron email issue
= 0.32.9.2 =
* Jquery conflict mode fix
= 0.32.9.3 =
* Added category & weeks to calendar-list shortcode - copy and paste from Category subpage of Calendar menu
= 0.32.9.4 = 
* Fixed calendar admin drop down menu bug
= 0.32.9.5 =
* Error message if calendar event not saved!
= 0.32.9.6 =
* Minor CSS tweak on address-list display for non white backgrounds
= 0.33.1 =
* Non queued emails not being sent fixed
* Email template and view before sending
* Small group now shows current group on directory editing
= 0.33.2.1 = 
* Missing template file added
* Added ability to send immediately
= 0.33.2.2 =
* Email cache directory change
== Upgrade Notice ==
* 0.33.2.2 required

== Credits: ==
