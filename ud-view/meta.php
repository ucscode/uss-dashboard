<?php
/**
 * - Meta Tags defines meta data about the HTML Document.
 * - Meta Tags provides search engine with information about the website
 */

defined('UDASH_MOD_DIR') or die;

?>	<title><?php echo htmlspecialchars(Uss::$global['title'], ENT_QUOTES | ENT_HTML5); ?></title>
	<link rel='icon' href='<?php echo Uss::$global['icon']; ?>'>
	<meta name='description' content='<?php echo htmlspecialchars(Uss::$global['description'], ENT_QUOTES | ENT_HTML5); ?>'>
	
	<?php

        $opengraph = array(
            'og:title' => 'title',
            'og:image' => 'icon',
            'og:description' => 'description'
        );

foreach($opengraph as $key => $alt) {
    Uss::$global['opengraph'][ $key ] = Uss::$global['opengraph'][ $key ] ?? Uss::$global[ $alt ];
}


/*
    Basic Open Graph Tags For SEO!

    [
        'og:url',
        'og:type',
        'og:locale',
        'og:determiner',
        'og:site_name',
        'og:video',
        'og:audio',
        'fb:app_id'
    ]

*/

foreach(Uss::$global['opengraph'] as $key => $value):

    if(empty($value)) {
        continue;
    } elseif(is_array($value)) {

        foreach($value as $chunk) {
            if(empty($chunk)) {
                continue;
            }
            $chunk = htmlspecialchars($chunk, ENT_QUOTES | ENT_HTML5);
            echo "<meta name='{$key}' content='{$chunk}'>\n\t";
        }

    } else {
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
        echo "<meta name='{$key}' content='{$value}'>\n\t";
    }

endforeach;

?>
	