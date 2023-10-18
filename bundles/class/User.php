<?php

use Ucscode\SQuery\SQuery;

class User extends AbstractUserRepository
{    
    /**
     * @method getUserMeta
     */
    public function getUserMeta(string $key, bool $epoch = false): mixed
    {
        return self::$usermeta->get($key, $this->getId(), $epoch);
    }

    /**
     * @method setUserMeta
     */
    public function setUserMeta(string $key, mixed $value): bool
    {
        if(!$this->getId()) {
            return self::$usermeta->set($key, $value, $this->getId());
        };
        return false;
    }

    /**
     * @method removeUserMeta
     */
    public function removeUserMeta(string $key): ?bool
    {
        return self::$usermeta->remove($key, $this->getId());
    }

    /**
     * @method getAvatar
     */
    public function getAvatar(): ?string
    {
        $default = Uss::instance()->abspathToUrl(DashboardImmutable::ASSETS_DIR . "/images/user.png");
        $avatar = $this->getUserMeta('user.avatar');
        return $avatar ?? $default;
    }

    /**
     * @method addNotification
     */
    public function addNotification(array $data): int|bool
    {
        if($this->exists()) {
            $uss = Uss::instance();
            $data = $this->filterNotificationData($data);

            if(empty($data['message'])) {
                throw new \Exception(
                    sprintf(
                        "%s(): `message` offset is required (with value of type: string)",
                        __METHOD__ 
                    )
                );
            };
            
            $data['userid'] = $this->getId();
            $data = $uss->sanitize($data, Uss::SANITIZE_SCRIPT_TAGS | Uss::SANITIZE_SQL);

            $SQL = (new SQuery())->insert(self::NOTIFICATION_TABLE, $data);
            $insert = $uss->mysqli->query($SQL);

            if($insert) {
                return $uss->mysqli->insert_id;
            };
        }
        return false;
    }

    /**
     * @method getNotification
     */
    public function getNotifications(
        ?array $filter = null, 
        int $start = 0, 
        int $limit = 20, 
        string $order = 'DESC'
    ): ?array
    {
        $data = [];

        if($this->exists()) {
            $uss = Uss::instance();

            if(empty($filter)) {
                $filter = [];
            };

            $filter['userid'] = $this->getId();
            $filter = $uss->sanitize($filter, Uss::SANITIZE_SCRIPT_TAGS | Uss::SANITIZE_SQL);

            $SQL = (new SQuery())
                ->select()
                ->from(self::NOTIFICATION_TABLE)
                ->where($filter)
                ->orderBy("id " . $uss->sanitize($order))
                ->limit(abs($start), abs($limit));

            $result = $uss->mysqli->query($SQL->getQuery());
            $data = $uss->mysqli_result_to_array($result);
        };

        return $data;
    }

    /**
     * @method updateNotification
     */
    public function updateNotification(array $data, int|array $filter): bool
    {
        if($this->exists()) {
            $uss = Uss::instance();

            $data = $this->filterNotificationData($data);
            $data = $uss->sanitize($data, Uss::SANITIZE_SQL);

            if(is_int($filter)) {
                $filter = ['id' => $filter];
            };

            $filter['userid'] = $this->getId();
            $filter = $uss->sanitize($filter, Uss::SANITIZE_SQL);

            $SQL = (new SQuery())
                ->update(self::NOTIFICATION_TABLE, $data)
                ->where($filter);

            $update = $uss->mysqli->query($SQL);
            return $update;
        };
        return false;
    }

    /**
     * @method removeNotification
     */
    public function removeNotification(int|array $filter): bool
    {
        if($this->exists()) {
            $uss = Uss::instance();

            if(!is_array($filter)) {
                $filter = ['id' => $filter];
            }

            $filter = $uss->sanitize($filter, Uss::SANITIZE_SQL);
            $filter['userid'] = $this->getId();

            $SQL = (new SQuery())
                ->delete(self::NOTIFICATION_TABLE)
                ->where($filter);

            $result = $uss->mysqli->query($SQL);
            return $result;
        };
        return false;
    }

    /**
     * @method countNotification
     */
    public function countNotifications(array $filter = []): int
    {
        if($this->exists()) {
            $uss = Uss::instance();

            $filter['userid'] = $this->getId();
            $filter = $uss->sanitize($filter, Uss::SANITIZE_SQL);

            $SQL = (new SQuery())
                ->select('COUNT(id) AS total')
                ->from(self::NOTIFICATION_TABLE)
                ->where($filter)
                ->groupBy('userid');

            $result = $uss->mysqli->query($SQL)->fetch_assoc();

            return (int)($result ? $result['total'] : 0);
        }
        return 0;
    }

    /**
     * Get all meta information or pattern associated to the current user
     *
     * @return array: An empty array if no meta information or user does not exist
     */
    public function getUserMetaByRegex(?string $regex = null): array
    {
        if($this->getId()) {
            return self::$usermeta->all($this->getId(), $regex);
        };
        return [];
    }

    /**
     * @method filterNotificationData
     */
    private function filterNotificationData(array $originalArray): array
    {
        $keysToExtract = Uss::instance()->getTableColumns(self::NOTIFICATION_TABLE);
        unset($keysToExtract['userid']);
        $filteredArray = array_intersect_key($originalArray, array_flip($keysToExtract));
        $result = array_filter($filteredArray, function ($value) {
            return is_scalar($value) || is_null($value);
        });
        return $result;
    }

}
