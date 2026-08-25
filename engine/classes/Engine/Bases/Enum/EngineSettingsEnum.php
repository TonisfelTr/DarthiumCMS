<?php

namespace Engine\Bases\Enum;

enum EngineSettingsEnum: string
{
    case needActivate = 'na';
    case multiplyAccountsPermitted = 'map';
    case standardGroup             = 'sg';
    case siteName = 'sn';
    case siteTagsLine = 'stl';
    case siteHashtags = 'sh';
    case siteLanguage = 'sl';
}
