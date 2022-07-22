<?php

namespace Upio\UpCache\Rules;

// if perfmatters is not installed
if ( function_exists('perfmatters_get_current_ID') ) {
    exit;
}

use Upio\UpCache\Enums\LifecycleTypes;
use Upio\UpCache\UpCacheBase;

class UpCachePerfmatters extends UpCacheBase implements IUpCacheRules
{
    private array $scriptManager;

    /**
     * @return array
     */
    private function getScriptManager(): array
    {
        if (!$this->scriptManager || empty($this->scriptManager)) {
            $this->scriptManager = is_multisite()
                ? get_blog_option(get_current_blog_id(), 'perfmatters_script_manager', array())
                : get_option('perfmatters_script_manager', array());
        }

        return $this->scriptManager;
    }

    /**
     * @param $type
     *
     * @return array
     */
    private function getScriptManagerRemoved($type): array
    {
        $manager = $this->getScriptManager();
        if (!isset($perfmatters_script_manager_options['disabled'][$type])
            || empty($perfmatters_script_manager_options['disabled'][$type])) {
            return array();
        }

        return $manager['disabled'][$type];
    }

    /**
     * @param $type
     *
     * @return array
     */
    private function getScriptManagerRemovedType($type): array
    {
        $excluded = array();
        foreach ($this->getScriptManagerRemoved($type) as $resource => $current) {
            if (!in_array($this->getCurrentId(), $current['current'])) {
                continue;
            }

            array_push($excluded, $resource);
        }

        return $excluded;
    }

    /**
     * @return mixed
     */
    private function getCurrentId(): mixed
    {
        return perfmatters_get_current_ID();
    }

    public function setCss(): void
    {
        self::setStyles(array(LifecycleTypes::Removed => $this->getScriptManagerRemovedType(ResourceTypes::CSS)));
    }

    public function setJs(): void
    {
        self::setScripts(array(LifecycleTypes::Removed => $this->getScriptManagerRemovedType(ResourceTypes::JS)));
    }

    public function setName(): void
    {
        self::setRuleName('up_cache_perfmatters');
    }
}
if ( !class_exists('UpCachePerfmatters') ) {
    $rule_perfmatters = new UpCachePerfmatters();
}