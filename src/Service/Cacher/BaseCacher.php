<?php

namespace App\Service\Cacher;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

abstract class BaseCacher
{
    protected AdapterInterface $memcached;

    #[Pure]
    public function __construct(TraceableAdapter $cacheAdapter)
    {
        $this->memcached = $cacheAdapter->getPool();
    }

    protected function getItem(string $key): mixed
    {
        $item = $this->memcached->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }
}