<?php

namespace QuinnInteractive\ClearKey\Extensions;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;

class ClearKeyExtension extends DataExtension implements Flushable
{
    private static $cleared_keys = [];

    public function cache()
    {
        return self::getCache();
    }

    public function ClearKey($key)
    {
        $value = $this->getClearKeyValue($key);
        if (!$value) {
            $value = $this->createClearKey($key);
        }
        return $value;
    }

    public function invalidateInvalidClearKeys(string $stage = Versioned::DRAFT): void
    {
        $keys = Config::inst()->get(self::class, 'invalidators');
        // check to see if we are an invalidator for any keys
        if (is_array($keys) && count($keys)) {
            foreach ($keys as $key => $list) {
                // we only need to clear once per page request
                $staged_keys = array_key_exists($stage, self::$cleared_keys) ? self::$cleared_keys[$stage] : [];
                if (!in_array($key, $staged_keys)) {
                    $class = $this->owner->ClassName;
                    if (is_array($list) && count($list)) {
                        foreach ($list as $invalidator) {
                            if ($class == $invalidator || in_array($invalidator, ClassInfo::ancestry($class))) {
                                self::invalidateClearKey($key, $stage);
                            }
                        }
                    } else {
                        // if we have a key, but no invalidators, invalidate no matter what
                        self::invalidateClearKey($key, $stage);
                    }
                }
            }
        }
    }

    public function onBeforeArchive(): void
    {
        $this->invalidateInvalidClearKeys(Versioned::DRAFT);
        $this->invalidateInvalidClearKeys(Versioned::LIVE);
    }

    /**
     * @return void
     */
    public function onBeforeDelete()
    {
        $this->invalidateInvalidClearKeys(Versioned::DRAFT);
    }

    public function onBeforePublish(): void
    {
        $this->invalidateInvalidClearKeys(Versioned::LIVE);
    }

    public function onBeforeUnpublish(): void
    {
        $this->invalidateInvalidClearKeys(Versioned::LIVE);
    }

    /**
     * @return void
     */
    public function onBeforeWrite()
    {
        $this->invalidateInvalidClearKeys(Versioned::DRAFT);
    }

    protected function createClearKey($key): string
    {
        $value = $this->generateClearKeyValue($key);
        $this->cache()->set($key, $value);
        return $value;
    }

    protected function generateClearKeyValue($key): string
    {
        $d = new \DateTime();
        return $key . ':' . $d->format('Y-m-d\TH:i:s.u');
    }

    protected function getClearKeyInvalidators($name)
    {
        $invalidators = Config::inst()->get(self::class, 'invalidators');
        if ($invalidators && key_exists($name, $invalidators)) {
            return $invalidators[$name];
        }
        return false;
    }

    protected function getClearKeyValue($key)
    {
        return $this->cache()->get($key);
    }

    /**
     * @return void
     */
    public static function flush()
    {
        self::getCache()->clear();
    }

    public static function getCache()
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.QuinnInteractiveClearKey');
        return $cache;
    }

    /**
     * @param (int|string) $key
     *
     * @psalm-param array-key $key
     */
    public static function invalidateClearKey($key, string $stage = Versioned::DRAFT): void
    {
        $orig_stage = Versioned::get_stage();
        if ($orig_stage) {
            Versioned::set_stage($stage);
            if (!in_array($key, self::$cleared_keys)) {
                self::getCache()->delete($key);
                self::$cleared_keys[$stage][] = $key;
            }
            Versioned::set_stage($orig_stage);
        }
    }
}
