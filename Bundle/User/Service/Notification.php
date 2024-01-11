<?php

namespace Module\Dashboard\Bundle\User\Service;

use Module\Dashboard\Bundle\User\User;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;
use Uss\Component\Database;

class Notification
{
    const TABLE_NAME = Database::PREFIX . "notifications";
    protected Uss $uss;

    public function __construct(protected User $user)
    {
        $this->uss = Uss::instance();
    }

    /**
     * @method addNotification
     */
    public function add(array $data): int|bool
    {
        if($this->user->isAvailable()) 
        {
            $data = $this->filter($data);

            if(empty($data['message'])) {
                throw new \Exception(
                    sprintf("The 'message' field is required and must have a non-empty string value.")
                );
            }            

            $data['userid'] = $this->user->getId();
            $data = $this->uss->sanitize($data, true);
            
            $squery = (new SQuery())->insert(self::TABLE_NAME, $data);
            $SQL = $squery->build();
            $insert = $this->uss->mysqli->query($SQL);

            return $insert ? $this->uss->mysqli->insert_id : false;
        }
        return false;
    }

    /**
     * @method getNotification
     */
    public function get(null|array|Condition $filter = null, int $offset = 0, int $limit = 20, string $order = 'DESC'): ?array 
    {
        $data = [];
        
        if($this->user->isAvailable()) {
            
            $filter = $this->deriveCondition($filter);
            $filter->add('userid', $this->user->getId());

            $squery = (new SQuery())
                ->select()
                ->from(self::TABLE_NAME)
                ->where($filter)
                ->orderBy("id", $this->uss->sanitize($order, true))
                ->limit(abs($limit))
                ->offset(abs($offset));

            $SQL = $squery->build();
            $result = $this->uss->mysqli->query($SQL);

            $data = $this->uss->mysqliResultToArray($result);
        };

        return $data;
    }

    /**
     * @method updateNotification
     */
    public function update(array $data, int|array|Condition $filter): bool
    {
        if($this->user->isAvailable()) 
        {
            $data = $this->filter($data);
            $data = $this->uss->sanitize($data, true);

            $filter = $this->deriveCondition( is_int($filter) ? ['id' => $filter] : $filter );
            $filter->and('userid', $this->user->getId());

            $squery = (new SQuery())
                ->update(self::TABLE_NAME, $data)
                ->where($filter);

            $SQL = $squery->build();
            $update = $this->uss->mysqli->query($SQL);

            return $update;
        };
        return false;
    }

    /**
     * @method removeNotification
     */
    public function remove(int|array|Condition $filter): bool
    {
        if($this->user->isAvailable()) 
        {
            $filter = $this->deriveCondition( is_int($filter) ? ['id' => $filter] : $filter );
            $filter->and('userid', $this->user->getId());

            $squery = (new SQuery())
                ->delete(self::TABLE_NAME)
                ->where($filter);

            $SQL = $squery->build();
            $result = $this->uss->mysqli->query($SQL);

            return $result;
        };
        return false;
    }

    /**
     * @method countNotification
     */
    public function count(null|array|Condition $filter = null): int
    {
        if($this->user->isAvailable()) 
        {
            $filter = $this->deriveCondition($filter);
            $filter->add('userid', $this->user->getId());

            $squery = (new SQuery())
                ->select('COUNT(id) AS total')
                ->from(self::TABLE_NAME)
                ->where($filter)
                ->groupBy('userid');

            $SQL = $squery->build();
            $result = $this->uss->mysqli->query($SQL);
            $item = $result->fetch_assoc();

            return (int)($item ? $item['total'] : 0);
        }
        return 0;
    }

    /**
     * Remove irrelevant columns from the input data
     */
    protected function filter(array $originalItem): array
    {
        $keysToExtract = Uss::instance()->getTableColumns(self::TABLE_NAME);
        unset($keysToExtract['userid']);
        $filteredArray = array_intersect_key($originalItem, array_flip($keysToExtract));
        $result = array_filter($filteredArray, function ($value) {
            return is_scalar($value) || is_null($value);
        });
        return $result;
    }

    protected function deriveCondition(null|array|Condition $filter): Condition
    {
        $filter ??= new Condition();
        if(is_array($filter)) {
            $preservedFilter = $filter;
            $filter = new Condition();
            foreach($preservedFilter as $key => $value) {
                $filter->add($key, $value);
            }
        };
        return $filter;
    }
}