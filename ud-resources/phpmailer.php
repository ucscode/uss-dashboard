<?php


defined('UDASH_DIR') or die;

/**
 * PHPMailer Classes
 */

$mailers = array(
    "PHPMailer.php",
    "Exception.php",
    "SMTP.php"
);

foreach($mailers as $file) {
    require_once __DIR__ . "/PHPMailer/{$file}";
}
