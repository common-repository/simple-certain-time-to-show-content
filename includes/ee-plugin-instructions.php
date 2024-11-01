<?php // SCTSC Instructions Display - Mitchell Bennis | Element Engage, LLC | mitch@elementengage.com
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
	
$eeOutput .= '<article id="eeInstructions">
			<p>' . __('At a time of your choosing show or hide anything enclosed within these shortcode tags', 'simple-certain-time-to-show-content') . ':</p>
			<code>[eeSCTSC]' . __('Your content here', 'simple-certain-time-to-show-content') . '[/eeSCTSC]</code>
			<p>&rarr; ' . __('Set the display schedule for each day and time you want your content to appear.', 'simple-certain-time-to-show-content') . '<br />
				&rarr; ' . __('Select the proper time zone.', 'simple-certain-time-to-show-content') . '<br />
				&rarr; ' . __('Choose to Show or Hide the content during the selected time periods.', 'simple-certain-time-to-show-content') . '<br />
				&rarr; ' . __('Optionally show a message when the main content is not displayed.', 'simple-certain-time-to-show-content') . '<br />
				&rarr; ' . __('The content inside the shortcodes can be anything, from a simple message, to a whole Page, Post or Text Widget.', 'simple-certain-time-to-show-content') . '<br />
				&rarr; ' . __('Use the shortcodes on a Page, Post in a Text Widget or in a theme file with', 'simple-certain-time-to-show-content') . '<em> do_shortcode();</em></p>
			<p>' . __('If you need help,', 'simple-certain-time-to-show-content') . ' <a href="?page=simple-certain-time-to-show-content&tab=support" title="' . __('contact me', 'simple-certain-time-to-show-content') . '" >' . __('please contact me.', 'simple-certain-time-to-show-content') . '</a></p>
			<p><a href="' . plugin_dir_url( __FILE__ ) . '/readme.txt" title="readme.txt" target="_blank">readme.txt</a></p>';
	
?>