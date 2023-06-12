<?php 
/**
 * - Meta Tags defines meta data about the HTML Document.
 * - Meta Tags provides search engine with information about the website
 */

defined( 'UDASH_MOD_DIR' ) OR DIE; 

?>	<title><?php echo htmlspecialchars( uss::$global['title'], ENT_QUOTES | ENT_HTML5 ); ?></title>
	<link rel='icon' href='<?php echo uss::$global['icon']; ?>'>
	<meta name='description' content='<?php echo htmlspecialchars( uss::$global['description'], ENT_QUOTES | ENT_HTML5 ); ?>'>
	
	<?php
		
		$opengraph = array(
			'og:title' => 'title',
			'og:image' => 'icon',
			'og:description' => 'description'
		);
		
		foreach( $opengraph as $key => $alt ) 
			uss::$global['opengraph'][ $key ] = uss::$global['opengraph'][ $key ] ?? uss::$global[ $alt ];
			
		
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
		
		foreach( uss::$global['opengraph'] as $key => $value ):
		
			if( empty($value) ) continue;
			
			else if( is_array($value) ) {
				
				foreach( $value as $chunk ) {
					if( empty($chunk) ) continue;
					$chunk = htmlspecialchars( $chunk, ENT_QUOTES | ENT_HTML5 );
					echo "<meta name='{$key}' content='{$chunk}'>\n\t";
				}
				
			} else {
				$value = htmlspecialchars( $value, ENT_QUOTES | ENT_HTML5 );
				echo "<meta name='{$key}' content='{$value}'>\n\t";
			}
			
		endforeach; 
		
	?>
	