=== KB Survey ===
Contributors: adamrbrown
Donate link: http://adambrown.info/b/widgets/donate/
Tags: survey, poll
Requires at least: 2.0
Tested up to: 2.5
Stable tag: trunk

Create multiple-item surveys (not just one-item sidebar polls) and administer them via WordPress.

== Description ==

There are several plugins out there for administering one-item sidebar polls. This is not one of them. This plugin is for administering multi-item surveys--the sort of thing you might use in market research or a sociological study. You can view the results online in summary form, or you can export them as a spreadsheet for statistical analysis.

**You must know PHP to use this plugin.** It does not have a pretty administrative interface (yet). If people express interest, then I'll code one. But as it stands now, you create a new survey by creating a new PHP file (it's not very hard, though). If that bothers you, you might be better off using surveymonkey.com instead of this plugin.

There is a [demo of KB Survey](http://adambrown.info/b/widgets/kb-wordpress-survey-demo/) at my site. Go take a survey, then view the results if you like.

= Support =

If you post your support questions as comments below, I probably won't see them. If the notes in the plugin file don't answer your questions, then post your support questions on a post in the [KB Survey category](http://adambrown.info/b/widgets/category/kb-survey/) at my site.

== Installation ==

1. After unzipping, upload everything in the `kbSurvey` folder to your `/wp-content/plugins/` directory (preserving directory structure).
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create a new page (not post) on your site. Put `[kbsurvey]` in it on a line by itself.

All done. The two demo surveys will now be available at the page you created. If you have trouble, look for more detailed instructions in `/kbSurvey/kbSurvey.php`.

To create a survey of your own:

1. Open up the `/kbSurvey/surveys/` folder. Copy the `demo.php` file and rename it (preserving the `.php` extension).
1. Open your new file and modify it as desired.
1. For further instructions, look at the detailed comments in `/kbSurvey/kbSurvey.php`.

= License =

This plugin is provided "as is" and without any warranty or expectation of function. I'll probably try to help you if you ask nicely, but I can't promise anything. You are welcome to use this plugin and modify it however you want, as long as you give credit where it is due. 

But please don't redistribute this plugin from anywhere other than right here. Unless months go by and it looks like I've completely abandoned this thing, let's not get forks going without a darn good reason; that just confuses people. But send me your improvements and I'll add them in and include a shout-out to you here.

== Screenshots ==

There is a [demo of KB Survey](http://adambrown.info/b/widgets/kb-wordpress-survey-demo/) at my site. Go take a survey, then view the results if you like.

== Frequently Asked Questions ==

Look inside the main kbSurvey.php file for a list of FAQs.

= I have a question that isn't addressed here. =

If you post your support questions as comments below, I probably won't see them. If the notes in the plugin file don't answer your questions, then post your support questions on a post in the [KB Survey category](http://adambrown.info/b/widgets/category/kb-survey/) at my site.