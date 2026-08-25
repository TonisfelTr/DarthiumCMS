<?php

namespace Engine\Bases\Enum;

enum DatabaseCompareActionsEnum: string
{
    case Equal       = '=';
    case Less        = '<';
    case LessOrEqual = '<=';
    case More        = '>';
    case MoreOrEqual = '>=';
    case Like        = 'LIKE';
    case iLike       = 'ILIKE';
    case NotEqual    = '<>';
}
