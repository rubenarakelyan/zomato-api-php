<?php

// Include the API binding
require_once '../src/ZomatoApi.php';

// Set up a new instance of the API binding with JSON output
$zomatoapi = new ZomatoApi('API_KEY_HERE', 'json');

// Get the city details for London, UK
$london = $zomatoapi->query('locations', array('query' => 'London, UK'));

// Print out the details
header('Content-type: application/json');
echo $london;

?>
