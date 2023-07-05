<?php


defined('UDASH_DIR') or die;

$reset = call_user_func(function () {

    $rcode = $_GET['v'] ?? null;

    if(!$rcode) {
        return false;
    }

    try {

        $rcode = base64_decode($rcode);
        $rcode = explode(":", $rcode);

        if(count($rcode) < 2) {

            Uss::console('@alert', "Invalid password reset link");

            return false;

        } else {

            $prefix = DB_TABLE_PREFIX;

            $user = Udash::fetch_assoc("{$prefix}_users", $rcode[1], 'email');

            if(!$user) {

                Uss::console('@alert', "The reset link is inappropriate");

                return false;

            } else {

                $epoch = Uss::$global['usermeta']->get('r-code', $user['id'], true);

                $hour = (time() - $epoch) / 3600;

                if($hour > 1) {

                    Uss::console('@alert', "The password reset link has expired. <br> Please try requesting for a new link to proceed");

                    return false;

                } else {

                    $reset_code = Uss::$global['usermeta']->get('r-code', $user['id']);

                    if($reset_code !== $rcode[0]) {

                        Uss::console('@alert', "The reset link could not be approved! <br> Please try requesting for a new link");

                        return false;

                    } else {

                        $passport = base64_encode(str_rot13($reset_code . '-' .  $user['id']));

                        $_SESSION['resetter'] = md5(rand());

                        return $passport;

                    }

                }

            }

        };

    } catch(Exception $e) {

        Uss::console('@alert', "The reset link is forbidden!");

        return false;

    }

});
