<?php

/**
 * @package Simple Certain Time to Show Content
 */
/*
Plugin Name: Simple Certain Time to Show Content
Plugin URI: https://elementengage.com/simple-certain-time-to-show-content/
Description: At a time of your choosing, simply show or hide anything enclosed within a set of shortcodes. | <a href="https://elementengage.com/donate/">Donate</a> | <a href="https://elementengage.com/contact-me/">Feedback</a>
Author: Mitchell Bennis - Element Engage, LLC
Version: 1.3.1
Author URI: https://elementengage.com
License: GPLv2 or later
Text Domain: simple-certain-time-to-show-content
Domain Path: /languages
*/
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );

define('eeSCTSC_Version', '1.3.1');
$eeSCTSC_Name = 'Simple Certain Time to Show Content'; // Titling
$eeSCTSC_Slug = 'simple-certain-time-to-show-content'; // Permalink
$eeSCTSC_Acronym = 'eeSCTSC'; // DB option_name - Can you say acronym?
$eeSCTSC_MenuLabel = 'Show Content'; // Menu Label
$eeSCTSC_MenuIcon = 'dashicons-clock'; // Menu Icon --> https://developer.wordpress.org/resource/dashicons/
$eeSCTSC_Days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

// Initialize
$eeSCTSC_Settings = array(); // Holds the plugin settings pulled from the database


// Enable shortcodes in text widgets
add_filter('widget_text','do_shortcode');


// --- FUNCTIONS -------------------------

// Admin <head> Requirements
function eeSCTSC_AdminHeadTags() {

	// Enqueue CSS
	wp_enqueue_style('eeSCTSC_TimePicker_CSS', plugin_dir_url( __FILE__ ) . 'timepicker/jquery.timepicker.css', array(), eeSCTSC_Version);
	wp_enqueue_style('eeSCTSC_Main_CSS', plugin_dir_url( __FILE__ ) . 'ee-style.css', array(), eeSCTSC_Version); // Version 2 as per your example
	
	// Enqueue JavaScript
	wp_enqueue_script('eeSCTSC_TimePicker_JS', plugin_dir_url( __FILE__ ) . 'timepicker/jquery.timepicker.js', array('jquery'), eeSCTSC_Version, true);
	wp_enqueue_script('eeSCTSC_Main_JS', plugin_dir_url( __FILE__ ) . 'ee-javascript.js', array('jquery'), eeSCTSC_Version, true);

}
add_action('admin_enqueue_scripts', 'eeSCTSC_AdminHeadTags');





// Plugin Activation
function eeActivate_SCTSC() { // Simply adding a single entry to the wp_options table, with option_name = eeSCTSC
	
	global $wpdb;
	
	$eeSCTSC_Acronym = 'eeSCTSC'; // Can you say acronym?
	
	// Check if options exist in the database
	if(get_option($eeSCTSC_Acronym) === FALSE) {
		
		// We simply store our settings in a multi-dimensionally delimited text string ;-)
		// Format: key=value|key=value|key=value|key=value
		$eeSCTSC_Settings = 'Days=Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday|'; // Days of the week
		$eeSCTSC_Settings .= 'From=08:00am|'; // ON Time
		$eeSCTSC_Settings .= 'To=10:00pm|'; // OFF Time
		$eeSCTSC_Settings .= 'TimeZone=|'; // Intended Time Zone
		$eeSCTSC_Settings .= 'Mode=Show|'; // OFF Time
		$eeSCTSC_Settings .= 'AltContent='; // The optional OFF message
		
		// Add the new option_names
		update_option($eeSCTSC_Acronym, $eeSCTSC_Settings);
				
	} // ENDs install check

}
register_activation_hook( __FILE__, 'eeActivate_SCTSC' );



// Show or Hide Content Based on Time and Day Settings
function eeSimpleCertainTimetoShowContent($eeSCTSC_Days, $eeSCTSC_From, $eeSCTSC_To, $eeTimeZone = 'UTC') {

	// Time Format: 6:16 pm - Hour:Minutes am/pm
	$eeFormat = 'g:i a';

	// Use WordPress's internal timezone handling
	$timezone = new DateTimeZone($eeTimeZone);
	
	// What time is it now in the selected timezone?
	$now = new DateTime('now', wp_timezone()); // Use WordPress timezone settings
	$eeDayToday = $now->format('l'); // Get today's day (Monday, Tuesday, etc.)
	$eeTimeNowObject = $now; // Get the current time as a DateTime object

	// Convert From and To times to DateTime objects using the selected timezone
	$eeFromObject = DateTime::createFromFormat($eeFormat, $eeSCTSC_From, wp_timezone());
	$eeToObject = DateTime::createFromFormat($eeFormat, $eeSCTSC_To, wp_timezone());

	if ($eeToObject <= $eeFromObject) {
		// If To time is less than or equal to From time, show content as a fallback
		return true;
	}

	// Check if today is in the allowed days array
	if (in_array($eeDayToday, $eeSCTSC_Days)) {
		// Check if the current time is within the From and To time range
		if ($eeTimeNowObject > $eeFromObject && $eeTimeNowObject < $eeToObject) {
			return true; // Show the content
		} else {
			return false; // Hide the content (outside the time range)
		}
	}

	return false; // Hide the content (today's day is not in the allowed days)
}





// The Shortcode Code
function eeSCTSC_Shortcode($eeAtts = '', $eeSCTSC_Content = null) {
	
	global $eeSCTSC_Acronym;
	
	$eeSettings = get_option($eeSCTSC_Acronym); // Get the plugin settings
	
	// Set display timezone
	if ($eeSettings['TimeZone']) {
		$timezone = $eeSettings['TimeZone'];
	} else {
		$timezone = wp_timezone_string(); // Fallback to the WordPress timezone setting
	}
	
	$datetime = new DateTime('now', new DateTimeZone($timezone)); // Create a DateTime object with the desired timezone

	
	// Is NOW between From and To, today?
	$eeShow = eeSimpleCertainTimetoShowContent($eeSettings['Days'], $eeSettings['From'], $eeSettings['To']);
	
	if($eeShow AND $eeSettings['Mode'] == 'Hide') { // Flip the result if Mode = Hide
		$eeShow = FALSE;
	}
	
	if($eeShow) {
		
		return $eeSCTSC_Content;
	
	} elseif(!empty($eeSettings['AltContent'])) {
		
		$eeAltContent = html_entity_decode($eeSettings['AltContent']);
		
		return $eeAltContent;
	
	} else {
		return FALSE; // Nothing to do
	}
}
add_shortcode( 'eeSCTSC', 'eeSCTSC_Shortcode' ); // [shortcode], function name



// The Admin Menu
function eeSCTSC_PluginMenu() {
	global $eeSCTSC_Name, $eeSCTSC_Slug, $eeSCTSC_MenuLabel, $eeSCTSC_MenuIcon;
	add_menu_page(
		$eeSCTSC_Name, 
		$eeSCTSC_MenuLabel, 
		'edit_posts', // Page editors and up can see this.
		$eeSCTSC_Slug, 
		'eeSCTSC_AdminPage', // The function called below does the display work.
		$eeSCTSC_MenuIcon); // The icon used for the menu item.
}
add_action( 'admin_menu', 'eeSCTSC_PluginMenu' );



// The Admin Page Display
function eeSCTSC_AdminPage() {
	
	global $wpdb, $eeSCTSC_Name, $eeSCTSC_Acronym, $eeSCTSC_Slug, $eeOutput, $eeSCTSC_Days;
	
	$eeString = ''; // General string holder
	$eeSettings = ''; // Holds the string we store in the database
	$eeError = ''; // Error message
	$eeSettings = array(); // Initialize the settings array
	$eeOutput = '<div id="eeSCTSC_AdminDisplay" class="wrap">'; // Our display output cache
	
	// Are we processing the form?
	if (isset($_POST['eeSCTSC']) && check_admin_referer($eeSCTSC_Slug, $eeSCTSC_Slug . '-nonce')) {
	
		
	
		// Form Validation ------------------------
	
		// Days
		$eeSettings['Days'] = array(); // Store days as an array
		foreach ($eeSCTSC_Days as $eeDay) {
			if (isset($_POST['eeSCTSC_Day_' . $eeDay])) {
				$eeThisDay = sanitize_text_field(wp_unslash($_POST['eeSCTSC_Day_' . $eeDay]));
				if ($eeThisDay) {
					$eeSettings['Days'][] = esc_textarea(substr($eeThisDay, 0, 32)); // Store the selected days
				}
			}
		}
	
		// From Time
		if (isset($_POST['eeSCTSC_From'])) {
			$eeString = sanitize_text_field(wp_unslash($_POST['eeSCTSC_From']));
			if ($eeString) {
				$eeSettings['From'] = esc_attr($eeString); // Store the 'From' time
				$eeSCTSC_From = esc_attr($eeString); // Retain the variable for validation
			}
		}
	
		// To Time
		if (isset($_POST['eeSCTSC_To'])) {
			$eeString = sanitize_text_field(wp_unslash($_POST['eeSCTSC_To']));
			if ($eeString) {
				$eeSettings['To'] = esc_attr($eeString); // Store the 'To' time
				$eeSCTSC_To = esc_attr($eeString); // Retain the variable for validation
			}
		}
	
		// Validate Times
		$eeFormat = 'g:i a'; // Time Format: 6:16 pm - Hour:Minutes am/pm
		$eeFromObject = DateTime::createFromFormat($eeFormat, $eeSCTSC_From);
		$eeToObject = DateTime::createFromFormat($eeFormat, $eeSCTSC_To);
		
		if ($eeToObject <= $eeFromObject) {
			$eeError = 'Duration Error. The FROM time happens after the TO time.';
		}
	
		// Time Zone
		if (isset($_POST['eeSCTSC_TimeZone'])) {
			$eeString = sanitize_text_field(wp_unslash($_POST['eeSCTSC_TimeZone']));
			if ($eeString) {
				$eeSettings['TimeZone'] = esc_attr($eeString); // Store the time zone
			}
		}
	
		// Mode (Show/Hide)
		if (isset($_POST['eeSCTSC_Mode'])) {
			$eeString = sanitize_text_field(wp_unslash($_POST['eeSCTSC_Mode']));
			if ($eeString === 'Show' || $eeString === 'Hide') {
				$eeSettings['Mode'] = esc_attr($eeString); // Store the mode (Show or Hide)
			} else {
				$eeSettings['Mode'] = 'Show'; // Default to 'Show' if invalid
			}
		}
	
		// Alternate Content
		if (isset($_POST['eeSCTSC_AltContent'])) {
			$eeString = sanitize_textarea_field(wp_unslash($_POST['eeSCTSC_AltContent']));
			$eeString = htmlentities(nl2br($eeString), ENT_QUOTES, 'UTF-8');
			$eeSettings['AltContent'] = esc_attr($eeString); // Store alternate content
		}
	
		// All Done
		if ($eeError) {
			$eeOutput .= '<div class="error"><p>' . esc_html($eeError) . '</p></div>';
		} else {
			// Update the settings in the database as an array
			if (update_option($eeSCTSC_Acronym, $eeSettings)) {
				$eeOutput .= '<div class="updated"><p>Your Settings Have Been Updated!</p></div>';
			} else {
				$eeOutput .= '<div class="error"><p>' . esc_html($wpdb->last_error) . '</p></div>';
			}
		}
	} // End POST processor

	
	
	// Begin the page...
	if (empty($eeSettings)) {
		$eeSettings = get_option($eeSCTSC_Acronym); // Get the plugin settings from the database
	}
	
	$eeOutput .= '<div class="wrap" role="region" aria-labelledby="pluginTitle"><h1 id="pluginTitle">' . esc_html($eeSCTSC_Name) . '</h1>';
	
	// Admin Page Tabs
	$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'timer_options';
	
	$eeOutput .= '<h2 class="nav-tab-wrapper" role="tablist">
		<a href="' . esc_url(admin_url('admin.php?page=simple-certain-time-to-show-content&tab=timer_options')) . '" class="nav-tab ' . ($active_tab == 'timer_options' ? 'nav-tab-active' : '') . '" role="tab" aria-selected="' . ($active_tab == 'timer_options' ? 'true' : 'false') . '">' . esc_html__('Timer Options', 'simple-certain-time-to-show-content') . '</a>
		<a href="' . esc_url(admin_url('admin.php?page=simple-certain-time-to-show-content&tab=instructions')) . '" class="nav-tab ' . ($active_tab == 'instructions' ? 'nav-tab-active' : '') . '" role="tab" aria-selected="' . ($active_tab == 'instructions' ? 'true' : 'false') . '">' . esc_html__('Instructions', 'simple-certain-time-to-show-content') . '</a>
		<a href="' . esc_url(admin_url('admin.php?page=simple-certain-time-to-show-content&tab=credits')) . '" class="nav-tab ' . ($active_tab == 'credits' ? 'nav-tab-active' : '') . '" role="tab" aria-selected="' . ($active_tab == 'credits' ? 'true' : 'false') . '">' . esc_html__('Author', 'simple-certain-time-to-show-content') . '</a>
	</h2>';
	
	// Timer Options
	if ($active_tab == 'timer_options') {
		$eeOutput .= '<h3>' . esc_html__('Timer Options', 'simple-certain-time-to-show-content') . '</h3>
			<form action="' . esc_url(admin_url('admin.php?page=' . $eeSCTSC_Slug . '&tab=' . $active_tab)) . '" method="post" aria-labelledby="pluginTitle">
			<input type="hidden" name="eeSCTSC" value="TRUE" />';
		$eeOutput .= wp_nonce_field($eeSCTSC_Slug, $eeSCTSC_Slug . '-nonce', true, false);
		
		$eeOutput .= '<fieldset role="group" aria-labelledby="daysLabel">
			<legend id="daysLabel">' . __('On These Days', 'simple-certain-time-to-show-content') . ':</legend>';
		
		// Loop through the days
		foreach ($eeSCTSC_Days as $eeDay) {
			// Check if the current day is in the 'Days' array
			$eeChecked = (is_array($eeSettings['Days']) && in_array($eeDay, $eeSettings['Days'])) ? ' checked="checked"' : '';
			
			// Generate the checkbox input with aria-describedby
			$eeOutput .= '<label class="eeSCTSC_Days" for="eeSCTSC_' . esc_attr($eeDay) . '">
				' . esc_html($eeDay) . '<br />
				<input type="checkbox" name="eeSCTSC_Day_' . esc_attr($eeDay) . '" value="' . esc_attr($eeDay) . '" id="eeSCTSC_' . esc_attr($eeDay) . '"' . $eeChecked . ' aria-describedby="daysHelp"/>
			</label>';
		}
	
		// Days help text
		$eeOutput .= '<span id="daysHelp" class="screen-reader-text">' . __('Select the days when the content should be displayed', 'simple-certain-time-to-show-content') . '</span>';
		
		// Times
		$eeOutput .= '<label for="eeSCTSC_From">' . __('From', 'simple-certain-time-to-show-content') . '</label>
			<input required id="eeSCTSC_From" type="text" name="eeSCTSC_From" value="' . esc_attr($eeSettings['From']) . '" size="32" aria-describedby="timeHelp" />';
		$eeOutput .= '<label class="eeNoClear" for="eeSCTSC_To">' . __('To', 'simple-certain-time-to-show-content') . '</label>
			<input required id="eeSCTSC_To" type="text" name="eeSCTSC_To" value="' . esc_attr($eeSettings['To']) . '" size="32" aria-describedby="timeHelp" />';
		
		// Time help text
		$eeOutput .= '<span id="timeHelp" class="screen-reader-text">' . __('Enter the time range in the format of hours and minutes (e.g., 2:30 pm)', 'simple-certain-time-to-show-content') . '</span>';
	
		// Time Zone
		$eeOutput .= '<label for="eeSCTSC_TimeZone">' . __('Time Zone', 'simple-certain-time-to-show-content') . '</label>';
		$eeOutput .= '<select required name="eeSCTSC_TimeZone" id="eeSCTSC_TimeZone" aria-describedby="timezoneHelp">' . wp_timezone_choice($eeSettings['TimeZone']) . '</select>';
		$eeOutput .= '<script>jQuery("#eeSCTSC_TimeZone").val("' . esc_attr($eeSettings['TimeZone']) . '");</script>';
	
		// Timezone help text
		$eeOutput .= '<span id="timezoneHelp" class="screen-reader-text">' . __('Select the timezone for the displayed time range.', 'simple-certain-time-to-show-content') . '</span>';
	
		// Mode
		$eeShowSelected = ($eeSettings['Mode'] == 'Show') ? 'selected="selected"' : '';
		$eeHideSelected = ($eeSettings['Mode'] == 'Hide') ? 'selected="selected"' : '';
		$eeOutput .= '<label for="eeSCTSC_Mode">' . __('Mode', 'simple-certain-time-to-show-content') . '</label>';
		$eeOutput .= '<select name="eeSCTSC_Mode" id="eeSCTSC_Mode" aria-describedby="modeHelp">
				<option value="Show" ' . $eeShowSelected . '>' . __('Show', 'simple-certain-time-to-show-content') . '</option>
				<option value="Hide" ' . $eeHideSelected . '>' . __('Hide', 'simple-certain-time-to-show-content') . '</option>
			</select>';
		
		// Mode help text
		$eeOutput .= '<span id="modeHelp" class="screen-reader-text">' . __('Select whether to show or hide the content.', 'simple-certain-time-to-show-content') . '</span>';
	
		// Alternate Content
		$eeAltContent = str_replace('<br />', "\r\n", html_entity_decode($eeSettings['AltContent']));
		$eeOutput .= '<hr />';
		$eeOutput .= '<h5>' . __('Alternate Content (Optional)', 'simple-certain-time-to-show-content') . '</h5>';
		$eeOutput .= '<textarea rows="5" cols="64" name="eeSCTSC_AltContent" id="eeSCTSC_AltContent" class="eeSCTSC_AltContent" aria-labelledby="altContentHelp">' . esc_textarea($eeAltContent) . '</textarea>';
		
		// Submit Button
		$eeOutput .= '<input type="submit" value="' . __('Save', 'simple-certain-time-to-show-content') . '" aria-describedby="submitHelp" />
			<span id="submitHelp" class="screen-reader-text">' . __('Submit to save the settings.', 'simple-certain-time-to-show-content') . '</span>
			<br class="eeClearFix" />
		</fieldset></form>';
	} elseif ($active_tab == 'instructions') {
		// Instructions tab
		$eeOutput .= '<h2>' . __('Instructions', 'simple-certain-time-to-show-content') . '</h2>';
		$eeNonce = wp_create_nonce('eeInclude');
		include(plugin_dir_path(__FILE__) . '/includes/ee-plugin-instructions.php');
	} else {
		// Credits tab
		$eeOutput .= '<h2>' . __('Author Credits', 'simple-certain-time-to-show-content') . '</h2>';
		$eeNonce = wp_create_nonce('eeInclude');
		include(plugin_dir_path(__FILE__) . '/includes/ee-plugin-credits.php');
	}
	
	$eeOutput .= '</div></div>'; // Ends #eeSCTSC_AdminDisplay

	
	// Everything has been escaped, so render the display
	echo $eeOutput;
	
	// echo wp_kses_post( $eeOutput ); Breaks the display

}

?>