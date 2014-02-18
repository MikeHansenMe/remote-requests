<?php
/*
Plugin Name: Remote Requests Log
Description: This plugin logs remote requests so you can see where WP core, themes, and plugins are communicating with.
Version: 0.1
Author: Mike Hansen
Author URI: http://mikehansen.me
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function rr_log( $preempt, $args, $url ) {
	$remote = get_option('remote_requests');
	$key = md5( $url );
	if( isset( $remote[ $key ] ) ) {
		$remote[ $key ]['count']++;

	} else {
		$remote[ $key ]['count'] = 1;
		$remote[ $key ]['url'] = $url;
	}
	update_option( 'remote_requests', $remote );
	return $preempt;
}
add_filter( 'pre_http_request', 'rr_log', 10, 3 );


function rr_add_page() {
	add_management_page( 'Remote Requests Log', 'Remote Requests', 'edit_posts', 'remote-request-log', 'rr_page_content' );
}
add_action( 'admin_menu', 'rr_add_page' );

function rr_page_content() {
	$message = array();
	if( isset( $_GET['clear'] ) AND $_GET['clear'] == true ) {
		update_option( 'remote_requests', array() );
		$message[] = array( 'type' => 'updated', 'message' => 'The Remote Requests Log was cleared.' );
	}
	$remote_requests = get_option( 'remote_requests', array() );

	?>
	<div class="wrap">
	<h2>Remote Request Log</h2>
	<h4>Here is a list of URLs that were called from WordPress Core/Themes/Plugins.</h4>
	<?php
	if( count( $message ) > 0 ){
		for ( $i=0;  $i < count( $message );  $i++ ) { 
			echo "<div class='" . $message[ $i ]['type'] . "'><p>" . $message[ $i ]['message'] . "</p></div>";
		}
	}
	?>
	<table class="widefat">
		<thead>
			<tr>
				<th>URL</th>
				<th>Count</th>
			</tr>
		</thead>
	<?php
	foreach ( $remote_requests as $v ) {
		echo "<tr>
				<td>" . $v['url'] . "</td>
				<td>" . $v['count'] . "</td>
			</tr>";
	}
	?>
		<tr>
			<td><p><a href="tools.php?page=remote-request-log&clear=true">Clear Log</a></p></td>
			<td></td>
		</tr>

	</table>
	</div>
	<?php
}