<?php

namespace Swoft\Support\Cache;

use Psr\SimpleCache\CacheInterface;
use Swoft\Support\Filesystem;
use Swoole\Coroutine;

class File implements CacheInterface
{
    /**
     * 缓存根目录
     *
     * @var string
     */
    protected $root;

    /**
     * 缓存目录类型（文件夹）
     *
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(array $options = [])
    {
        $this->root   = alias($options['path'] ?? '@runtime');
        $this->type   = $options['type'] ?? 'default';
        $this->prefix = $options['prefix'] ?? '';

        $this->filesystem = filesystem();
    }

    /**
     * @param string $key
     * @return string
     */
    protected function normalizeKey(&$key)
    {
        return $this->prefix.$key;
    }

    /**
     * 格式化value
     *
     * @param  mixed $value
     * @return array
     */
    protected function normalizeValue(&$value, $timeout = null)
    {
        $time = time() + $timeout;
        if ($timeout == null) {
            $time = 0;
        }
        $value = [
            'value'   => is_bool($value) ? (int) $value : $value,
            'timeout' => &$time
        ];
    }

    /**
     * 获取缓存路径
     *
     * @param  string $Key
     * @return string
     */
    public function normalizePath($key)
    {
        $key = $this->normalizeKey($key);
        return "{$this->root}/{$this->type}/{$key}";
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        if (empty($key)) {
            return false;
        }
        $this->normalizeValue($value, $ttl);

        return $this->filesystem->put($this->normalizePath($key), $value, true);
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            return $default;
        }
        $content = $this->filesystem->get($this->normalizePath($key), true);
        if ($this->isExpired($key, $content)) {
            return false;
        }
        return $content['value'];
    }

    /**
     * 判断缓存内容是否有效
     *
     * @param $key
     * @param $data
     * @return bool
     */
    protected function isExpired($key, &$content)
    {
        if (!isset($content['value']) || $content['value'] === '' || $content['value'] === false) {
            $this->delete($key);
            return true;
        }

        if (empty($data['timeout'])) {
            return false;
        }
        if (time() > $data['timeout']) {
            // 如过期则删除缓存文件
            $this->delete($key);
            return true;
        }
        return false;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!$values instanceof \Iterator && !is_array($values)) {
            return false;
        }

        $result = false;
        foreach ($values as $k => &$v) {
            $result &= $this->set($k, $v, $ttl);
        }

        return $result;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        if (!$keys instanceof \Iterator && !is_array($keys)) {
            return [];
        }

        $result = [];
        foreach ($keys as &$v) {
            $result[$v] = $this->get($v, $default);
        }

        return $result;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        if (empty($key)) {
            return false;
        }

        $filePath = $this->normalizePath($key);

        if (is_file($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        if (!$keys instanceof \Iterator && !is_array($keys)) {
            return false;
        }

        $result = false;
        foreach ($keys as &$v) {
            $result &= $this->delete($v);
        }

        return $result;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        return (bool) $this->get($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $dir = $this->normalizePath('');

        if (!is_dir($dir)) {
            return true;
        }

        return $this->filesystem->deleteDirectory($dir);
    }

}
