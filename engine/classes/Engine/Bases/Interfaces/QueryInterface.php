<?php

namespace Engine\Bases\Interfaces;

use Engine\Bases\Enum\DatabaseCompareActionsEnum;

interface QueryInterface
{
    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;
    const QUERY_DELETE = 3;
    const QUERY_UPDATE = 4;
    
    function from(string $tableName, string $alias = null): self;
    
    function fromSubquery(self $subquery, string $alias = null): self;
    
    function fromRawSubquery(string $subquery, string $alias = null): self;
    
    function where(string $column, DatabaseCompareActionsEnum|string $compare, string $value): self;
    
    function whereNot(string $column, string $value): self;
    
    function orWhereNot(string $column, string $value): self;
    
    function whereRaw(string $column, DatabaseCompareActionsEnum|string $compare, string $subquery): self;
    
    function orWhereRaw(string $column, DatabaseCompareActionsEnum|string $compare, string $subquery): self;
    
    function orWhere(string $column, DatabaseCompareActionsEnum|string $compare, string $value): self;
    
    function whereIn(string $column, array $values): self;
    
    function whereNotIn(string $column, array $values): self;
    
    function whereLike(string $column, string $likeValue): self;
    
    function whereNotLike(string $column, string $likeValue): self;
    
    function orWhereLike(string $column, string $likeValue): self;
    
    function orWhereNotLike(string $column, string $likeValue): self;
    
    function select(string $column, string $alias = null): self;
    
    function selectAll(string $fromTable = null): self;
    
    function selectSubquery(self $subquery, string $alias = null): self;
    
    function selectRaw(string $raw): self;
    
    function selectMax(string $column, string $alias = null): self;
    
    function selectMin(string $column, string $alias = null): self;
    
    function selectAbs(string $column, string $alias = null): self;
    
    function selectCount(string $column, string $alias = null): self;
    
    function selectExists(self|string $query, string $alias = null): self;
    
    function selectCeil(string $column, string $alias = null): self;
    
    function selectFloor(string $column, string $alias = null): self;
    
    function selectRound(string $column, string $alias = null): self;
    
    function selectTruncated(string $column, string $alias = null): self;
    
    function distinctSelect(): self;
    
    function join(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                  string $toColumn): self;
    
    function innerJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                       string $toColumn): self;
    
    function rightJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                       string $toColumn): self;
    
    function leftJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                      string $toColumn): self;
    
    function rightOuterJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                            string $toColumn): self;
    
    function leftOuterJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                           string $toColumn): self;
    
    function crossJoin(string $table, string $toTable, string $column, DatabaseCompareActionsEnum|string $compare,
                       string $toColumn): self;
    
    function groupBy(string $column, string $table = null): self;
    
    public function having(string $columnStatement, DatabaseCompareActionsEnum|string $compare, string $value): self;
    
    function orderBy(string $column, string $order = 'ASC'): self;
    
    function limit(int $count, int $offset): self;
    
    function offset(int $count): self;
    
    function insert(string $table, array $fields): self;
    
    function update(string $table, array $fields): self;
    
    function delete(string $table): self;
    
    function get(): string;
    
    function runDirectly(): array|bool;
    
    function getContents(): array;
}