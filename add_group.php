<?php
$uri = new SFURI();
SFApplication::LoadLibrary('user');
$settings = new SFSettings();

$group_id = "";
$top_heading = "Add User Group";
$cmd = "add_group";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST["cmd"] === "add_group") addGroup();
    if ($_POST["cmd"] === "edit_group") editGroup();
}

if (!empty($_GET['id'])) {
    $group_id = SanitizeAll($_GET['id']);
    $top_heading = "Edit User Group";
    $cmd = "edit_group";
}

$default_email_sender = 1;
$group_detail = getGroupDetailById($group_id);
$default_total_business = 1;

// Define permission keys to reduce repetition
$permission_keys = [
    'reminder_bar', 'beta_features', 'traffic_course', 'email_list_verify',
    'fraud_detection', 'powered', 'split_test', 'one_click_signup', 'tagging',
    'callback_url', 'api_access', 'restrict_participant', 'priority_support',
    'subscribers', 'zapier', 'leaderboard', 'standard_embed', 'popup_embed',
    'popover_embed', 'html_embed', 'fb_login', 'pixel_tracking', 'universal_capture',
    'countdown_timer', 'default_total_business'
];

$permissions = [];

if ($group_detail) {
    foreach ($permission_keys as $key) {
        $permissions[$key] = getPermissions($group_detail->group_id, $key);
    }
}

$paykickstart_plans = getPaykickstartPlans();
?>

<h3 class="page-title"><?= $top_heading; ?></h3>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="icon-home"></i>
            <a href="<?= $uri->GetSiteURL(); ?>">Home</a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li><a href="#"><?= $top_heading; ?></a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <form name="email_settings" id="myProfile" method="post" class="form-horizontal validate">
            <div class="portlet box green">
                <div class="portlet-title"><div class="caption"><?= $top_heading; ?></div></div>
                <div class="portlet-body form">
                    <div class="form-body">
                        <?php
                        $fields = [
                            'Group Name' => ['group_name', $group_detail->group_name ?? '', 'text'],
                            'Total Email Sender Profile' => ['total_email_sender_profiles', $group_detail->total_email_sender_profiles ?? $default_email_sender, 'number'],
                            'Total Custom Domains' => ['total_custom_domain', $group_detail->total_custom_domain ?? 0, 'number'],
                            'Total Business' => ['permission[default_total_business]', $permissions['default_total_business'] ?? $default_total_business, 'number'],
                            'Total Sub-Accounts' => ['total_account', $group_detail->total_account ?? 0, 'number']
                        ];

                        foreach ($fields as $label => [$name, $value, $type]) {
                            echo "
                            <div class='form-group'>
                                <label class='col-md-3 control-label'>{$label}</label>
                                <div class='col-md-4'>
                                    <input type='{$type}' name='{$name}' class='form-control' value='{$value}' required>
                                </div>
                            </div>";
                        }
                        ?>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Current Label</label>
                            <div class="col-md-4">
                                <select class="form-control" name="current_label">
                                    <?php
                                    $labels = ['starter', 'business', 'premium', 'unlimited'];
                                    foreach ($labels as $label) {
                                        $selected = ($group_detail && $group_detail->current_label === $label) ? 'selected' : '';
                                        echo "<option value='$label' $selected>" . ucfirst($label) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Upgrade To Paykickstart Group</label>
                            <div class="col-md-4">
                                <select name="upgrade_group" class="form-control">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($paykickstart_plans as $plan) {
                                        $selected = ($plan->id == ($group_detail->upgrade_group ?? '')) ? 'selected' : '';
                                        echo "<option value='{$plan->id}' $selected>{$plan->title}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Expire Duration</label>
                            <div class="col-md-2">
                                <input type="text" name="exp_time" class="form-control" value="<?= $group_detail->exp_time ?? ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="exp_duration" onchange="Duration();">
                                    <?php
                                    $durations = ['days', 'weeks', 'months', 'year', ''];
                                    foreach ($durations as $dur) {
                                        $selected = ($group_detail->exp_duration ?? '') === $dur ? 'selected' : '';
                                        $label = $dur ? ucfirst($dur) : 'Lifetime';
                                        echo "<option value='$dur' $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Table -->
            <div class="portlet box green admin-user">
                <div class="portlet-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th colspan="2">Manage Permissions</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $checkboxes = [
                                'Beta Features' => 'beta_features',
                                'Traffic Tutorial Popup' => 'traffic_course',
                                'Free Email Verification' => 'email_list_verify',
                                'Remove Powered by Upviral' => 'powered',
                                'Split Testing' => 'split_test',
                                'Tagging Feature' => 'tagging',
                                'Callback URL' => 'callback_url',
                                'API Access' => 'api_access',
                                'Geo-Targeting' => 'restrict_participant',
                                'Priority Support' => 'priority_support',
                                'Zapier Integration' => 'zapier',
                                'Leaderboard' => 'leaderboard',
                                'Standard Embed' => 'standard_embed',
                                'Popup Embed' => 'popup_embed',
                                'Popover Embed' => 'popover_embed',
                                'HTML Embed' => 'html_embed',
                                'FB Login' => 'fb_login',
                                'Pixel Tracking' => 'pixel_tracking',
                                'Universal Capture' => 'universal_capture',
                                'Countdown Timer' => 'countdown_timer'
                            ];

                            foreach ($checkboxes as $label => $key) {
                                $checked = ($permissions[$key] ?? '') === 'yes' ? 'checked' : '';
                                $disabled = $key === 'email_list_verify' ? 'disabled' : '';
                                echo "
                                <tr>
                                    <td>{$label}</td>
                                    <td><input type='checkbox' name='permission[{$key}]' value='yes' $checked $disabled></td>
                                </tr>";
                            }
                            ?>

                            <tr>
                                <td>Max Subscribers</td>
                                <td>
                                    <input type="number" name="permission[subscribers]" class="form-control" value="<?= $permissions['subscribers'] ?? 0; ?>" min="0">
                                    <small>0 = unlimited subscribers</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <input type="hidden" name="group_id" value="<?= $group_id; ?>">
                    <input type="hidden" name="cmd" value="<?= $cmd; ?>">
                    <div class="form-actions">
                        <input type="submit" value="Save" class="btn green">
                        <input type="button" value="Back" class="btn" onclick="history.back();">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function Duration() {
    if (document.querySelector(".exp_duration").value === "") {
        document.querySelector("input[name='exp_time']").value = "";
    }
}
</script>
