<?php

(defined('UDASH_DIR') && Uss::$global['user']['id']) or die;

/** The current user */
$user = Uss::$global['user'];

/** Table prefix */
$prefix = DB_TABLE_PREFIX;

/**
 * Get the `usercode` present in the Query string
 * If none exists, then we use that of the current user
 */
$usercode = Uss::query(3);
if(empty($usercode)) {
    $usercode = $user['usercode'];
}

/**
 * Get the owner of the `usercode`
 * We'll be referring to this owner as `prospect`
 */
$prospect = Udash::fetch_assoc("{$prefix}_users", $usercode, "usercode");

/**
 * If there is no such account,
 * reference prospect as the current user
 */
if(!$prospect) {
    $prospect = $user;
}

/**
 * Initialize the hierarchy class;
 */
$hierarchy = new hierarchy();

/**
 * Check if the prospect is the current user
 * Or check if the prospect is a child of the current user
 * If not, prevent the current user from viewing children of the prospect
*/

if($prospect['id'] === $user['id']) {
    $approved = true;
} else {
    $approved = !!$hierarchy->descendants_of($user['id'], "id = '{$prospect['id']}'")->num_rows;
}


/**
 * Let's create a function that will display the rectangular HTML node
 * The HTML node will contain:
 * - avatar
 * - name
 * - and other information if applicable
*/

$value_of = function ($prospect, $info = '') {
    $avatar = Udash::user_avatar($prospect['id']);
    $title = $prospect['username'] ?: $prospect['email'];
    $value = "
		<div data-node='{$prospect['usercode']}''>
			<div class='depth'>{$info}</div>
			<div class='mb-1'><img src='{$avatar}' class='img-fluid'></div>
			<div class='text-truncate title'>{$title}</div>
		</div>
	";
    return $value;
};

/**
 * Get the parent of the prospect
 */
$parent = Udash::fetch_assoc("{$prefix}_users", $prospect['parent']);

/**
 * Now find all descendants of the prospect!
 * Visible depth should be less than 4
 */
$descendants = $hierarchy->descendants_of($prospect['id'], "depth < 4");

/**
 * PREPARE THE TREE
 */
$tree = array();

/**
 * If prospect has parent ( prospect is not the root node )
 * add parent to the list
 * This will enable the user to move upward the tree
 */
if($parent) {
    $tree[] = array(
        "id" => $parent['id'],
        "value" => $value_of($parent, "<i class='bi bi-arrow-up root-icon'></i>"),
        "parent" => null
    );
};

/**
 * Add the prospect to the list
 */
$tree[] = array(
    "id" => $prospect['id'],
    "value" => $value_of($prospect, "<i class='bi bi-check-circle root-icon root'></i>"),
    "parent" => $parent ? $parent['id'] : null
);

/**
 * Add all descendentants to the list
 */
if($descendants->num_rows) {
    /**
     * Loop through the MYSQLI_RESULT
     */
    while($child = $descendants->fetch_assoc()) {
        /**
         * Check if child is a leaf node
         */
        $children = $hierarchy->descendants_of($child['id'])->num_rows;

        /**
         * Add child to the list
         */
        $tree[] = array(
            "id" => $child['id'],
            "value" => $value_of($child, $children ? "<span class='children'>{$children}</span>" : null),
            "parent" => $child['parent']
        );
    };
    // End Loop
};

/**
 * Convert to JSON Format
 */
$jsonTree = addslashes(json_encode($tree));

/**
 * RENDER THE SCRIPT
 */
?>

	<script>
		"use strict";
		let tree = JSON.parse(<?php echo "'{$jsonTree}'"; ?>);
		(new treeData).build(tree, function(result) {
			$('#hierarchy').get(0).appendChild(result);
			$(result).find('a').addClass('node').each(function() {
				let icon = $(this).find('.root-icon.root');
				if( icon.length ) {
					icon.removeClass('root');
					$(this).addClass('root');
				};
			});
			$('#hierarchy').click(function(e) {
				let el = e.target;
				while( el && !el.hasAttribute('data-node') ) el = el.parentElement;
				if( !el ) return;
				window.location.href = `<?php echo Core::url(ROOT_DIR . "/" . $hierFocus); ?>/${el.dataset.node}`;
			});
		});
	</script>

