<?php

abstract class AbstractCrudEditManager implements CrudEditInterface
{
    protected const DATASET = [
        'integer' => [
            'TINYINT',
            'SMALLINT',
            'MEDIUMINT',
            'INT',
            'BIGINT'
        ],
        'float' => [
            'DECIMAL',
            'FLOAT',
            'DOUBLE',
            'REAL'
        ],
        'date' => [
            'DATE',
            'DATETIME',
            'TIMESTAMP',
            'TIME'
        ],
        'string' => [
            'CHAR',
            'VARCHAR'
        ],
        'text' => [
            'TINYTEXT',
            'TEXT',
            'MEDIUMTEXT',
            'LONGTEXT'
        ]
    ];

    protected array $fields = [];
    protected string $submitUrl;

    public function __construct(
        protected string $tablename
    ) {
        $this->submitUrl = $_SERVER['REQUEST_URI'];
    }

    /**
     * @method setField
     */
    public function setField(string $name, CrudField $field): CrudEditInterface
    {
        $this->fields[$name] = $field;
        return $this;
    }

    /**
     * @method getField
     */
    public function getField(string $name): ?CrudField
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @method setSubmitUrl
     */
    public function setSubmitUrl(string $url): CrudEditInterface
    {
        $this->submitUrl = $url;
        return $this;
    }

    /**
     * @method getSubmitUrl
     */
    public function getSubmitUrl(): ?string
    {
        return $this->submitUrl;
    }
}
