<?php

use Ucscode\Packages\Pairs;
use Ucscode\SQuery\SQuery;

class User implements UserInterface
{
    use PropertyAccessTrait;

    public const TABLE = DB_PREFIX . "users";
    public const META_TABLE = DB_PREFIX . "usermeta";

    protected array $user = [];
    protected $errors = [];
    protected ?Pairs $meta;

    public function __construct(?int $userId = null)
    {
        $this->polyFill($userId);
    }

    public function __debugInfo()
    {
        return [
            'user' => $this->user,
            'errors' => $this->errors
        ];
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->user);
    }

    public function &__get(string $key)
    {
        $this->validate($key, 'access');
        return $this->user[$key];
    }

    public function __set(string $key, $value)
    {
        $this->validate($key, 'set');
        $type = gettype($value);
        if(in_array($type, ['object', 'array'])) {
            $class = __CLASS__;
            throw new \Exception("Invalid datatype ({$type}) assigned to {$class}::\${$key}");
        } elseif(strtolower($key) === 'id') {
            throw new \Exception("Permission Denied: User ID cannot be changed");
        };
        if(is_bool($value)) {
            $value = (int)$value;
        };
        $this->user[$key] = $value;
    }

    public function getAvatar(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return $this->meta->get('user:roles') ?? [];
    }

    public function addRole(string|array $role): bool
    {
        return true;
    }

    public function removeRole(string|array $role): bool
    {
        return true;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }

    /**
     * Save user information in database
     *
     * If user does not exist, user is created
     * Else, user is updated
     *
     * @return bool: true if the user was added or updated, false otherwise
     */
    public function persist(): bool
    {
        $user = $this->getUser($this->user['id']);

        try {

            if(!$user) {

                $user = $this->user;
                unset($user['id']);

                $SQL = (new SQuery())->insert(self::TABLE, $this->user);
                $result = Uss::instance()->mysqli->query($SQL);

                if($result) {
                    $this->user['id'] = Uss::instance()->mysqli->insert_id;
                }

            } else {

                $newValues = [];

                // For each updated user data
                foreach($this->user as $key => $value) {

                    // if updated value !== default value
                    if($value !== $user[$key]) {
                        $newValues[$key] = $value;
                    }

                };

                $SQL = (new SQuery())
                    ->update(self::TABLE, $newValues)
                    ->where('id', $user['id']);

                $result = Uss::instance()->mysqli->query($SQL);

            };

        } catch(\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        };

        return $result;
    }

    /**
     * Delete the user record from database
     *
     * @return bool: true if the user was deleted
     */
    public function delete(): ?bool
    {
        $user = $this->getUser($this->user['id']);
        $result = null;
        if($user) {
            $SQL = (new SQuery())->delete()
                ->from(self::TABLE)
                ->where('id', $user['id']);
            $result = Uss::instance()->mysqli->query($SQL);
        };
        return $result;
    }

    /**
     * Check if a user exists
     *
     * This method checks the database for a user with a specific ID
     *
     * @return bool: true if the user exists
     */
    public function exists(): bool
    {
        return !!$this->getUser($this->user['id']);
    }

    /**
     * Get all meta information or pattern associated to the current user
     *
     * @return array: An empty array if no meta information or user does not exist
     */
    public function getAllMeta(?string $regex = null): array
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->all($this->user['id'], $regex);
        }
    }

    /**
     * Obtain a User Meta based on the specified key
     *
     * @param string $key: The meta data to obtain
     * @param bool $epoch: Whether to return the timestamp of when the data was added
     *
     * @return mixed: The meta value; or a timestamp if parameter 2 is set to `true`
     */
    public function getMeta(string $key, bool $epoch = false): mixed
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->get($key, $this->user['id'], $epoch);
        };
        return null;
    }

    /**
     * Set a User Meta for the associated user
     * 
     * @param string $key: The meta data key
     * @param string $value: The meta data value for the specified key
     *
     * @return bool: true if the meta data was added, false otherwise
     */
    public function setMeta(string $key, mixed $value): bool
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->set($key, $value, $this->user['id']);
        };
        return false;
    }

    /**
     * Remove a User Meta based on the specified key
     *
     * @param string $key: The key of the meta data to be removed
     *
     * @return bool: true if the meta data was removed, false otherwise
     */
    public function removeMeta(string $key): ?bool
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->remove($key, $this->user['id']);
        };
        return null;
    }

    /**
     * Get error messages associated to failures in queriing the database
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Encode and save user information to session
     */
    public function saveToSession() {
        if($this->exists()) {
            $userSecret = $this->user['password'] . $this->user['usercode'];
            $var = $this->user['id'] . ":" . hash('sha256', $userSecret);
            $_SESSION['UssUser'] = $var;
        }
    }

    /**
     * Get user information from session, decode it and populate the user instance
     * 
     * This will not populate the user instance if the instance already contains a user information
     *
     * @return bool: true if the session information is found and the User instance was populated, false otherwise
     */
    public function getFromSession(): bool {
        if(empty($this->user['id']) && !empty($_SESSION['UssUser']) && is_string($_SESSION['UssUser'])) {
            $detail = explode(":", $_SESSION['UssUser']);
            if(count($detail) === 2 && is_numeric($detail[0])) {
                $user = $this->getUser($detail[0]);
                if($user) {
                    $hash = hash('sha256', $user['password'] . $user['usercode']);
                    if($hash === $detail[1]) {
                        return !!($this->user = $user);
                    }
                };
            };
        };
        return false;
    }

    /**
     * Private Methods
     * @ignore
     */
    
    private function getUser(?int $userId): ?array
    {
        if(is_null($userId)) {
            $userId = -1;
        }
        return Udash::instance()->fetchData(self::TABLE, $userId);
    }

    private function polyFill(?int $userId): bool
    {
        $SQL = (new SQuery())
            ->select('column_name')
            ->from('information_schema.columns')
            ->where('table_schema', DB_NAME)
            ->and('table_name', self::TABLE);

        $result = Uss::instance()->mysqli->query($SQL);

        if($result->num_rows) {

            $this->user = $this->getUser($userId) ?? [];

            if(empty($this->user)) {

                while($data = $result->fetch_assoc()) {
                    $key = $data['column_name'];
                    if(strtolower($key) === 'id' && !empty($userId)) {
                        $value = $userId;
                    } else {
                        $value = null;
                    };
                    $this->user[$key] = $value;
                };

            }

            $this->meta = Udash::instance()->usermeta;

            return true;

        };

        return false;
    }

    private function validate(string $key, string $action): void
    {
        if(!array_key_exists($key, $this->user)) {
            $class = __CLASS__;
            throw new \Exception(
                "Trying to {$action} unknown property {$class}::\${$key}; references to unknown column `{self::TABLE}`.`{$key}`"
            );
        }
    }

}
