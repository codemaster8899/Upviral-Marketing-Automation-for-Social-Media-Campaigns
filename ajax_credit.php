<?php
SFApplication::LoadLibrary('user');

$db = getDB();

// Initialize POST variables with validation and sanitization
$is_cancelled = (isset($_POST['action']) && $_POST['action'] == "current") ? 0 : 1;
$search_text = isset($_POST['search']['value']) ? SanitizeAll(trim($_POST['search']['value'])) : '';
$type = isset($_POST['type']) ? SanitizeAll($_POST['type']) : '';
$start = isset($_POST['start']) ? (int)SanitizeAll($_POST['start']) : 0;
$perPage = isset($_POST['length']) ? (int)SanitizeAll($_POST['length']) : 10;  // Default to 10 per page

// Columns for sorting and ordering
$columns = array('id','credit','credit_type','credit_reason','created_date');
$order_column = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;  // Default to the first column
$order_dir = isset($_POST['order'][0]['dir']) ? SanitizeAll($_POST['order'][0]['dir']) : 'ASC';

// Get the user ID
$user_id = isset($_POST['user_id']) ? SanitizeAll($_POST['user_id']) : null;

if (!$user_id) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Prepare the SQL query for pagination with safe query execution (using prepared statements)
$sql = "SELECT id, credit, credit_type, credit_reason, created_date
        FROM tbl_credit_history 
        WHERE user_id = ? AND is_cancelled = ? 
        AND (credit_type LIKE ? OR credit_reason LIKE ?)
        ORDER BY {$columns[$order_column]} {$order_dir}
        LIMIT ?, ?";

// Prepare the statement
$stmt = $db->prepare($sql);

// Bind parameters
$searchPattern = "%" . $search_text . "%";
$stmt->bind_param('iissii', $user_id, $is_cancelled, $searchPattern, $searchPattern, $start, $perPage);

// Execute and get results
$stmt->execute();
$results = $stmt->get_result();

// Fetch total count of records for pagination
$sql1 = "SELECT COUNT(*) FROM tbl_credit_history WHERE user_id = ? AND is_cancelled = ? AND (credit_type LIKE ? OR credit_reason LIKE ?)";
$stmt1 = $db->prepare($sql1);
$stmt1->bind_param('iiss', $user_id, $is_cancelled, $searchPattern, $searchPattern);
$stmt1->execute();
$stmt1->bind_result($logs_count);
$stmt1->fetch();

// Initialize data array for response
$data = [];
$total = getTotalUserCredit($user_id, $is_cancelled, $start, $results->fetch_assoc()['id']);

// Loop through results and prepare data
while ($topic = $results->fetch_object()) {
    $data[] = [
        $topic->id,
        $topic->credit,
        $total,
        $topic->credit_type,
        $topic->credit_reason,
        date('F d, Y H:i:s', strtotime($topic->created_date))
    ];
    $total -= $topic->credit;
}

// Return response as JSON
$json_data = [
    "draw" => intval($_POST['draw']),
    "recordsTotal" => intval($logs_count),
    "recordsFiltered" => intval($logs_count),
    "data" => $data
];

echo json_encode($json_data);
?>
