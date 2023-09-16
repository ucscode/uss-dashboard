<?php

/*
    Roles are important!
    But very complicated :-(

    Anyway, the roles class is a global class for the dashboard which contains a list
    of available roles, permissions and level of authority


*/

class Roles
{
    protected static ?string $error = null;

    private static ?array $user;

    private static int $priorityBreak = 100;

    private static array $roles = array(

        /*

            Role consist of user title, priority and permissions

            Title: The name of the role
            Priority: The importance of the role ( The level of authority )
            Permission: List of what the role can do

        */

        'member' => array(
            'priority' => 1,
            'permissions' => array(
                'view-dashboard',
                'update-profile'
            )
        ),

        'super-admin' => array(
            'priority' => 100,
            'permissions' => array(
                'view-cpanel',
                'manage-users',
                'update-settings'
            )
        )

    );


    /*
        - Add a new role to the existing roles!

        - That is, declare a new available role
    */

    public static function add(string $title, int $priority, array $permissions = [])
    {

        $permissions = self::probe($permissions, __FUNCTION__);

        if(self::get($title)) {
            return false;
        }

        self::$roles[ trim($title) ] = array(
            "priority" => self::adjustPriority($priority),
            "permissions" => $permissions
        );

        return true;

    }


    /*
        - Remove a role from the existing roles!
    */

    public static function remove(string $title)
    {

        if(self::get($title)) {
            unset(self::$roles[ $title ]);
        }

        return true;

    }

    private static function adjustPriority(int $priority)
    {

        /*
            - If you know what you're doing, you wouldn't try setting an authority above the greatest authority
            - Which of course, is the `super-admin`
            - So set a perfect priority or you'll be forcefully taken down! Hmmm... :-/
        */

        if($priority > self::$priorityBreak) {
            $priority = (self::$priorityBreak - 1);
        }

        return $priority;

    }

    /*
        Validate Permission Before Using it!
        This is a private method!
    */

    private static function probe($permission)
    {

        $type = getType($permission);

        if(!in_array($type, ['string', 'array'])) {
            return [];
        }

        if($type == 'string') {
            $permission = [ $permission ];
        }

        $permission = array_unique(array_filter(array_values($permission)));

        return $permission;

    }


    /*
        - Get a unit or list of all roles
        -----------------------------------
    */

    public static function get(?string $title = null, ?string $key = null)
    {

        // If parameter 1 is null, return all roles
        if(is_null($title)) {
            return self::$roles;
        }

        $role = self::$roles[ trim($title) ] ?? null;

        if($role) {

            // If parementer 2 is not given, return the role

            if(is_null($key)) {
                return $role;
            }

            // If parameter 2 exists, return the attribute of the role

            return $role[ $key ] ?? null;

        };

    }


    /*
        Set a new permission to an existing role;
    */

    public static function setPermission(string $title, $permissions /* string or array */)
    {

        $permissions = self::probe($permissions);

        /*
            If no such role exists or permissions is empty
            Return false
        */

        $role = self::get($title);

        if(empty($role) || !$permissions) {
            return false;
        }

        $permissions = array_unique(array_merge($role['permissions'], $permissions));

        self::$roles[ $title ]['permissions'] = array_values($permissions);

        return true;

    }


    /*
        Remove a permissions from an existing role;
    */

    public static function removePermission(string $title, $permissions)
    {

        $permissions = self::probe($permissions);

        $role = self::get($title);

        if(!empty($role) && $permissions) {
            self::$roles[ $title ]['permissions'] = array_values(array_diff($role['permissions'], $permissions));
            return true;
        };

        return false;

    }


    /*
        Change the status of the priority!
        -----------------------------------
    */

    public static function updatePriority(string $title, int $index)
    {
        if(self::get($title)) {
            self::$roles[ $title ]['priority'] = self::adjustPriority($index);
            return true;
        };
        return false;
    }


    /*
        - Now Let's link above method to users

        - The roles, permission and authority of a user can easily be retrieved by userid. For Example:

            `Roles::user(1)::maxPriority()`


        However, every call to a user dedicated method will reset the user variable.

            `Roles::user(1)` // gets the user;

            `Roles::maxPriority()` // gets the maxPriority & clears the user;

            `Roles::hasRole('member')` // false; No user available


        The best way is to always call involved the user by re-adding it

            `Roles::user(1)::maxPermission()` // gets the user maxPriority & clears the user;

            `Roles::user(1)::hasRole('members') // returns true & clears the user;


    */

    public static function user(?int $userid = null)
    {

        // reset user to null;

        if($userid) {

            self::$user = Udash::fetch_assoc(DB_TABLE_PREFIX . "_users", $userid);

            if(self::$user) {
                self::$user['id'] = abs(self::$user['id']);
            }

        } else {
            self::clear_user();
        }

        return __CLASS__;

    }


    private static function clear_user(?string $error = null)
    {
        self::$error = $error;
        return !!(self::$user = null);
    }


    /*
        assign one or more roles to a user
        ----------------------------------
    */

    public static function assign($roles)
    {

        if(empty(self::$user)) {
            return self::clear_user("Cannot assign role(s) to user of type NULL");
        }

        // Now we have to ensure that the roles being assigned is registered!

        $roles = self::probe($roles);
        $roles = array_intersect(array_keys(self::$roles), $roles);

        if(empty($roles)) {
            return self::clear_user("Assigned role(s) does not match any existing registered role");
        }

        // Now we'll get unique part of the roles and assign it to users

        $new_roles = array_values(array_unique(array_merge(self::get_user_roles(), $roles)));

        $assigned = Uss::$global['usermeta']->set('roles', $new_roles, self::$user['id']);

        self::clear_user($assigned ? null : "Database Error: Failed to assign role(s)");

        return $assigned;

    }


    /*
        Remove a role from a user!
    */

    public static function unassign($roles)
    {

        if(empty(self::$user)) {
            return self::clear_user("Cannot unassign role(s) for user of type NULL");
        }

        $roles = self::probe($roles);
        $new_roles = array_values(array_diff(self::get_user_roles(), $roles));

        $unassigned = Uss::$global['usermeta']->set('roles', $new_roles, self::$user['id']);

        self::clear_user($unassigned ? null : "Database Error: Failed to unassign role(s)");

        return $unassigned;

    }


    /*

    */

    public static function get_user_roles()
    {
        if(self::$user) {
            $roles = Uss::$global['usermeta']->get('roles', self::$user['id']) ?? [];
        } else {
            $roles = [];
        }
        return array_values($roles);
    }


    /*

    */

    public static function hasPermission(string $permission)
    {

        if(empty(self::$user)) {
            return self::clear_user("Cannot detect permission for user of type NULL");
        }

        $result = false;

        foreach(self::get_user_roles() as $title) {
            $offset = self::get($title, 'permissions');
            if($offset && in_array($permission, $offset)):
                $result = true;
                break;
            endif;
        };

        return $result && !self::clear_user();

    }


    /*

    */

    public static function is($role)
    {
        return self::hasRole($role);
    }

    public static function hasRole($role)
    {
        $result = in_array($role, self::get_user_roles());
        self::clear_user();
        return $result;
    }


    /*

    */

    public static function maxPriority(bool $name = false)
    {

        if(empty(self::$user)) {
            self::$error = "Cannot detect priority for user of type NULL";
            return self::clear_user();
        };

        $roles = array_values(array_intersect(array_keys(self::$roles), self::get_user_roles()));
        $priorities = [];

        foreach($roles as $title) {
            $priorities[ $title ] = self::get($title, 'priority');
        };

        self::clear_user();

        $result = empty($priorities) ? 0 : max($priorities);

        if($name) {
            $result = array_search($result, $priorities);
            if(!$result) {
                $result = null;
            }
        };

        return $result;

    }


    /*

    */

    public static function authority(bool $name = false)
    {

        return self::maxPriority($name);

    }


    /*

    */

    public static function getError()
    {
        return self::$error;
    }


    /*

    */

    public static function get_assigned_users(string $role, bool $complete = false)
    {

        $role = Uss::$global['mysqli']->real_escape_string($role);

        $prefix = DB_TABLE_PREFIX;

        $SQL = "
			SELECT {$prefix}_users.*
			FROM {$prefix}_usermeta
			INNER JOIN {$prefix}_users
				ON {$prefix}_usermeta._ref = {$prefix}_users.id
			WHERE 
				{$prefix}_usermeta._key = 'roles'
				AND {$prefix}_usermeta._value REGEXP '{$role}'
		";

        $data = array();

        $results = Uss::$global['mysqli']->query($SQL);

        if($results->num_rows) {
            while($user = $results->fetch_assoc()) {
                $data[] = $complete ? $user : $user['id'];
            };
        };

        return $data;

    }


    /*

    */

    public static function get_permitted_users(string $permission, bool $complete = false)
    {

        // get all roles that have the permission;

        $users = array();

        foreach(self::$roles as $role => $value) {
            if(in_array(trim($permission), $value['permissions'])) {
                $results = self::get_assigned_users($role, $complete);
                foreach($results as $data) {
                    $key = is_array($data) ? $data['id'] : $data;
                    $users[ $key ] = $data;
                };
            }
        };

        return array_values($users);

    }

};

/*

    SAMPLE:
    ------------------------------
    -------------------------------
    ---------------------------------


    # To add a new Role!
    ---------------------

    Roles::add( 'administrator', 100, array(
        "update-profile",
        "manage-account"
    ));



    # To Remove Role!
    -----------------

    Roles::remove( 'administrator' );



    # To Add Permission
    -------------------

    Roles::setPermission( 'administrator', "manage-users" );



    # To Remove Permission
    -----------------------

    Roles::removePermission( 'administrator', 'manage-users' );



    # To Change Priority
    ---------------------

    Roles::updatePriority( 'administrator', 200 );



    # To Get Roles
    ---------------------

    Roles::get( 'administrator' );



    # To Get Roles Attribute
    ---------------------

    Roles::get( 'administrator', 'permissions' );
    Roles::get( 'administrator', 'priority' );



    # To Link A User
    -----------------

    Roles::user( 2 );



    # To Assign Role To A User
    ---------------------------

    Roles::user( 2 )::assign( 'administrator' );



    # To Un-Assign Role From A User
    --------------------------------

    Roles::user( 2 )::unassign( 'administrator' );



    # To Check If A User Has Role
    ------------------------------

    Roles::user( 2 )::hasRole( 'administrator' );
    OR
    Roles::user( 2 )::is( 'administrator' );



    # To Check If A User Has Permission
    ------------------------------------

    Roles::user( 2 )::hasPermission( 'manage-account' );



    # To Get A User's Maximum Priority
    ------------------------------------

    Roles::user( 2 )::maxPriority();
    OR
    Roles::user( 2 )::authority();



    # To Get All roles of a user
    ------------------------------------

    Roles::user( 2 )::get_user_roles();



    # To get a list of users with a particular role
    -------------------------------------------------

    Roles::get_assigned_users( 'members' ); // Returns only the users ID

    Roles::get_assigned_users( 'members', true ); // Returns the complete user detail



    # To get a list of users with a particular permission
    ------------------------------------------------------

    Roles::get_permitted_users( 'manage-account' ); // Returns only the user ID

    Roles::get_permitted_users( 'manage-account', true ); // Returns the complete user detail;


*/
