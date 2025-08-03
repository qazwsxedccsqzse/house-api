<?php

namespace App\Foundations;

use Illuminate\Support\Facades\Redis;

/**
 * @see https://redis.io/commands
 *
 * @method bool  expire(string $key, int $seconds)                                     EXPIRE key seconds
 * @method bool  expireAt(string $key, int $timestamp)                                 EXPIREAT key timestamp
 * @method bool  set(string $key, mixed $value, ...$options)                           SET key value
 * @method bool  setNx(string $key, mixed $value)                                      SETNX key value
 * @method bool  setEx(string $key, int $seconds, mixed $value, array $options = [])   SETEX key seconds value
 * @method mixed get(string $key)                                                      GET key
 * @method int   incr(string $key)                                                     INCR key
 * @method int   incrBy(string $key, int $increment)                                   INCRBY key increment
 * @method int   decr(string $key)                                                     DECR key
 * @method int   decrBy(string $key, int $decrement)                                   DECRBY key decrement
 * @method bool  mSet(string $key, mixed $value, ...$options)                          MSET key value [key value ...]
 * @method array mGet(string $key, ...$options)                                        MGET key [key ...]
 * @method bool  exists(string $key)                                                   EXISTS key
 * @method bool  del(string $key, ...$options)                                         DEL key [key ...]
 * @method array hKeys(string $key)                                                    HKEYS key
 * @method array hExists(string $key, string $field)                                   HEXISTS key field
 * @method int   hSet(string $key, string $field, mixed $value, ...$options)           HSET key field value [field value ...]
 * @method bool  hSetNx(string $key, string $field, mixed $value)                      HSETNX key field value
 * @method bool  hMSet(string $key, string $field, mixed $value, ...$options)          HMSET key field value [field value ...]
 * @method int   hIncrBy(string $key, string $field, int $increment)                   HINCRBY key field increment
 * @method mixed hGet(string $key, string $field)                                      HGET key field
 * @method array hMGet(string $key, string $field, ...$options)                        HMGET key field [field ...]
 * @method array hGetAll(string $key)                                                  HGETALL key
 * @method array hLen(string $key)                                                     HLEN key
 * @method array hDel(string $key, string $field, ...$options)                         HDEL key field [field ...]
 * @method mixed pipeline(callable $callback)                                          PIPELINE
 *
 * @method int         sAdd(string $key, mixed $member, ...$options)                   SADD key member [member ...]
 * @method array       sMembers(string $key)                                           SMEMBERS key
 * @method int         sCard(string $key)                                              SCARD key
 * @method int         sRem(string $key, mixed $member, ...$options)                   SREM key member [member ...]
 * @method array|mixed sRandMember(string $key, ?int $count = null)                    SRANDMEMBER key [count]
 * @method bool        sIsMember(string $key, mixed $member)                           SISMEMBER key member
 * @method array|mixed sPop(string $key, ?int $count = null)                           SPOP key [count]
 */
class RedisHelper
{
    const SEPARATOR = '::';
    const DEFAULT_EXPIRE_TIME = 3600 * 24 * 30;

    // lock 預設過期時間為 300 秒
    const DEFAULT_LOCK_EXPIRE_TIME = 300;


    protected \Illuminate\Redis\Connections\Connection $redis;
    protected string $prefix;

    public function __construct(string $connection = 'admin-redis')
    {
        $this->redis = Redis::connection($connection);
        $this->prefix = config('cache.prefix') . self::SEPARATOR;
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->redis->{$method}(...$parameters);
    }
}
