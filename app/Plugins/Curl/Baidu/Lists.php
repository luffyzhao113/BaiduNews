<?php
/**
 * Created by PhpStorm.
 * User: luffyzhao
 * Date: 2018/7/19
 * Time: 23:40
 */

namespace App\Plugins\Curl\Baidu;


use Ares333\Curl\Curl;
use Closure;

class Lists
{
    const API = 'https://news.baidu.com/news?';
    protected static $instance = null;

    protected $success;

    protected $fail;

    protected $curl = null;

    protected $maxThread = 2;

    protected $pageSize = 40;

    protected $cache = [
        'enable' => false,
        'compress' => 0,
        'dir' => null,
        'expire' => 86400,
        'verifyPost' => false
    ];

    protected $httpOpt = [];

    protected function __construct()
    {
        $this->curl = new Curl();
    }

    /**
     * @param Closure $success
     * @return $this
     */
    public function setSuccess(Closure $success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @param Closure $fail
     * @return $this
     */
    public function setFail(Closure $fail)
    {
        $this->fail = $fail;

        return $this;
    }

    /**
     * @param int $maxThread
     * @return Detail
     */
    public function setMaxThread(int $maxThread): Detail
    {
        $this->maxThread = $maxThread;

        return $this;
    }

    /**
     * @param array $cache
     * @return Detail
     */
    public function setCache(array $cache): Detail
    {
        $this->cache = array_merge($this->cache, $cache);

        return $this;
    }

    /**
     * @param array $httpOpt
     * @return Detail
     */
    public function setHttpOpt(array $httpOpt): Detail
    {
        $this->httpOpt = array_merge($this->httpOpt, $httpOpt);

        return $this;
    }

    /**
     * @param int $pageSize
     * @return Lists
     */
    public function setPageSize(int $pageSize): Lists
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    protected function __clone()
    {
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 开始搜索
     * @param string $word
     * @param int $page
     */
    public function search(string $word, int $page = 1){
        $this->add($word, $page);
        $this->start();
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
     * @param string $word
     * @param int $page
     */
    protected function add(string $word, int $page): void
    {
        $fail = $success = null;

        if($this->success instanceof Closure){
            $success = function (array $r, array $args) {
                $body = json_decode($r['body'], true);
                if (isset($body['errno']) && $body['errno'] === 0) {
                    call_user_func($this->success, $body['data']['list'], $args);
                }
            };
        }

        if($this->fail instanceof Closure){
            $fail =  $this->fail;
        }

        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_URL => $this->formatUrl($word, $page),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_REFERER => 'https://news.baidu.com/news',
                    CURLOPT_HTTPHEADER => ['Host:news.baidu.com'],
                ],
                'args' => ['word' => $word],
            ],
            $success,
            $fail
        );
    }

    /**
     * 验证内容
     * @param $content
     * @return bool
     */
    protected function validateContent($content)
    {
        if (is_array($content) && count($content) > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 格式化url
     * @param string $word
     * @param int $page
     * @return string
     */
    protected function formatUrl(string $word, int $page): string
    {
        return self::API.http_build_query(
                [
                    'tn' => 'bdapinewsearch',
                    'word' => $word,
                    'pn' => $page,
                    'rn' => $this->pageSize,
                    'ct' => 0,
                ]
            );
    }
}