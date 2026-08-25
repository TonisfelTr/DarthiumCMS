<?php

namespace Engine;

use Engine\Bases\Abstracts\AbstractDatabaseQuery;
use Engine\Bases\Enum\DatabaseTypeEnum;
use Engine\Database\MySQLQuery;
use Engine\Database\PostreSQLQuery;
use Exceptions\Exemplars\InSqlQueryError;
use Exceptions\Exemplars\NotConnectedToDatabaseError;
use Exceptions\TavernException;
use PDO;
use PDOException;

class Database
{
    private static bool $transactionUsing = false;
    private static PDO $connection;
    private static array|null $lastQueryResult = null;
    private static int $lastInsertedID;
    private static int $lastAffectedCount;

    private static function connect(): void {
        $dsn     = Engine::getDBInfo(5) .
                   ":dbname=" . Engine::getDBInfo(3) .
                   ";host=" . Engine::getDBInfo(0) .
                   ";port=" . Engine::getDBInfo(4) .
                   ";charset=utf8";
        $dblogin = Engine::getDBInfo(1);
        $dbpass  = Engine::getDBInfo(2);
        try {
            self::$connection = new PDO($dsn, $dblogin, $dbpass);
        } catch (PDOException $pdoExcp) {
            $errorMessage = $pdoExcp->getMessage();
            throw new NotConnectedToDatabaseError("Cannot connect to database: $errorMessage");
        }
    }
    
    public static function getLastAffectedCount(): int {
        return self::$lastAffectedCount;
    }
    
    public static function runQuery(string $query, array $values = []): bool {
        self::connect();
        
        if (!empty($values)) {
            $preparedStatement = self::$connection->prepare($query);
            $preparedValues = [];
            
            foreach ($values as $value) {
                if (is_array($value)) {
                    $preparedValues = array_merge($preparedValues, $value);
                }
            }
            
            foreach ($preparedValues as $index => $value) {
                if (is_bool($value)) {
                    $preparedStatement->bindValue($index + 1, $value, PDO::PARAM_BOOL);
                }
                elseif (is_int($value)) {
                    $preparedStatement->bindValue($index + 1, $value, PDO::PARAM_INT);
                }
                else {
                    $preparedStatement->bindValue($index + 1, $value, PDO::PARAM_STR);
                }
            }
            
            $preparedStatement->execute();
            if (str_starts_with($query, "SELECT")) {
                self::$lastQueryResult = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif (str_starts_with($query, "INSERT")) {
                self::$lastInsertedID = self::$connection->lastInsertId();
            }
            elseif (str_starts_with($query, "DELETE") || str_starts_with($query, "UPDATE")) {
                self::$lastAffectedCount = $preparedStatement->rowCount();
            }
        }
        else {
            $queryStatement = self::$connection->query($query);
            $queryStatement->execute();
            if (str_starts_with($query, "SELECT")) {
                self::$lastQueryResult = $queryStatement->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif (str_starts_with($query, "INSERT")) {
                self::$lastInsertedID = self::$connection->lastInsertId();
            }
            elseif (str_starts_with($query, "DELETE") || str_starts_with($query, "UPDATE")) {
                self::$lastAffectedCount = $queryStatement->rowCount();
            }
        }
        
        if (self::$connection->errorCode() !== '00000') {
            throw new InSqlQueryError(self::$connection->errorInfo()["message"]);
        }
        else {
            return true;
        }
    }
    
    public static function toggleTransaction(): void {
        if (!self::$connection->inTransaction()) {
            self::$connection->beginTransaction();
        }
        else {
            self::rollbackTransaction();
        }
    }
    
    public static function rollbackTransaction(): bool {
        if (!self::$connection->inTransaction()) {
            return false;
        }

        self::$connection->rollback();
        return true;
    }
    
    public static function commitTransaction(): bool {
        if (!self::$connection->inTransaction()) {
            return false;
        }

        self::$connection->commit();
        return true;
    }
    
    public static function isReturned(): bool {
        return is_array(self::$lastQueryResult);
    }
    
    public static function getBuilder(): AbstractDatabaseQuery {
        $dbType = Engine::GetDBInfo(5);
        
        return match ($dbType) {
            DatabaseTypeEnum::MySql->value      => new MySQLQuery(),
            DatabaseTypeEnum::PostgreSQL->value => new PostreSQLQuery(),
            /** @todo Необходимо сделать ошибку создания драйвера. */
            default                      => throw new TavernException()
        };
    }
    
    public static function getLastInsertedID(): int|false {
        return self::$lastInsertedID ?? false;
    }
    
    public static function getLastResult(): mixed {
        $result = self::$lastQueryResult;
        
        if (count($result) > 1) {
            return $result;
        }
        elseif (count($result) == 1 && count(reset($result)) == 1) {
            return $result[0];
        }
        elseif (is_array($result) && empty($result)) {
            return [];
        }
        else {
            return reset($result);
        }
    }
}