<?php

namespace Engine;

class DataKeeper
{
    private static $errMessage = "";

    private static function connect()
    {
        $dsn = Engine::GetDBInfo(5) . ":dbname=" . Engine::GetDBInfo(3) . ";host=" . Engine::GetDBInfo(0) . ";port=" . Engine::GetDBInfo(4);
        $dblogin = Engine::GetDBInfo(1);
        $dbpass = Engine::GetDBInfo(2);
        try {
            $pdo = new \PDO($dsn, $dblogin, $dbpass);
            return $pdo;
        } catch (\PDOException $pdoExcp) {
            ErrorManager::GenerateError(2);
            ErrorManager::PretendToBeDied(ErrorManager::GetErrorCode(2), $pdoExcp);
        }
        return false;
    }

    public static function getMax($table, $column)
    {
        $pdo = self::connect();
        $preparedQuery = $pdo->prepare("SELECT MAX(`$column`) FROM $table");
        $preparedQuery->execute();
        $result = $preparedQuery->fetch();
        return $result[0];
    }

    public static function exists($table, $column, $content)
    {
        $pdo = self::connect();
        $query = "select exists( select * from `$table` where `$column` = ?) as `exists`";
        $preparedQuery = $pdo->prepare($query);
        $preparedQuery->execute([$content]);
        $result = $preparedQuery->fetch($pdo::FETCH_ASSOC);
        if ($result["exists"] > 0) {
            return true;
        } else {
            return false;
        }

    }

    public static function existsWithConditions(string $table, array $whereArray) : bool
    {
        $whereStatement = "";
        $currentElement = 0;
        $preparedParameters = [];

        foreach ($whereArray as $column => $value) {
            $currentElement++;
            $preparedParameters[] = $value;

            if ($currentElement === count($whereArray)){
                $whereStatement .= "`$column` = ?";
            } else {
                $whereStatement .= "`$column` = ? AND";
            }
        }

        $pdo = self::connect();
        $query = "select (
                    select *
                    from $table
                    where $whereStatement
                    ) as `exists`";
        $preparedQuery = $pdo->prepare($query);
        $preparedQuery->execute($preparedParameters);
        $result = $preparedQuery->fetch(\PDO::FETCH_ASSOC)["exists"] > 0;

        return $result;

    }

    /**Insert into table one record.
     *
     * @param string $table Name of table.
     * @param array $varsArr Associative array where key is column and value is content.
     * @return int
     */
    public static function InsertTo($table, array $varsArr)
    {
        $pdo = self::connect();
        $keys = "";
        $values = "";
        $varsArrToSend = [];
        foreach ($varsArr as $key => $value) {
            $keys .= "`$key`,";
            $values .= "?,";
            $varsArrToSend[] = $value;
        }
        $keys = rtrim($keys, ",");
        $values = rtrim($values, ",");
        $query = "INSERT INTO `$table` ($keys) VALUES ($values)";
        $preparedQuery = $pdo->prepare($query);
        $execute = $preparedQuery->execute($varsArrToSend);
        if ($execute) {
            return $pdo->lastInsertId();
        } else {
            return 0;
        }
    }

    /** Update value in table of fields by filter.
     *
     * @param string $table Table that need to update.
     * @param array $varsArr Associative array with new value of field as key.
     * @param array $whereArr Associative array with values of field as key.
     * @return bool
     */
    public static function Update(string $table, array $varsArr, array $whereArr)
    {
        $pdo = self::connect();
        $keys = "";
        $whereKeys = "";
        $varsArrToSend = [];
        foreach ($varsArr as $key => $value) {
            $keys .= "`$key`=?,";
            $varsArrToSend[] = $value;
        }
        foreach ($whereArr as $whereKey => $whereValue) {
            $whereKeys .= "`$whereKey`=? AND";
            $varsArrToSend[] = $whereValue;
        }

        $keys = rtrim($keys, ", ");
        $whereKeys = rtrim($whereKeys, "AND");
        $query = "UPDATE $table SET $keys WHERE $whereKeys";
        $preparedQuery = $pdo->prepare($query);
        if ($preparedQuery->execute($varsArrToSend))
            return true;
        else {
            ErrorManager::GenerateError(33);
            ErrorManager::PretendToBeDied("$query", new \PDOException($preparedQuery->errorInfo()[2]));
            return false;
        }
    }

    /** Delete record from table.
     * @param string $table Name of table
     * @param array $whereArr Associative array where key is name of column and value is value of column.
     * @return int Count of affected rows.
     */
    public static function Delete($table, array $whereArr)
    {
        $pdo = self::connect();
        $whereStr = "";
        $varsArrToSend = [];
        foreach ($whereArr as $key => $value) {
            $whereStr .= "`$key`=? AND";
            $varsArrToSend[] = $value;
        }
        $whereStr = rtrim($whereStr, "AND");
        $query = "DELETE FROM `$table` WHERE $whereStr";
        $preparedQuery = $pdo->prepare($query);
        $preparedQuery->execute($varsArrToSend);
        return $preparedQuery->rowCount();
    }

    /** Executes "SELECT" query to $table of database and returns
     *  associative array as result.
     *
     * @param string $table Name of table
     * @param array $whatArr Array with name of necessary row.
     * @param array $whereArr
     * @return array Array with results. First record has number 0 in resultative response.
     */
    public static function Get($table, array $whatArr, array $whereArr = null, int $limit = -1)
    {
        $pdo = self::connect();
        $varsArrToSend = [];
        if ($whereArr != null) {
            $whereStr = "";
            foreach ($whereArr as $key => $value) {
                $whereStr .= "`$key`=? AND ";
                $varsArrToSend[] = $value;
            }
        }
        $whatStr = "";
        foreach ($whatArr as $key => $value) {
            if ($value != "*")
                $whatStr .= "`$value`,";
            else {
                $whatStr .= "$value,";
                break;
            }
        }
        $whatStr = rtrim($whatStr, ",");
        $whereStr = rtrim(@$whereStr, " AND ");

        if ($whereArr != null) {
            $query = "SELECT $whatStr FROM `$table` WHERE $whereStr" . (($limit > 0) ? " LIMIT $limit" : "");
            $preparedQuery = $pdo->prepare($query);
            $preparedQuery->execute($varsArrToSend);
        } else {
            $query = "SELECT $whatStr FROM `$table`" . (($limit > 0) ? " LIMIT $limit" : "");
            $preparedQuery = $pdo->prepare($query);
            $preparedQuery->execute();
        }

        return $preparedQuery->fetchAll($pdo::FETCH_ASSOC);
    }

    /** Executes SQL query.
     *
     * @param string $query SQL query
     * @param array|null $whereArr Associative array with values by order.
     * @param bool $multiResponse If true returns all fetches lines.
     * @return array|bool|mixed One fetched line or all fetched lines. If query is invalid returns false.
     */
    public static function MakeQuery($query, array $whereArr = null, bool $multiResponse = false)
    {
        $pdo = self::connect();

        $preparedQuery = $pdo->prepare($query);
        if ($whereArr !== null)
            $result = $preparedQuery->execute($whereArr);
        else
            $result = $preparedQuery->execute();
        if (!$result) {
            ErrorManager::GenerateError(33);
            ErrorManager::PretendToBeDied("Cannot make special SQL query: [" . $preparedQuery->errorInfo()[0] . "] " . $preparedQuery->errorInfo()[2], new \PDOException("Cannot make special SQL query."));
            return false;
        }
        if ($multiResponse)
            return $preparedQuery->fetchAll($pdo::FETCH_ASSOC);
        else
            return $preparedQuery->fetch($pdo::FETCH_ASSOC);
    }
}