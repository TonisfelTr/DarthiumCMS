<?php

namespace Engine\Bases\Enum;

enum DatabaseTypeEnum: string
{
    case PostgreSQL = 'pgsql';
    case MySql      = 'mysql';
}
