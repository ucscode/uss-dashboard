<?php

class Hierarchy
{
    /**
     * A string that contains SQL Query to traverse child nodes
     *
     * @var string $recursiveChildren
     */
    protected string $recursiveChildren;

    /**
     * A string that contains SQL Query to traverse parent nodes
     *
     * @var string $recursiveParent
     */
    protected string $recursiveParent;

    public function __construct()
    {

        $prefix = DB_TABLE_PREFIX;

        $this->recursiveChildren = "
		
			WITH RECURSIVE cte as ( 
			
				SELECT 
					{$prefix}_users.*, 
					0 as depth 
				FROM {$prefix}_users
				WHERE id = '%1\$s' 
				
					UNION ALL 
					
				SELECT 
					{$prefix}_users.*, 
					depth + 1 
				FROM cte 
				INNER JOIN (
					SELECT * FROM {$prefix}_users 
					WHERE id <> parent
				) AS {$prefix}_users
					ON cte.id = {$prefix}_users.parent 
				
			) SELECT * FROM cte 
				GROUP BY id 
				ORDER BY depth, id
			
		";

        $this->recursiveParent = "
			
			WITH RECURSIVE cte AS (
			
				SELECT 
					{$prefix}_users.*,
					0 as depth
				FROM {$prefix}_users
				WHERE id = '%s'
				
					UNION
					
				SELECT 
					{$prefix}_users.*,
					depth + 1
				FROM (
					SELECT * FROM {$prefix}_users 
					WHERE id <> parent
				) AS {$prefix}_users
				INNER JOIN cte 
					ON cte.parent = {$prefix}_users.id
				WHERE cte.id <> cte.parent
					
			) SELECT * FROM cte
				GROUP BY id
				ORDER BY depth
			
		";

    }


    public function __get($property)
    {
        return property_exists($this, $property) ? $this->{$property} : null;
    }

    /**
     * @ignore
     */
    protected function traversal(string $recursion, ?int $userid, ?string $query = null)
    {

        if(!$userid) {
            return false;
        }

        $SQL = sprintf("
			SELECT * FROM (
				{$recursion}
			) AS hierarchy
			WHERE depth > 0 AND {$query}
		", $userid);

        return Uss::$global['mysqli']->query($SQL);

    }

    /**
     * returns a MYSQLI result containing the descendant of a user
     *
     * @param int $userid The id of the user whose descendant you want to get
     * @param string|null $query A query string after `WHERE` clause which will enable you return limited result based on custom condition
     *
     * @return MYSQLI_RESULT
     */
    public function descendants_of(?int $userid, ?string $query = '1')
    {
        return $this->traversal($this->recursiveChildren, $userid, $query);
    }

    /**
     * returns a MYSQLI result containing the parents of a user
     *
     * @param int $userid The id of the user whose parent you want to get
     * @param string|null $query A query string after `WHERE` clause which will enable you return limited result based on custom condition
     *
     * @return MYSQLI_RESULT
     */
    public function ancestors_of(?int $userid, ?string $query = '1')
    {
        return $this->traversal($this->recursiveParent, $userid, $query);
    }

}
