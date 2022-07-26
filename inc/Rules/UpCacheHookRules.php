<?php

namespace Upio\UpCache\Rules;

use Upio\UpCache\Enums\LifecycleType;
use Upio\UpCache\UpCacheBase;

class UpCacheHookRules extends UpCacheBase implements IUpCacheRules
{
    public function setCss(): void
    {
        $rules = apply_filters('upio_uc_set_css_rules', array());
        self::setStyles( $rules );
    }

    public function setJs(): void
    {
        $rules = apply_filters('upio_uc_set_js_rules', array());
        self::setStyles( $rules );
    }

    public function setName(): void
    {
        self::setRuleName('hooked_rules');
    }
}

if ( !class_exists('UpCacheHookRules') ) {
    $rule_hooks = new UpCacheHookRules();
}
