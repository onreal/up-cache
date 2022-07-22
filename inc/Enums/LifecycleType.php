<?php

namespace Upio\UpCache\Enums;

abstract class LifecycleType
{
    const Ignore = 'ignore';
    const Remove = 'remove';
    const Require = 'require';
}
