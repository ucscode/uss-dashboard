<?php

use Ucscode\SQuery\SQuery;

class Roles
{
    public Uss $uss;

    public function __construct()
    {
        $this->uss = Uss::instance();
    }

    public function getUsersHavingRole(string|array $roles): array
    {
        if(is_string($roles)) {
            $roles = [$roles];
        }
        $roles = array_values($roles);

        $SQL = (new SQuery())
            ->select()
            ->from(DB_PREFIX . "usermeta")
            ->where("_key", "user.roles");

        $phase = [];

        foreach($roles as $key => $role) {
            $phase[] = "JSON_SEARCH(`_value`, 'all', '{$role}', NULL, '$') IS NOT NULL";
        }

        $SQL->raw("AND (\n\t" . implode("\n\tOR ", $phase) . "\n)");
        $result = $this->uss->mysqli->query($SQL);

        $list = [];

        if($result->num_rows) {
            while($data = $result->fetch_assoc()) {
                $list[] = (int)$data['_ref'];
            }
        }

        return array_unique($list);
    }
}
