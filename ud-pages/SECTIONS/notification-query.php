<?php


defined('UDASH_DIR') or die;

/**
 * READY!
 *
 * Let's get our table prefix
 * And also, the current logged in user
 */
$prefix = DB_TABLE_PREFIX;
$userid = Uss::$global['user']['id'];


/**
 * In the array below, we are going to be saving our data
 */
$notify = array();


/**
 * COUNT ALL NOTIFICATIONS!
 *
 * By knowing the total number of notifications, we are able to tell many pages the user can visit
 * Hence, this will enable us decide if the page should have a paginator or not
 */

$SQL = "
	SELECT COUNT(userid) AS _all
	FROM {$prefix}_notifications
	WHERE userid = {$userid} 
		AND hidden = 0
	GROUP BY userid
";

$all = Uss::$global['mysqli']->query($SQL)->fetch_assoc();

/**
 * Now let's save the data with index `size`
 */
$notify['size'] = $all ? $all['_all'] : 0;


/**
 * GET NUMBER OF NOTIFICATIONS TO DISPLAY PER PAGE
 *
 * By identifying the number of notification per page, we can be able to know how many pages we have
 */

$max_list = Udash::config("notifications_per_page") ?? 20;


/**
 * CHECK FOR THE CURRENT VISITED PAGE
 *
 * We have to check the URL to know the current notification that page user has visited
 */

$page_number = (int)($_GET['view'] ?? 1);

if($page_number < 1) {
    $page_number = 1;
} // page number cannot be less than 1


/**
 * DETEMINE THE POINTER
 *
 * Pointer helps us determine the starting point of a page in the SQL table list
 * Assuming there are 10 notifications per page, then:
 *
 * - Pointer of page 1 = 0
 * - Pointer of page 2 = 10
 * - Pointer of page 3 = 20
 * - Pointer of page 4 = 30 etc...
 */

$pointer = (($page_number - 1) * $max_list);


/**
 * CREATE SQL QUERY
 *
 * Now we create the SQL Query
 * This will return a limited list of notications based on the current page
 */

$SQL = SQuery::select("{$prefix}_notifications", "
	userid = {$userid}
	AND hidden = 0
	ORDER BY id DESC
	LIMIT {$pointer}, {$max_list}
");


/** We save the query result with an index `query` */

$notify['query'] = Uss::$global['mysqli']->query($SQL);


/**
 * GET NEXT AND PREVIOUS PAGES
 *
 * @var $QuerySize
 *
 * Tells us what range of SQL rows we have reached.
 * For example: at 10 notification per page:
 *
 * - page 1: $QuerySize = 10
 * - page 2: $QuerySize = 20
 * - page 3: $QuerySize = 30
 *
 * $QuerySize indicates that so far, we have reached "x" number of rows
 *
 */

$QuerySize = $pointer + $max_list;


/**
 * GET NEXT PAGE
 *
 * Check if the number of rows reached is greater or equal to the maximum rows available
 * If the number of rows reached is less than the total available rows,
 * then it signifies there is a "NEXT PAGE"
 *
 * We save the information
 */
$notify['next'] = ($QuerySize >= $notify['size']) ? null : ($page_number + 1);


/**
 * GET PREVIOUS PAGE
 *
 * If the current page number is greater than 1
 * then it signifies there is a "PREVIOUS PAGE"
 *
 * We also save the information
 */
$notify['prev'] = ($page_number > 1) ? ($page_number - 1) : null;


/**
 * Return The Notification Query;
 */
return $notify;
