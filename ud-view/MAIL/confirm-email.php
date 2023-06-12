<?php 
/**
 * The email confirmation message
 *
 * This email is converted into HTML table elements and rendered using X2Client library
 * @see X2Client
 * @author ucscode
*/

defined( 'UDASH_MOD_DIR' ) OR DIE;

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
            <x2:div><x2:img src='<?php echo uss::$global['icon']; ?>' /></x2:div>
            <x2:p>Thank you for signing up on <?php echo uss::$global['title']; ?></x2:p>
            <x2:p>To confirm %{email} as the email address for your account, please click the link below</x2:p>
            <x2:br />
            <x2:p>
                <x2:a href='%{href}' class='buttons'>Verify Your Email</x2:a>
            </x2:p>
            <x2:br>
            <x2:p>This confirmation link will automatically expire in 12 hours. If you didn&apos;t add this email or you need assistance, please contact <?php echo uss::$global['title']; ?> Support.</x2:p>
        </x2:div>
    </x2:body>
</x2:html>

<?php return trim(ob_get_clean()); ?>