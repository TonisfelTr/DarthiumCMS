<?php

namespace Engine\Database;

use Engine\Bases\Abstracts\AbstractDatabaseQuery;
use Engine\Bases\Enum\DatabaseCompareActionsEnum;
use Engine\Bases\Interfaces\QueryInterface;

class PostreSQLQuery extends AbstractDatabaseQuery
{
    public function from(string $tableName): self {
        $this->fromTable = $tableName;
        
        return $this;
    }
    
    public function fromSubquery(QueryInterface $subquery): self {
        $this->fromTable = "($subquery)";
        
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
    
    public function select(string $column, string $alias = null): self {
        if ($column != '*') {
            $column = '"' . $column . '"';
        }
        
        $this->select[] = is_null($alias) ? "$column" : "$column AS '$alias'";
        
        return $this;
    }
    
    public function selectAll(): self {
        $this->select[] = "*";
        
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
        $this->select[] = is_null($alias) ? "COUNT(`$column`)" : "COUNT(`$column`) AS `$alias`";
        
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
        $this->orderBy = "ORDER BY `$column` $order";
        
        return $this;
    }
    
    public function insert(string $table, array $fields): string {
        $gluedColumnsTitles = implode(", ", array_map(array: array_keys($fields), callback: fn (string $columnName): string => "`$columnName`"));
        $gluedQuestionSigns = implode(", ", array_map(array: $fields, callback: fn () => "?" ));
        
        return "INSERT INTO `$table` ($gluedColumnsTitles) VALUES ($gluedQuestionSigns)";
    }
    
    public function update(string $table, array $fields): string {
        $gluedColumnValues = implode(" AND ", array_map(fn (string $column) => "`$column` = ?", $fields));
        
        if (empty($this->where)) {
            return "UPDATE `$table` SET $gluedColumnValues";
        }
        else {
            $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            $gluedOrWhereColumn  = implode(" OR ", $this->where["or"]);
            
            if (!empty($gluedAndWhereColumn) && !empty($gluedOrWhereColumn)) {
                return "UPDATE `$table` SET $gluedColumnValues WHERE ($gluedAndWhereColumn) AND ($gluedOrWhereColumn)";
            } elseif (!empty($gluedAndWhereColumn)) {
                return "UPDATE `$table` SET $gluedColumnValues WHERE $gluedAndWhereColumn";
            } else {
                return "UPDATE `$table` SET $gluedColumnValues WHERE $gluedOrWhereColumn";
            }
        }
    }
    
    public function delete(string $table): string {
        if (empty($this->where)) {
            return "DELETE FROM `$table`";
        } else {
            $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            $gluedOrWhereColumn  = implode(" OR ", $this->where["or"]);
            
            if (!empty($gluedAndWhereColumn) && !empty($gluedOrWhereColumn)) {
                return "DELETE FROM `$table` WHERE ($gluedAndWhereColumn) AND ($gluedOrWhereColumn)";
            } elseif (!empty($gluedAndWhereColumn)) {
                return "DELETE FROM `$table` WHERE $gluedAndWhereColumn";
            } else {
                return "DELETE FROM `$table` WHERE $gluedOrWhereColumn";
            }
        }
    }
    
    public function get(): string {
        /** @todo сделать ошибку неправильного запроса * */
        $gluedSelected = implode(", ", $this->select);
        
        if (!empty($this->where)) {
            $gluedAndWhereColumn = implode(" AND ", $this->where["and"]);
            $gluedOrWhereColumn  = implode(" OR ", $this->where["or"]);
            
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
        
        if (isset($where)) {
            $result .= $where . " ";
        }
        
        if (!empty($this->orderBy)) {
            $result .= $this->orderBy . " ";
        }
        
        if (!empty($this->limit) && !empty($this->offset)) {
            $result .= " LIMIT {$this->limit}, {$this->offset} ";
        }
        elseif (!empty($this->limit) && empty($this->offset)) {
            $result .= " LIMIT {$this->limit} ";
        }
        elseif (empty($this->limit) && !empty($this->offset)) {
            $result .= " OFFSET {$this->offset} ";
        }
        
        if (isset($gluedGroupBy)) {
            $result .= " GROUP BY $gluedGroupBy ";
        }
        
        if (isset($this->having)) {
            $result .= " {$this->having} ";
        }
        
        return trim($result);
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