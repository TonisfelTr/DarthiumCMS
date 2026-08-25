<?php

namespace Engine\Database;

use Engine\Bases\Abstracts\AbstractDatabaseQuery;
use Engine\Bases\Enum\DatabaseCompareActionsEnum;
use Engine\Bases\Interfaces\QueryInterface;

class MySQLQuery extends AbstractDatabaseQuery
{
    public function from(string $tableName, string $alias = null): self {
        $this->fromTable = $tableName;
        if (!is_null($alias)) {
            $this->fromTableAlias = $alias;
        }
        
        return $this;
    }
    
    public function fromSubquery(QueryInterface $subquery, string $alias = null): self {
        $this->fromIsSubquery = true;
        $this->fromTable = "({$subquery->get()})";
        if (!is_null($alias)) {
            $this->fromTableAlias = $alias;
        }
        
        $this->content['where'] =
            array_merge($this->content['where'] ?? [], $subquery->getContents()['where'] ?? []);
        
        return $this;
    }
    
    public function fromRawSubquery(string $subquery, string $alias = null): self {
        $this->fromTable = "($subquery)";
        if (!is_null($alias)) {
            $this->fromTableAlias = $alias;
        }
        
        return $this;
    }
    
    public function where(string $column, DatabaseCompareActionsEnum|string $compare, string $value): self {
        if (str_contains($column, ".")) {
            $exploded = explode(".", $column);
            $this->where["and"][] = "`$exploded[0]`.`$exploded[1]` $compare ?";
        } else {
            $this->where["and"][] = "`$column` $compare ?";
        }
        $this->content["where"][] = $value;
        
        return $this;
    }
    
    public function whereNot(string $column, string $value): self {
        if (str_contains($column, ".")) {
            $exploded             = explode(".", $column);
            $this->where["and"][] = "`$exploded[0]`.`$exploded[1]` <> ?";
        }
        else {
            $this->where["and"][] = "`$column` <> ?";
        }
        $this->content["where"][] = $value;
        
        return $this;
    }
    
    public function orWhereNot(string $column, string $value): self {
        if (str_contains($column,".")) {
            $exploded = explode(".", $column);
            $this->where["or"][] = "`$exploded[0]`.`$exploded[1]` <> ?";
        } else {
            $this->where["or"][] = "`$column` <> ?";
        }
        $this->content["where"][] = $value;
        
        return $this;
    }
    
    public function whereRaw(string $column, DatabaseCompareActionsEnum|string $compare, string $subquery): self {
        $this->where["and"][] = "`$column` $compare ($subquery)";
        
        return $this;
    }
    
    public function orWhereRaw(string $column, DatabaseCompareActionsEnum|string $compare, string $subquery): self {
        $this->where["or"][] = "`$column` $compare ($subquery)";
        
        return $this;
    }
    
    public function orWhere(string $column, DatabaseCompareActionsEnum|string $compare, string $value): self {
        $this->where["or"][]      = "`$column` $compare ?";
        $this->content["where"][] = $value;
        
        return $this;
    }
    
    public function whereIn(string $column, array $values): self {
        $questSymbols = [];
        foreach ($values as $value) {
            $questSymbols[] = '?';
        }
        
        $this->content["where"][] = '`$column` IN(' . implode(', ', $questSymbols) . ')';
        
        return $this;
    }
    
    public function whereNotIn(string $column, array $values): self {
        $questSymbols = [];
        foreach ($values as $value) {
            $questSymbols[] = '?';
        }
        
        foreach ($values as $value) {
            $this->where["and"][] = "`$$column` NOT IN(" . implode(",", $questSymbols) .  ")";
        }
        
        return $this;
    }
    
    public function whereLike(string $column, string $likeValue): self {
        if (str_contains($column, ".")) {
            $exploded             = explode(".", $column);
            $this->where["and"][] = "`$exploded[0]`.`$exploded[1]` LIKE ?";
        }
        else {
            $this->where["and"][] = "`$column` LIKE ?";
        }
        $this->content["where"][] = $likeValue;
        
        return $this;
    }
    
    public function whereNotLike(string $column, string $likeValue): self {
        if (str_contains($column, ".")) {
            $exploded             = explode(".", $column);
            $this->where["and"][] = "`$exploded[0]`.`$exploded[1]` NOT LIKE ?";
        }
        else {
            $this->where["and"][] = "`$column` NOT LIKE ?";
        }
        $this->content["where"][] = $likeValue;
        
        return $this;
    }
    
    public function orWhereLike(string $column, string $likeValue): self {
        if (str_contains($column, ".")) {
            $exploded            = explode(".", $column);
            $this->where["or"][] = "`$exploded[0]`.`$exploded[1]` LIKE ?";
        }
        else {
            $this->where["or"][] = "`$column` LIKE ?";
        }
        $this->content["where"][] = $likeValue;
        
        return $this;
    }
    
    public function orWhereNotLike(string $column, string $likeValue): self {
        if (str_contains($column, ".")) {
            $exploded             = explode(".", $column);
            $this->where["or"][] = "`$exploded[0]`.`$exploded[1]` NOT LIKE ?";
        }
        else {
            $this->where["or"][] = "`$column` NOT LIKE ?";
        }
        $this->content["where"][] = $likeValue;
        
        return $this;
    }
    
    public function select(string $column, string $alias = null): self {
        if ($column != "*") {
            $column = "`$column`";
        }
        
        $this->select[] = is_null($alias) ? "$column" : "$column AS `$alias`";
        
        return $this;
    }
    
    public function selectAll(string $fromTable = null): self {
        if (!is_null($fromTable)) {
            $this->select[] = "`$fromTable`.*";
        }
        else {
            $this->select[] = "*";
        }
        
        return $this;
    }
    
    public function selectSubquery(QueryInterface $subquery, string $alias = null): self {
        if (!is_null($alias)) {
            $this->select[] = "({$subquery->get()}) AS `$alias`";
        } else {
            $this->select[] = "({$subquery->get()})";
        }
        $this->content["where"] = array_merge($this->content['where'] ?? [], $subquery->getContents()['where'] ?? []);
        
        return $this;
    }
    
    public function selectRaw(string $raw): self {
        $this->select[] = $raw;
        
        return $this;
    }
    
    public function distinctSelect(): self {
        $this->distinct = true;
        
        return $this;
    }
    
    public function selectMax(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "MAX(`$column`)" : "MAX(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectMin(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "MIN(`$column`)" : "MIN(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectAbs(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "ABS(`$column`)" : "ABS(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectCount(string $column, string $alias = null): self {
        if ($column != '*') {
            $column = "`$column`";
        }
        $this->select[] = is_null($alias) ? "COUNT($column)" : "COUNT($column) AS `$alias`";
        
        return $this;
    }
    
    public function selectExists(string|QueryInterface $query, string $alias = null): self {
        if (!is_string($query)) {
            $queryString   = $query->get();
            $this->content['where'] = array_merge($this->content['where'] ?? [], $query->getContents()['where'] ?? []);
        }
        else {
            $queryString = $query;
        }
        
        $this->select[] = is_null($alias) ? "EXISTS($queryString)" : "EXISTS($queryString) AS `$alias`";
        
        return $this;
    }
    
    public function selectCeil(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "CEIL(`$column`)" : "CEIL(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectFloor(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "FLOOR(`$column`)" : "FLOOR(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectRound(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "ROUND(`$column`)" : "ROUND(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function selectTruncated(string $column, string $alias = null): self {
        $this->select[] = is_null($alias) ? "TRUNCATE(`$column`)" : "TRUNCATE(`$column`) AS `$alias`";
        
        return $this;
    }
    
    public function join(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                            string $toColumn): self {
        
        $this->joins[] = "JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function innerJoin(string                            $table, string $toTable, string $column,
                              string|DatabaseCompareActionsEnum $compare, string $toColumn): QueryInterface {
        $this->joins[] = "INNER JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function rightJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                                 string $toColumn): self {
        $this->joins[] = "RIGHT JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function leftJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                                string $toColumn): self {
        $this->joins[] = "LEFT JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function rightOuterJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                                      string $toColumn): self {
        $this->joins[] = "RIGHT OUTER JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function leftOuterJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                                     string $toColumn): self {
        $this->joins[] = "LEFT OUTER JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function crossJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                                 string $toColumn): self {
        $this->joins[] = "CROSS JOIN `$toTable` ON `$toTable`.`$toColumn` $compare `$table`.`$column`";
        
        return $this;
    }
    
    public function groupBy(string $column, string $table = null): self {
        $this->groupBy[] = is_null($table) ? "`$column`" : "`$table`.`$column`";
        
        return $this;
    }
    
    public function having(string $columnStatement, DatabaseCompareActionsEnum|string $compare, string $value): self {
        $this->having = "HAVING $columnStatement $compare ?";
        
        return $this;
    }
    
    public function orderBy(string $column, string $order = 'ASC'): self {
        $order = strtoupper($order);
        $this->orderBy = "ORDER BY `$column` $order";
        
        return $this;
    }
    
    public function insert(string $table, array $fields): self {
        $gluedColumnsTitles = implode(", ", array_map(array: array_keys($fields), callback: fn (string $columnName): string => "`$columnName`"));
        $gluedQuestionSigns = implode(", ", array_map(array: $fields, callback: fn () => "?" ));
        
        $this->query = "INSERT INTO `$table` ($gluedColumnsTitles) VALUES ($gluedQuestionSigns)";
        
        return $this;
    }
    
    public function update(string $table, array $fields): self {
        $gluedColumnValues = implode(" AND ", array_map(fn (string $column) => "`$column` = ?", $fields));
        
        if (empty($this->where)) {
            $this->query = "UPDATE `$table` SET $gluedColumnValues";
        }
        else {
            $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            $gluedOrWhereColumn  = implode(" OR ", $this->where["or"]);
            
            if (!empty($gluedAndWhereColumn) && !empty($gluedOrWhereColumn)) {
                $this->query =
                    "UPDATE `$table` SET $gluedColumnValues WHERE ($gluedAndWhereColumn) AND ($gluedOrWhereColumn)";
            } elseif (!empty($gluedAndWhereColumn)) {
                $this->query = "UPDATE `$table` SET $gluedColumnValues WHERE $gluedAndWhereColumn";
            } else {
                $this->query = "UPDATE `$table` SET $gluedColumnValues WHERE $gluedOrWhereColumn";
            }
        }
        
        return $this;
    }
    
    public function delete(string $table): self {
        if (empty($this->where)) {
            $this->query = "DELETE FROM `$table`";
        } else {
            $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            $gluedOrWhereColumn  = implode(" OR ", $this->where["or"]);
            
            if (!empty($gluedAndWhereColumn) && !empty($gluedOrWhereColumn)) {
                $this->query = "DELETE FROM `$table` WHERE ($gluedAndWhereColumn) AND ($gluedOrWhereColumn)";
            } elseif (!empty($gluedAndWhereColumn)) {
                $this->query = "DELETE FROM `$table` WHERE $gluedAndWhereColumn";
            } else {
                $this->query = "DELETE FROM `$table` WHERE $gluedOrWhereColumn";
            }
        }
        
        return $this;
    }
    
    public function get(): string {
        /** @todo сделать ошибку неправильного запроса * */
        $gluedSelected = implode(", ", $this->select);
        
        if (!empty($this->where)) {
            if (isset($this->where["and"])) {
                $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            }
            if (isset($this->where["or"])) {
                $gluedOrWhereColumn = implode(" OR ", $this->where["or"]);
            }
            
            if (!empty($gluedAndWhereColumn) && !empty($gluedOrWhereColumn)) {
                $where = "WHERE ($gluedAndWhereColumn) AND ($gluedOrWhereColumn)";
            }
            elseif (!empty($gluedAndWhereColumn)) {
                $where = "WHERE $gluedAndWhereColumn";
            }
            else {
                $where = "WHERE $gluedOrWhereColumn";
            }
        }
        
        if (!empty($this->groupBy)) {
            $gluedGroupBy = implode(", ", $this->groupBy);
        }
        
        $result = "SELECT $gluedSelected ";
        if ($this->fromTable) {
            if (!$this->fromIsSubquery) {
                $result .= "FROM `{$this->fromTable}` ";
            }
            else {
                $result .= "FROM {$this->fromTable} ";
            }
            
            if (isset($this->fromTableAlias)) {
                $result .= "AS `{$this->fromTableAlias}` ";
            }
        }
        
        if (isset($where)) {
            $result .= $where . " ";
        }
        
        if (!empty($this->orderBy)) {
            $result .= $this->orderBy . " ";
        }
        
        if (isset($this->limit) && !empty($this->offset)) {
            $result .= " LIMIT {$this->limit}, {$this->offset} ";
        }
        elseif (isset($this->limit)) {
            $result .= " LIMIT {$this->limit} ";
        }
        
        if (isset($gluedGroupBy)) {
            $result .= " GROUP BY $gluedGroupBy ";
        }
        
        if (isset($this->having)) {
            $result .= " {$this->having} ";
        }
        
        return $this->query = trim($result);
    }
    
    public function limit(int $count, int $offset): self {
        $this->limit  = $count;
        $this->offset = $offset;
        
        return $this;
    }
    
    public function offset(int $count): self {
        $this->offset = $count;
        
        return $this;
    }
}