<?php

use Ucscode\Packages\Pairs;
use Ucscode\SQuery\SQuery;

class User implements UserInterface
{
    use PropertyAccessTrait;

    protected array $user = [];
    protected $errors = [];
    protected $userTable = DB_PREFIX . "users";
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

    public function persist(): bool
    {
        $user = $this->getUser($this->user['id']);

        try {

            if(!$user) {

                $user = $this->user;
                unset($user['id']);

                $SQL = (new SQuery())->insert($this->userTable, $this->user);
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
                    ->update($this->userTable, $newValues)
                    ->where('id', $user['id']);

                $result = Uss::instance()->mysqli->query($SQL);

            };

        } catch(\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        };

        return $result;
    }

    public function delete(): ?bool
    {
        $user = $this->getUser($this->user['id']);
        $result = null;
        if($user) {
            $SQL = (new SQuery())->delete()
                ->from($this->userTable)
                ->where('id', $user['id']);
            $result = Uss::instance()->mysqli->query($SQL);
        };
        return $result;
    }

    public function exists(): bool
    {
        return !!$this->getUser($this->user['id']);
    }

    public function getAll(?string $regex = null): array
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->all($this->user['id'], $regex);
        }
    }

    // Obtain User Meta
    public function get(string $key, bool $epoch = false): mixed
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->get($key, $this->user['id'], $epoch);
        };
        return null;
    }

    public function set(string $key, mixed $value): bool
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->set($key, $value, $this->user['id']);
        };
        return false;
    }

    public function remove(string $key): ?bool
    {
        if(!is_null($this->user['id'])) {
            return $this->meta->remove($key, $this->user['id']);
        };
        return null;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function getUser(?int $userId): ?array
    {
        if(is_null($userId)) {
            $userId = -1;
        }
        return Udash::instance()->easyQuery($this->userTable, $userId);
    }

    private function polyFill(?int $userId): bool
    {
        $SQL = (new SQuery())
            ->select('column_name')
            ->from('information_schema.columns')
            ->where('table_schema', DB_NAME)
            ->and('table_name', $this->userTable);

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
                "Trying to {$action} unknown property {$class}::\${$key}; references to unknown column `{$this->userTable}`.`{$key}`"
            );
        }
    }

}
