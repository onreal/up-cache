<?php

namespace Upio\UpCache\Rules;

interface IUpCacheRules
{
    public function setCss(): void;

    public function setJs(): void;

    public function setName(): void;
}