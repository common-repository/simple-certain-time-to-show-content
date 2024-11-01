<?php // Mitchell Bennis | Element Engage, LLC | mitch@elementengage.com
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
if ( ! wp_verify_nonce( $eeNonce, 'eeInclude' ) ) exit('Noncence!'); // Exit if nonce fails
	
// Plugin Contributors Array - Format: Name|URL|DESCRIPTION Example: Thanks to <a href="URL">NAME</a> DESCRIPTION
// Values here are inserted below
$eeContributors = array(
	'Jon Thornton|http://jonthornton.github.com/jquery-timepicker|for the Time Picker'
);

// FIX: Ensure the 'page' index exists, unslash it, and sanitize the input
$eePluginSlug = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';


// The Content
$eeOutput .= '<article>
	<h2>' . __('Plugin Author', 'simple-certain-time-to-show-content') . '</h2>
	<p id="eeCredits">' . __('Plugin by', 'simple-certain-time-to-show-content') . ' <a href="http://mitchellbennis.com/" target="_blank">Mitchell Bennis</a> ' . __('at', 'simple-certain-time-to-show-content') . ' 
	<a href="https://elementengage.com/" target="_blank">Element Engage</a> ' . __('in', 'simple-certain-time-to-show-content') . ' Cokato, Minnesota, USA<br />'; // That's me!

$eeOutput .= __('Contact Me', 'simple-certain-time-to-show-content') . ': <a href="https://elementengage.com/contact-me/">' . __('Feedback or Questions', 'simple-certain-time-to-show-content') . '</a><br />' . 
	__('Please rate this plugin', 'simple-certain-time-to-show-content') . ' <a href="https://wordpress.org/plugins/' . esc_url($eePluginSlug) . '/" target="_blank">' . __('here', 'simple-certain-time-to-show-content') . '</a>.<br /><br />'; // It's a good thing

$eeOutput .= 'Also try my other plugins: <a href="https://wordpress.org/plugins/simple-file-list/">Simple File List</a> and <a href="https://wordpress.org/plugins/basic-front-end-login/">Basic Front-End Login</a>
	<hr />
	<h6>' . __('Contributors', 'simple-certain-time-to-show-content') . '</h6>';

// Contributors Output
foreach( $eeContributors as $eeValue) {
	$eeArray = explode('|', $eeValue);
	// FIX: Escape the output for safety of dynamic content
	$eeOutput .= __('Thanks to', 'simple-certain-time-to-show-content') . ' <a href="' . esc_url($eeArray[1]) . '" target="_blank">' . esc_html($eeArray[0]) . ' </a>' . esc_html($eeArray[2]) . '<br />';
}

$eeOutput .= '</p></article>';

?>