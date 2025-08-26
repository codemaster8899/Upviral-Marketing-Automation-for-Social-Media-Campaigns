<?php
SFApplication::LoadLibrary('user');

if (isset($_POST['action']) && $_POST['action'] === 'get_stats') {
    $db = getDB();
    
    $userid = isset($_POST['userid']) ? SanitizeAll($_POST['userid']) : null;
    $type = isset($_POST['type']) ? SanitizeAll($_POST['type']) : null;

    if (!$userid || !$type) {
        echo "0|0|0"; // Early exit if required params missing
        exit;
    }

    // Determine date range
    if ($type === 'custom') {
        // Validate and sanitize dates
        $sdate_raw = isset($_POST['sdate']) ? SanitizeAll($_POST['sdate']) : '';
        $edate_raw = isset($_POST['edate']) ? SanitizeAll($_POST['edate']) : '';

        $sdate = DateTime::createFromFormat('Y-m-d', $sdate_raw);
        $edate = DateTime::createFromFormat('Y-m-d', $edate_raw);

        if (!$sdate || !$edate) {
            // If dates invalid or not in Y-m-d format, fallback to defaults
            $edate = new DateTime();
            $sdate = (clone $edate)->modify('-7 days'); // Default to last 7 days
        }

        $sdate = $sdate->format('Y-m-d');
        $edate = $edate->format('Y-m-d');
    } else {
        // Assume $type is an integer number of days
        $days = filter_var($type, FILTER_VALIDATE_INT);
        if ($days === false || $days < 0) {
            $days = 7; // default to last 7 days if invalid
        }
        $edate = date('Y-m-d');
        $sdate = date('Y-m-d', strtotime("-$days days"));
    }

    $result = getTopMostStatsByUser($sdate, $edate, $userid);

    $total_mail_sent = 0;
    $bounce_count = 0;
    $ratio = 0;

    if ($result) {
        $total_mail_sent = (int) $result->total_mail_sent;
        $bounce_count = (int) $result->bounce_count;
        
        if ($bounce_count > 0) {
            $ratio = round($total_mail_sent / $bounce_count, 2);
        }
    }

    echo "{$total_mail_sent}|{$bounce_count}|{$ratio}";
}
?>
