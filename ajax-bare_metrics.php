<?php

// Include necessary libraries
SFApplication::LoadLibrary('dashboard');

// A class to handle report generation
class ReportService {

    // Helper function to sanitize input values
    private static function sanitizeInput($input) {
        return htmlspecialchars(trim($input));
    }

    // Helper function to calculate date difference
    private static function getDateDifference($start_date, $end_date) {
        $datediff = strtotime($end_date) - strtotime($start_date);
        return floor($datediff / (60 * 60 * 24)) - 1; // Total days minus 1
    }

    // Function to get all required data for the report
    public static function getReportData($start_date, $end_date, $id, $filter_type) {
        // Calculate date difference
        $days = self::getDateDifference($start_date, $end_date);

        // Prepare data array
        $data = array(
            'new_customers' => self::getDataByTransType($start_date, $end_date, "sale", $id, $filter_type, "totalcount"),
            'refund_amount' => self::getDataByTransType($start_date, $end_date, "refund", $id, $filter_type),
            'net_revenue' => self::getDataByTransType($start_date, $end_date, "sale", $id, $filter_type),
            'rebill_amount' => self::getDataByTransType($start_date, $end_date, "rebill", $id, $filter_type),
            'cancel_amount' => self::getDataByTransType($start_date, $end_date, "cancelation", $id, $filter_type, "totalcount"),

            'new_customers_chart' => self::getChartDataByTransType($days, $start_date, $end_date, "sale", $id, "totalcount"),
            'refund_amount_chart' => self::getChartDataByTransType($days, $start_date, $end_date, "refund", $id),
            'net_revenue_chart' => self::getChartDataByTransType($days, $start_date, $end_date, "sale", $id),
            'rebill_amount_chart' => self::getChartDataByTransType($days, $start_date, $end_date, "rebill", $id),
            'cancel_amount_chart' => self::getChartDataByTransType($days, $start_date, $end_date, "cancelation", $id, "totalcount"),

            'active_users' => self::getUsersByColumn($start_date, $end_date, $id, "user_created_on", $filter_type),
            'loggedin_users' => self::getUsersByColumn($start_date, $end_date, $id, "extra5", $filter_type),
            'active_campaigns' => self::getActiveCampaign($start_date, $end_date, $id, $filter_type),
            'user_engagement' => self::getUserEngagement($start_date, $end_date, $id, $filter_type),
            'total_bounces' => self::getTotalBounces($start_date, $end_date, $id, $filter_type),

            'active_users_chart' => self::getUsersByColumnChartData($days, $start_date, $end_date, $id, "user_created_on"),
            'loggedin_users_chart' => self::getUsersByColumnChartData($days, $start_date, $end_date, $id, "extra5"),
            'user_engagement_chart' => self::getUserEngagementChart($days, $start_date, $end_date, $id),
            'active_campaigns_chart' => self::getActiveCampaignChart($days, $start_date, $end_date, $id),
            'total_bounces_chart' => self::getTotalBouncesChart($days, $start_date, $end_date, $id),
            'timeago' => self::get_timeago(self::getDateDifference($start_date, $end_date))
        );

        return $data;
    }

    // Helper function to get data based on transaction type
    private static function getDataByTransType($start_date, $end_date, $type, $id, $filter_type, $column = null) {
        // Placeholder for your database logic
        // Example: Use prepared statements or ORM to retrieve data
        // return fetchFromDB($start_date, $end_date, $type, $id, $filter_type, $column);
    }

    // Helper function to get chart data based on transaction type
    private static function getChartDataByTransType($days, $start_date, $end_date, $type, $id, $column = null) {
        // Placeholder for your chart data fetching logic
    }

    // Helper function to get users by column
    private static function getUsersByColumn($start_date, $end_date, $id, $column, $filter_type) {
        // Placeholder for your database logic
    }

    // Helper function to get active campaigns
    private static function getActiveCampaign($start_date, $end_date, $id, $filter_type) {
        // Placeholder for your database logic
    }

    // Helper function to get user engagement
    private static function getUserEngagement($start_date, $end_date, $id, $filter_type) {
        // Placeholder for your database logic
    }

    // Helper function to get total bounces
    private static function getTotalBounces($start_date, $end_date, $id, $filter_type) {
        // Placeholder for your database logic
    }

    // Helper function to format time difference
    private static function get_timeago($datediff) {
        // Placeholder for your time difference logic
    }
}

// Process incoming request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $start_date = date("Y-m-d", strtotime($_POST['start_date']));
    $end_date = date("Y-m-d", strtotime($_POST['end_date']));
    $id = ReportService::sanitizeInput($_POST['id']);
    $filter_type = ReportService::sanitizeInput($_POST['filter_type']);

    // Get the report data
    $report_data = ReportService::getReportData($start_date, $end_date, $id, $filter_type);

    // Return the report data as JSON
    echo json_encode($report_data);
}
