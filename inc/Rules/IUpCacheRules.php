<?php

namespace Upio\UpCache\Rules;

interface IUpCacheRules
{
    public static function getType(): string;

    public function setCss(): void;

    public function setJs(): void;

    public function setIntegrationName(): void;
}