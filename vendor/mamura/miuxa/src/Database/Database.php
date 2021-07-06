<?php
namespace miuxa\Database;

use PDO;

class Database
{
    protected $query;
    protected $stmt;
    protected $pdo;
    protected $className;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getDB()
    {
        return $this->pdo;
    }

    public function setQuery($query)
    {
        $this->stmt = $this->pdo->prepare($query);
        return $this;
    }

    public function execute(array $input_parameters = null)
    {
        if (!empty($this->className)) {
            $this->stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->className);
        }

        return $this->stmt->execute($input_parameters);
    }

    public function fetchAll($pdoParam = null)
    {
        $this->execute();
        return $this->stmt->fetchAll($pdoParam);
    }
}
