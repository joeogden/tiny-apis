<?php
// Check if an IP is in a list of subnets or not, provide results as a JSON string

function ip_in_range($ip, $range){
		if(strpos($range, '/' ) == false){
				$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list($range, $netmask) = explode('/', $range, 2);
		
		$range_decimal = ip2long($range);
		$ip_decimal = ip2long($ip);
		$wildcard_decimal = pow(2, (32 - $netmask)) - 1;
		$netmask_decimal = ~ $wildcard_decimal;
		
		return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}

// Add all your subnets here, or load them from a file or database
$networks = array("192.168.0.0/24",
             			"192.168.1.0/24",
             			"192.168.1.0/23");

// Get query IP
if(isset($_GET['ip'])){ $myip = $_GET['ip']; }

// If no query IP use the remote address
if(empty($myip)){ $myip = $_SERVER['REMOTE_ADDR']; }

// Not found by default
$status = false;

// Start a JSON string for the results
$print = '{"results":[';
foreach ($networks as $k => $v) {
	
	 // Get IP bits
   list($net,$mask) = split("/", $v);

   // Check the IP and set found or not
	// Save results to a JSON string for each found row
   if (ip_in_range($myip,$v)) {
		$print .= '{"status":"Found", "ip":"'.$myip.'", "subnet":"'.$v.'"},';
		$status = true;
	}

}

// Add the not found result to the JSON string
if($status == false){
		$print .= '{"status":"Not Found", "ip":"'.$myip.'", "subnet":"None"}';
}else{
// Trim the trailing comma for the found results
		$print = rtrim($print,',');
}
// Last bit of JSON
$print .= ']}';

// Print the output
echo $print;

// Log every query if needed
// file_put_contents('ip_log.txt', "".$myip." checked by ".$_SERVER['REMOTE_ADDR']." on ".date('Y-m-d H:i:s')."\r\n", FILE_APPEND | LOCK_EX);
?>