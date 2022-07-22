<?php

namespace Upio\UpCache\Rules;

use Upio\UpCache\Enums\LifecycleTypes;
use Upio\UpCache\UpCacheBase;

class UpCacheOptionsExclude extends UpCacheBase implements IUpCacheRules
{
    public function setCss(): void
    {
        $excluded = $this->getPluginOption('ignore_css_files_min');
        if (!$excluded || empty($excluded)) {
            return;
        }
        $excluded = explode(',', $excluded);
        self::setStyles(array(LifecycleTypes::Ignore => $excluded));
    }

    public function setJs(): void
    {
        $excluded = $this->getPluginOption('ignore_js_files_min');
        if (!$excluded || empty($excluded)) {
            return;
        }
        $excluded = explode(',', $excluded);
        self::setStyles(array(LifecycleTypes::Ignore => $excluded));
    }

    public function setName(): void
    {
        self::setRuleName('admin_exclude_min_option_fields');
    }
}
if ( !class_exists('UpCacheOptionsExclude') ) {
    $rule_options = new UpCacheOptionsExclude();
}
