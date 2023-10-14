<?php


defined("UDASH_AJAX") or die;


Events::instance()->addListener('udash:ajax', function () {

    $status = !($data = []);

    $prefix = DB_TABLE_PREFIX;

    $user = Ud::fetch_assoc("{$prefix}_users", $_POST['email'], 'email');

    if(!$user) {
        $message = "No account is linked to the given email";
    } else {

        $status = Ud::send_pwd_reset_email($user['email']);

        if($status) {

            $message = "Please check your email for direction on how to reset your password";

        } else {
            $message = "Sorry! A reset confirmation link could not be sent to the email";
        }

    };

    $result = [
        "status" => &$status,
        "message" => &$message,
        "data" => &$data,
    ];

    # Module Execution Phase;

    Events::instance()->exec("udash:ajax/reset", $result);

    # Send Output

    Uss::instance()->exit($result['message'], $result['status'], $result['data']);

}, 'ajax-reset');