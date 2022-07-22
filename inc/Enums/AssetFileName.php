<?php

namespace Upio\UpCache\Enums;

abstract class AssetFileName
{
    const Style = UP_CACHE_GLOBAL_PREFIX . '.' . AssetExtension::CSS;
    const Script = UP_CACHE_GLOBAL_PREFIX . '.' . AssetExtension::JS;
}
