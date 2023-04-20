<?php

namespace Engine;

use Exceptions\Exemplars\InSqlQueryError;
use Exceptions\Exemplars\NotConnectedToDatabaseError;

class DataKeeper
{
    private static $errMessage       = "";
    private static $connection;
    private static $transactionUsing = false;

    private static function connect() {
        $dsn     = Engine::GetDBInfo(5) .
                   ":dbname=" . Engine::GetDBInfo(3) .
                   ";host=" . Engine::GetDBInfo(0) .
                   ";port=" . Engine::GetDBInfo(4) .
                   ";charset=utf8";
        $dblogin = Engine::GetDBInfo(1);
        $dbpass  = Engine::GetDBInfo(2);
        try {
            self::$connection = new \PDO($dsn, $dblogin, $dbpass);
            return true;
        } catch (\PDOException $pdoExcp) {
            throw new NotConnectedToDatabaseError("Cannot connect to database: {self::$pdoExcp->getMessage()}");
        }

        return false;
    }

    public static function getMax($table, $column) {
        self::connect();

        $preparedQuery = self::$connection->prepare("SELECT MAX(`$column`) FROM $table");
        $preparedQuery->execute();
        $result = $preparedQuery->fetch();
        return $result[0];
    }

    public static function exists($table, $column, $content) {
        self::connect();

        $query         = "select exists( select * from `$table` where `$column` = ?) as `exists`";
        $preparedQuery = self::$connection->prepare($query);
        $preparedQuery->execute([$content]);
        $result = $preparedQuery->fetch(self::$connection::FETCH_ASSOC);
        if ($result["exists"] > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function existsWithConditions(string $table, array $whereArray) : bool {
        self::connect();

        $whereStatement     = "";
        $currentElement     = 0;
        $preparedParameters = [];

        foreach ($whereArray as $column => $value) {
            $currentElement++;
            $preparedParameters[] = $value;

            if ($currentElement === count($whereArray)) {
                $whereStatement .= "`$column` = ?";
            }
            else {
                $whereStatement .= "`$column` = ? AND";
            }
        }


        $query         = "select (
                    select *
                    from $table
                    where $whereStatement
                    ) as `exists`";
        $preparedQuery = self::$connection->prepare($query);
        $preparedQuery->execute($preparedParameters);
        $result = $preparedQuery->fetch(\PDO::FETCH_ASSOC)["exists"] > 0;

        return $result;

    }

    /**Insert into table one record.
     *
     * @param string $table   Name of table.
     * @param array  $varsArr Associative array where key is column and value is content.
     * @return int
     */
    public static function InsertTo($table, array $varsArr) {
        self::connect();

        $keys          = "";
        $values        = "";
        $varsArrToSend = [];
        foreach ($varsArr as $key => $value) {
            $keys            .= "`$key`,";
            $values          .= "?,";
            $varsArrToSend[] = $value;
        }
        $keys          = rtrim($keys, ",");
        $values        = rtrim($values, ",");
        $query         = "INSERT INTO `$table` ($keys) VALUES ($values)";
        $preparedQuery = self::$connection->prepare($query);
        $execute       = $preparedQuery->execute($varsArrToSend);
        if ($execute) {
            return self::$connection->lastInsertId();
        }
        else {
            return 0;
        }
    }

    /** Update value in table of fields by filter.
     *
     * @param string $table    Table that need to update.
     * @param array  $varsArr  Associative array with new value of field as key.
     * @param array  $whereArr Associative array with values of field as key.
     * @return bool
     */
    public static function Update(string $table, array $varsArr, array $whereArr) {
        self::connect();
        $keys          = "";
        $whereKeys     = "";
        $varsArrToSend = [];
        foreach ($varsArr as $key => $value) {
            $keys            .= "`$key`=?,";
            $varsArrToSend[] = $value;
        }
        foreach ($whereArr as $whereKey => $whereValue) {
            $whereKeys       .= "`$whereKey`=? AND";
            $varsArrToSend[] = $whereValue;
        }

        $keys          = rtrim($keys, ", ");
        $whereKeys     = rtrim($whereKeys, "AND");
        $query         = "UPDATE $table SET $keys WHERE $whereKeys";
        $preparedQuery = self::$connection->prepare($query);
        if ($preparedQuery->execute($varsArrToSend))
            return true;
        else {
            throw new InSqlQueryError($preparedQuery->errorInfo()[2]);
            return false;
        }
    }

    /** Delete record from table.
     *
     * @param string $table    Name of table
     * @param array  $whereArr Associative array where key is name of column and value is value of column.
     * @return int Count of affected rows.
     */
    public static function Delete($table, array $whereArr) {
        self::connect();
        $whereStr      = "";
        $varsArrToSend = [];

        if (self::$transactionUsing) {
            self::$connection->beginTransaction();
        }

        foreach ($whereArr as $key => $value) {
            $whereStr        .= "`$key`=? AND";
            $varsArrToSend[] = $value;
        }
        $whereStr      = rtrim($whereStr, "AND");
        $query         = "DELETE FROM `$table` WHERE $whereStr";
        $preparedQuery = self::$connection->prepare($query);
        $preparedQuery->execute($varsArrToSend);
        return $preparedQuery->rowCount();
    }

    /** Executes "SELECT" query to $table of database and returns
     *  associative array as result.
     *
     * @param string $table   Name of table
     * @param array  $whatArr Array with name of necessary row.
     * @param array  $whereArr
     * @return array Array with results. First record has number 0 in resultative response.
     */
    public static function Get($table, array $whatArr, array $whereArr = null, int $limit = -1) {
        self::connect();
        $varsArrToSend = [];

        if (!empty($whatArr)) {
            $whatStr = '';
            foreach ($whatArr as $key => $value) {
                if ($value != "*")
                    $whatStr .= "`$value`,";
                else {
                    $whatStr .= "$value,";
                    break;
                }
            }
            $whatStr = rtrim($whatStr, ",");
        }
        else {
            $whatStr = '*';
        }
        if (!empty($whereArr)) {
            $whereStr = '';
            foreach ($whereArr as $key => $value) {
                $whereStr        .= "`$key`=? AND ";
                $varsArrToSend[] = $value;
            }
            $whereStr = rtrim($whereStr, " AND ");
        }

        if (!empty($whereArr)) {
            $query         = "SELECT $whatStr FROM `$table` WHERE $whereStr" . (($limit > 0) ? " LIMIT $limit" : "");
            $preparedQuery = self::$connection->prepare($query);
            $preparedQuery->execute($varsArrToSend);
        }
        else {
            $query         = "SELECT $whatStr FROM `$table`" . (($limit > 0) ? " LIMIT $limit" : "");
            $preparedQuery = self::$connection->prepare($query);
            $preparedQuery->execute();
        }

        return $preparedQuery->fetchAll(self::$connection::FETCH_ASSOC);
    }

    /** Executes SQL query. Cannot be in transaction.
     *
     * @param string     $query         SQL query
     * @param array|null $whereArr      Associative array with values by order.
     * @param bool       $multiResponse If true returns all fetches lines.
     * @return array|bool|mixed One fetched line or all fetched lines. If query is invalid returns false.
     */
    public static function MakeQuery($query, array $whereArr = null, bool $multiResponse = false) {
        self::connect();

        try {
            $preparedQuery = self::$connection->prepare($query);
            if ($whereArr !== null)
                $result = $preparedQuery->execute($whereArr);
            else
                $result = $preparedQuery->execute();
            if (!$result) {
                throw new InSqlQueryError($preparedQuery->errorInfo()[2]);
            }

            if ($multiResponse)
                return $preparedQuery->fetchAll(self::$connection::FETCH_ASSOC);
            else
                return $preparedQuery->fetch(self::$connection::FETCH_ASSOC);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public static function toggleTransaction() {
        if (!self::$connection->inTransaction()) {
            self::$connection->beginTransaction();
        }
        else {
            self::rollbackTransaction();
        }
    }

    public static function rollbackTransaction() {
        if (!self::$connection->inTransaction()) {
            return false;
        }

        self::$connection->rollback();
    }

    public static function commitTransaction() {
        if (!self::$connection->inTransaction()) {
            return false;
        }

        self::$connection->commit();
    }
}