<?php
/**
 * BaiduNews
 * Abstracts.php.
 * @author luffyzhao@vip.126.com
 */

namespace App\Plugins\Curl;


use Ares333\Curl\Curl;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * @property Closure succes
 */
abstract class Abstracts
{
    protected static $instance = null;

    protected $success;

    protected $fail;

    protected $curl = null;

    protected $maxThread = 2;

    protected $cache = [
        'enable' => false,
        'compress' => 0,
        'dir' => null,
        'expire' => 86400,
        'verifyPost' => false
    ];

    protected function __clone()
    {
    }

    abstract public function handle(array $urls) : void;

    protected $httpOpt = [];

    protected function __construct()
    {
        $this->curl = new Curl();
        $this->setFail(function (array $r, array $args){});
        $this->setSuccess(function (array $r, array $args){});
    }

    /**
     *
     * @method instance
     *
     * @static
     *
     * @return null|static
     *
     * @author luffyzhao@vip.126.com
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @return Closure
     */
    public function getFail(): ? Closure
    {
        return $this->fail;
    }

    /**
     * @return Closure
     */
    public function getSuccess(): ? Closure
    {
        return $this->success;
    }

    /**
     * 开始采集
     */
    protected function start()
    {
        // 设置httpOpt
        if(empty($this->httpOpt)){
            $this->curl->opt = $this->httpOpt;
        }
        // 设置缓存
        $this->curl->cache = $this->cache;
        // 线程
        $this->curl->maxThread = $this->maxThread;
        $this->curl->start();
    }

    /**
     * 添加url
     * @method add
     * @param string $url
     * @param array $args
     *
     * @author luffyzhao@vip.126.com
     */
    protected function add(string $url, array $args) : void
    {
        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_URL => $url,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                ],
                'args' => ['url' => $url],
            ],
            $this->getSuccess(),
            $this->getFail()
        );
    }

    /**
     * @param array $cache
     * @return Abstracts
     */
    public function setCache(array $cache): Abstracts
    {
        $this->cache = array_merge($this->cache, $cache);

        return $this;
    }

    /**
     * @param Closure $success
     * @return Abstracts
     */
    public function setSuccess(Closure $success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @param Closure $fail
     * @return Abstracts
     */
    public function setFail(Closure $fail)
    {
        $this->fail = $fail;

        return $this;
    }

    /**
     * @param int $maxThread
     * @return Abstracts
     */
    public function setMaxThread(int $maxThread): Abstracts
    {
        $this->maxThread = $maxThread;

        return $this;
    }

    /**
     * @param array $httpOpt
     * @return Abstracts
     */
    public function setHttpOpt(array $httpOpt): Abstracts
    {
        $this->httpOpt = array_merge($this->httpOpt, $httpOpt);

        return $this;
    }
}