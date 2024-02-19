<?php

use WHMCS\Database\Capsule;

if (!defined('WHMCS'))
	die('You cannot access this file directly.');

define("MODULENAME", 'ip_logs');

// Inserts User ID, Last IP Address and Last Login Datetime to mod_ip_logs. Executes on client login.
add_hook('UserLogin', 1, function ($vars) {

	$userid = $vars['user']['id'];
	$ip = $vars['user']['last_ip'];
	$datetime = $vars['user']['last_login'];

	try {
		Capsule::table('mod_ip_logs')->insert(
			['user_id' => $userid, 'ip' => $ip, 'login_datetime' => $datetime]
		);
	} catch (Exception $e) {
		return "An error has occurred";
	}
});


// Delete client IPs when deleting the client account
add_hook('PreDeleteClient', 1, function($vars) {

	try {
		Capsule::table('mod_ip_logs')->where('user_id', '=', $vars)->delete();
	} catch (Exception $e) {
		return "An error has occurred";
	}

});


// Display client ip history on clientssummary
add_hook('AdminAreaClientSummaryPage', 1, function ($vars) {

	$userId = $vars['userid'];

	$ipTable = Capsule::table('mod_ip_logs')->select('ip', 'login_datetime')->where('user_id', '=', $userId)->get();

	$output = '<div class="clientssummarybox">
	<div class="title">IP History</div>
	<div class="table-responsive" style="height: 150px; margin-bottom: 2%;">
	<table class="clientssummarystats" cellspacing="0" cellpadding="2">
	<thead>
		<tr>
			<th>Date</th>
			<th>IP Address</th>
		</tr>
	</thead>
	<tbody id="ipTable">';

	// Adds class="altrow" to every other row
	$flipflop = false;
	foreach ($ipTable as $i) {
		if ($flipflop) {
			$output .= '<tr class="altrow"><td>' . $i->login_datetime . '</td><td>' . $i->ip . '</td></tr>';
			$flipflop = false;
		} else {
			$output .= '<tr><td>' . $i->login_datetime . '</td><td>' . $i->ip . '</td></tr>';
			$flipflop = true;
		}
	}

	$output .= '</tbody>
	</table>
	</div>
	<input class="form-control" type="search" placeholder="Search" aria-label="Search" id="searchInput">
	</div>';

	// First jQuery script adds the table to the UI
	// Second jQuery script makes the search bar for the IP table work
	return  '<script>

	jQuery(document).ready(function() {
		jQuery("#clientsummarycontainer .row .col-lg-3").eq(2).append("' . preg_replace("/\r|\n/", "", str_replace('"', '\"', $output)) . '");
	});

	jQuery(document).ready(function(){
		jQuery("#searchInput").on("keyup", function() {
			var value = jQuery(this).val().toLowerCase();
			jQuery("#ipTable tr").filter(function() {
				jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1)
			});
		});
	});

	</script>';
});