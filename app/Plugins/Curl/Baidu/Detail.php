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

class Detail
{
    const API = 'https://news.baidu.com/news?';
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
     * @param array $urls
     */
    public function details(array $urls): void
    {
        collect($urls)->each(
            function ($item, $key) {
                $this->add($item, [$key]);
            }
        );
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
     * @param string $url
     * @param array $args
     */
    protected function add(string $url, array $args): void
    {
        $fail = $success = null;
        if($this->success instanceof Closure){
            $success =  function(array $r, array $args){
                $body = json_decode($r['body'], true);
                if ($body['errno'] == 0 && isset($body['data']) && isset($body['data']['news']) && isset($body['data']['news'][0])) {
                    $item = $body['data']['news'][0];
                    if($this->validateContent($item['content'])){
                        call_user_func($this->success, $item, $args);
                    }else{
                        call_user_func($this->success, [], $args);
                    }
                }
            };
        }

        if($this->fail instanceof Closure){
            $fail = $this->fail;
        }

        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_URL => $this->formatUrl($url),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_REFERER => 'https://news.baidu.com/news',
                    CURLOPT_HTTPHEADER => ['Host:news.baidu.com'],
                ],
                'args' => ['url' => $url],
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
     * @param string $url
     * @return string
     */
    protected function formatUrl(string $url): string
    {
        return self::API.http_build_query(
                [
                    'src' => $url,
                    'tn' => 'bdapiinstantfulltext',
                ]
            );
    }
}