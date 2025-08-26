<?php
SFApplication::LoadLibrary('user');

// Initialize response array
$user_ary = [
    'success' => true,
    'user_details' => []
];

// Get total users
$totalUser = showTotalUser();

// Check if there are users to process
if (count($totalUser) > 0) {
    // Loop through each user
    foreach ($totalUser as $key => $user) {
        
        // Get user balance and format the amount
        $bal = show_current_balance($user->id);
        $gigley_price = $user->currency === 'HRK' ? "$bal kn" : "$" . $bal;

        // Initialize service verification
        $service = "";
        $verification = apiShowJointAccount($user->id);

        // If user has verification data
        if (count($verification) > 0) {
            foreach ($verification as $verify) {
                // Build service links based on the service type
                switch ($verify->service) {
                    case 'facebook':
                        $service .= '<a href="https://www.facebook.com/' . $verify->verification_data . '" target="_blank">
                                        <i class="fa fa-facebook-square fn_icon" aria-hidden="true"></i></a> ';
                        break;
                    case 'google':
                        $service .= '<a href="https://plus.google.com/' . $verify->verification_data . '" target="_blank">
                                        <i class="fa fa-google-plus-square fn_icon" aria-hidden="true"></i></a> ';
                        break;
                    case 'phone_no':
                        $service .= '<a href="tel:' . $verify->verification_data . '">
                                        <i class="fa fa-phone-square fn_icon" aria-hidden="true"></i></a> ';
                        break;
                }
            }
        }

        // Determine user type (Outsourcer, Worker, or Both)
        if ($user->user_is_outsourcer === "yes" && $user->user_is_doer === "yes") {
            $user_type = "Both";
        } elseif ($user->user_is_outsourcer === "yes") {
            $user_type = "Outsourcer";
        } elseif ($user->user_is_doer === "yes") {
            $user_type = "Worker";
        }

        // Prepare the user data to add to the response array
        $user_ary['user_details'][$key] = [
            $user->id, // User ID
            '<input type="checkbox" name="select_check" class="select_check" value="' . $user->id . '">', // Checkbox
            $user->first_name . ' ' . $user->last_name . "<br>" . $service, // Name and services
            $user->email, // Email
            $user_type, // User type (Outsourcer/Worker/Both)
            $gigley_price, // Balance
            $user->currency, // Currency
            getStarRatingByUserId($user->id), // Star rating (custom function)
            $user->device_type, // Device type
            $user->api_version, // API version
            generateUserActionLinks($user->id) // Action links
        ];
    }
}

// Output the response as JSON
echo json_encode($user_ary);
exit;

/**
 * Generate user action links for various actions (Edit, Delete, View Gigs, etc.)
 */
function generateUserActionLinks($userId)
{
    return '<a href="' . SFURI::SEFURL('index.php?itemtype=user&layout=add_user', array('act' => 'edit', 'id' => $userId)) . '" class="btn mini blue tooltips" data-original-title="Edit">
                <i class="fa fa-pencil"></i>
            </a> 
            <a href="javascript:deletedUser(' . $userId . ')" class="btn mini blue tooltips" data-original-title="Delete">
                <i class="icon-trash"></i>
            </a>  
            <a href="' . SFURI::SEFURL('index.php?itemtype=gigley&layout=index', array('id' => $userId)) . '" class="btn mini blue tooltips" data-original-title="View Gigs">
                <i class="fa fa-book"></i>
            </a>  
            <a href="' . SFURI::SEFURL('index.php?itemtype=user&layout=withdrawl_requests', array('id' => $userId)) . '" class="btn mini blue tooltips" data-original-title="Withdrawl Requests">
                <i class="fa fa-tasks"></i>
            </a>  
            <a href="' . SFURI::SEFURL('index.php?itemtype=user&layout=user_docs', array('id' => $userId)) . '" class="btn mini blue tooltips" data-original-title="Docs">
                <i class="fa fa-file"></i>
            </a>  
            <a href="' . SFURI::SEFURL('index.php?itemtype=user&layout=user_invoices', array('id' => $userId)) . '" class="btn mini blue tooltips" data-original-title="Invoices">
                <i class="fa fa-list-ol"></i>
            </a>';
}
?>
