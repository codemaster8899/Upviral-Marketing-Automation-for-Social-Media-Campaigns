<?php
require_once '../lib/statistics.php'; // Load the statistics functions

header('Content-Type: application/json');

// Validate input
if (!isset($_POST['uid'], $_POST['date_range'])) {
    echo json_encode(['error' => 'Missing required input: uid or date_range']);
    exit;
}

// Sanitize inputs
$user_id = filter_var($_POST['uid'], FILTER_SANITIZE_NUMBER_INT);
$date_range = explode(" - ", $_POST['date_range']);

// Validate date range format
if (count($date_range) !== 2) {
    echo json_encode(['error' => 'Invalid date range format.']);
    exit;
}

// Convert to Y-m-d format
$start_date = date("Y-m-d", strtotime($date_range[0]));
$end_date = date("Y-m-d", strtotime($date_range[1]));

// Calculate number of days
$datediff = strtotime($end_date) - strtotime($start_date);
$days = floor($datediff / (60 * 60 * 24));

// Fetch stats
$stats_data = getEmailStatsByUserId($user_id, $days);

// Return response
echo json_encode($stats_data);
