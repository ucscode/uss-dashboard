<?php

use Ucscode\Packages\Pairs;
use Ucscode\SQuery\SQuery;

class User implements UserInterface
{
    use PropertyAccessTrait;

    private ?array $user = [];

    #[Accessible]
    protected ?Pairs $meta;

    private $userTable = DB_PREFIX . "users";
    private $errors = [];

    public function __construct(?int $userId = null)
    {
        if($this->polyFill()) {
            $this->user = $this->fetchUser($userId) ?: $this->user;
        }
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

        // Fetch default user from database
        $user = $this->fetchUser($this->user['id']);

        // if user does not exist; prepare a new record
        if(!$user) {

            $user = $this->user;
            unset($user['id']);
            $SQL = (new SQuery())->insert($this->userTable, $this->user);

        } else {

            $newValues = [];

            // For each current user data
            foreach($this->user as $key => $value) {

                // if current value !== default value
                if($value !== $user[$key]) {
                    $newValues[$key] = $value;
                }

            };

            $SQL = (new SQuery())
                ->update($this->userTable, $newValues)
                ->where('id', $user['id']);

        };

        try {
            $result = Uss::instance()->mysqli->query($SQL);
        } catch(\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        };

        return $result;
    }

    public function delete(): ?bool
    {
        $user = $this->fetchUser($this->user['id']);
        if(!$user) {
            return null;
        };
        $SQL = (new SQuery())->delete()
            ->from($this->userTable)
            ->where('id', $user['id']);
        $result = Uss::instance()->mysqli->query($SQL);
        return $result;
    }

    public function exists(): bool
    {
        return !!$this->fetchUser($this->user['id']);
    }

    public function get(?string $key = null)
    {
        if(is_null($key)) {
            return $this->user;
        }
        $this->validate($key, __METHOD__);
        return $this->exists() ? ($this->user[$key] ?? null) : false;
    }

    public function set(string $key, mixed $value): void
    {
        $this->validate($key, __METHOD__);
        $this->user[$key] = $value;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function fetchUser(?int $userId): ?array
    {
        if(is_null($userId)) {
            $userId = 'Undefined';
        }
        return Udash::instance()->easyQuery($this->userTable, $userId);
    }

    private function polyFill(): bool
    {
        // build Query
        $SQL = (new SQuery())
            ->select('column_name')
            ->from('information_schema.columns')
            ->where('table_schema', DB_NAME)
            ->and('table_name', $this->userTable);

        // Get result
        $result = Uss::instance()->mysqli->query($SQL->getQuery());

        if($result->num_rows) {
            // Update User
            while($data = $result->fetch_assoc()) {
                $key = $data['column_name'];
                $this->user[$key] = null;
            };
            return true;
        };
        return false;
    }

    private function validate(string $key, string $method): void
    {
        if(!array_key_exists($key, $this->user)) {
            throw new \Exception(
                $method . "(\"{$key}\", ...) Unknown column name `{$this->userTable}`.`{$key}`"
            );
        }
    }
}
