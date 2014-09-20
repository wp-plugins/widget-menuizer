=== Plugin Name ===
Contributors: drywallbmb
Tags: menus, widgets, sidebars
Requires at least: 3.9.2
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unlocks the full potential of the menu system by allowing sidebar regions to be placed into WordPress menus

== Description ==

Widget Menuizer makes it possible to embed sidebars within your site's menus. That's right: anything you can do with a widget can now be done inside your menus. This makes the menu system much more powerful, as it allows for easy creation of sophisticated "mega dropdowns" and other menu fanciness without completely overhauling the menu management system into something unfamiliar.

Upon activation, when you visit the menu management screen under *Appearance > Menus*, you'll see a new option under the familiar Pages, Posts, Links, Categories and Tags list: Sidebars. This list consists of all the sidebar regions that exist in your currently-active theme. Simply check the box to add a sidebar into your menu the same way you would for any other menu item.

Once in your menu, you'll see a new option, "Container Element," which lets you control which HTML tag is wrapped around the sidebars that are output into the menu.

Because it's possible to put menu widgets inside sidebars, you may see a warning notice if the sidebar region you've put in your menu contains a menu widget. This is because you may have inadvertantly created a recursion: if the menu contained in your sidebar is the same menu your sidebar is placed in, you'll have an infinite loop that will do bad, bad things. So be careful.

== Installation ==

1. Upload the `widget-menuizer` directory to your plugins directory (typically wp-content/plugins)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit Appearance > Menus to add sidebars to your menus (you might have to go into Screen Options to show the Sidebars option)

== Frequently Asked Questions ==

= Why would I want to do this? =

The menu system in WordPress is a powerful but under-utilized feature. "Menus" aren't limited to just regular navigation menus, for example -- they can also be great for things like social media icon links.

But the WordPress menu system is also somewhat limited, in that it generally only offers options for links to individual posts (of all types), categories and tags, in addition to a fairly generic "Link" option. If you wanted to have a nice rich dropdown menu that showed some images, descriptions, or anything beyond just a link, you had to resort to something drastic.

With Widget Menuizer, it's easy to build "megu menus" that have whatever you want in them, because the Widget system itself is so incredibly flexible. With this plugin, you can put anything you can put into a widget into a menu -- which is just about anything at all.

= Why am I seeing an 'infinite loop' warning in my menuized sidebar?  =

Because sidebars can contain menus and menus can now contain sidebars, it's possible to accidentally create a problem where WordPress gets stuck in a loop outputting a menu inside a sidebar inside a menu inside a sidebar... etc. The warning message simply indicates that your sidebar contains a menu widget and thus *might* cause such a recursion. At this time, Widget Menuizer can't actually tell if your sidebar contains the exact same menu the sidebar has been placed into -- just that there's some menu in it somewhere.

If the menu widget your sidebar contains is for a different menu than the one your sidebar is living in, you can safely ignoring the warning. If it's the same menu, however, you'll need to make an adjustment or you'll break your site!

= I changed themes and my sidebar disappeared from my menu. What gives? =

Because the contents of sidebar regions are tied to particular themes (different themes have different regions, after all), if you place a sidebar that belongs to one theme into your menu, and then change themes, the sidebar will not be shown in your menu. *Only sidebars from the active theme can be displayed.*

= I don't see 'Sidebars' as an option in the lefthand column of the Edit Menus page after activating this. Where is it? =

In the upper right corner of your window, click on 'Screen Options' and make sure the Sidebars box is checked.

== Screenshots ==

1. The menu management screen after activation of Widget Menuizer. Notice the entry for 'Sidebars' at the bottom of the lefthand column.

== Changelog ==

= 0.5.5 =
Changing 'attr_title' from a textfield into an option to set where (or whether) to display the title. Also adding a 'none' option to the container.

= 0.5 =
Initial public release.