<?php
$db = getDB();

// Sanitize and validate inputs
$search_text = isset($_POST['search']['value']) ? SanitizeAll(trim($_POST['search']['value'])) : '';
$type = isset($_POST['type']) ? SanitizeAll($_POST['type']) : '';
$start = isset($_POST['start']) ? (int)SanitizeAll($_POST['start']) : 0;
$perPage = isset($_POST['length']) ? (int)SanitizeAll($_POST['length']) : 10; // default 10 per page

$columns = ['created_date', 'entiry_id', 'entity_type', 'reason', 'ip_address', 'lead_id', 'http_code'];

// Validate order column index
$order_column_index = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$order_column = isset($columns[$order_column_index]) ? $columns[$order_column_index] : $columns[0];

// Validate order direction
$order_dir = isset($_POST['order'][0]['dir']) && in_array(strtoupper($_POST['order'][0]['dir']), ['ASC', 'DESC']) ? strtoupper($_POST['order'][0]['dir']) : 'ASC';

// Validate user_id
$user_id = isset($_POST['user_id']) ? SanitizeAll($_POST['user_id']) : null;
if (!$user_id) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Prepare search patterns for LIKE queries
$searchPattern = '%' . $search_text . '%';
$typePattern = '%' . $type . '%';

// Prepare SQL with placeholders to prevent SQL injection
$sql = "SELECT entiry_id, entity_type, reason, lead_id, http_code, ip_address, created_date
        FROM tbl_log
        WHERE user_id = ?
          AND entity_type LIKE ?
          AND (
            entiry_id LIKE ? 
            OR entity_type LIKE ? 
            OR reason LIKE ? 
            OR ip_address LIKE ?
          )
        ORDER BY $order_column $order_dir
        LIMIT ?, ?";

// Prepare statement
$stmt = $db->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare the SQL statement']);
    exit;
}

// Bind parameters: 'issssiii' = int, string x4, int, int
$stmt->bind_param(
    'issssiii',
    $user_id,
    $typePattern,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $start,
    $perPage
);

// Execute and fetch results
$stmt->execute();
$resultSet = $stmt->get_result();

$data = [];
while ($row = $resultSet->fetch_assoc()) {
    $data[] = [
        $row['entiry_id'],
        $row['entity_type'],
        $row['reason'],
        $row['lead_id'],
        $row['http_code'],
        $row['ip_address'],
        date('F d, Y H:i:s', strtotime($row['created_date']))
    ];
}
$stmt->close();

// Fetch total records count for pagination
$countSql = "SELECT COUNT(*) FROM tbl_log WHERE user_id = ? AND entity_type LIKE ? AND (entiry_id LIKE ? OR entity_type LIKE ? OR reason LIKE ? OR ip_address LIKE ?)";
$countStmt = $db->prepare($countSql);
if ($countStmt === false) {
    echo json_encode(['error' => 'Failed to prepare the count SQL statement']);
    exit;
}
$countStmt->bind_param('isssss', $user_id, $typePattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
$countStmt->execute();
$countStmt->bind_result($logs_count);
$countStmt->fetch();
$countStmt->close();

// Output JSON response
echo json_encode([
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal" => intval($logs_count),
    "recordsFiltered" => intval($logs_count),
    "data" => $data,
]);
