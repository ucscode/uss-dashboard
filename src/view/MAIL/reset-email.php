<?php
/**
 * The reset password message
 *
 * This email is converted into HTML table elements and rendered using X2Client library
 * @see X2Client
 * @author ucscode
*/

defined('UDASH_DIR') or die;

/** Output Buffer */

ob_start();
?>

<x2:html>
    <x2:head>
        <x2:style>
            .button {
				background-color: blue;
				color: #fff;
				padding: 15px;
				min-width: 90px;
            }
            div, p {
				text-align: center;
            }
        </x2:style>
    </x2:head>
    <x2:body>
        <x2:div>
            <x2:div><x2:img src='<?php echo Uss::$global['icon']; ?>'/></x2:div>
            <x2:p>A request has been made to reset your account password on <?php echo Uss::$global['title']; ?>.</x2:p>
            <x2:p>To confirm that the request is really from you, please click the link below</x2:p>
            <x2:br />
            <x2:p>
                <x2:a href='%{href}' class='buttons'>Reset My Password</x2:a>
            </x2:p>
            <x2:br>
            <x2:p>This password reset link will automatically expire in 1 hours. If you didn&apos;t make this request, do not hesitate to contact <?php echo Uss::$global['title']; ?> Support.</x2:p>
        </x2:div>
    </x2:body>
</x2:html>

<?php return trim(ob_get_clean()); ?>