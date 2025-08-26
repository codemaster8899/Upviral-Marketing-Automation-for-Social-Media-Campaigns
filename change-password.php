<?php
// Assuming you've already validated the user and are logged in

// Load necessary libraries
SFApplication::LoadLibrary('account');
SFApplication::LoadLibrary('user');

// Function to change password
function changePassword($user_id, $new_password) {
    // Hash the password before saving to the database
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database (assumed to have a function updateUserPassword)
    $result = updateUserPassword($user_id, $hashed_password);

    if ($result) {
        // Set success message and redirect
        SFMESSAGE::SetMessage("Password updated successfully.", 'success');
        redirect(SFURI::SEFURL('index.php?itemtype=account&layout=change-password'));
    } else {
        // Handle error updating password
        SFMESSAGE::SetMessage("Failed to update password. Please try again.", 'error');
    }
}

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirm_pass'])) {
    $user_id = $_SESSION['user_id'];  // Get user ID from session or other source
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_pass'];

    // Validate the passwords match
    if ($password === $confirm_password) {
        changePassword($user_id, $password);
    } else {
        SFMESSAGE::SetMessage("Passwords do not match.", 'error');
    }
}

?>

<!-- HTML form remains largely the same -->

<script>
$("#change_pass_form").validate({
  rules: {
    password: "required",
    confirm_pass: {
      required: true,
      equalTo: "#password"
    }
  }
});
</script>
