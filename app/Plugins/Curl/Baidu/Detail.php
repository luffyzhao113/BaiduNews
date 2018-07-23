<?php
/**
 * Created by PhpStorm.
 * User: luffyzhao
 * Date: 2018/7/19
 * Time: 23:40
 */

namespace App\Plugins\Curl\Baidu;


use App\Plugins\Curl\Abstracts;
use Closure;
use Illuminate\Support\Facades\Log;

class Detail extends Abstracts
{
    const API = 'https://news.baidu.com/news?';

    protected $httpOpt = [
        CURLOPT_REFERER => 'https://news.baidu.com/news',
        CURLOPT_HTTPHEADER => ['Host:news.baidu.com'],
        CURLOPT_HEADER => false
    ];
        /**
     * @param array $urls
     */
    public function handle(array $urls): void
    {
        collect($urls)->each(
            function ($item, $key) {
                if(is_string($item)){
                    $this->add($this->formatUrl($item), [$key]);
                }else{
                    Log::info('-_-:' . json_encode($item));
                }
            }
        );
        $this->start();
    }

    /**
     * @param Closure $success
     * @return Abstracts
     */
    public function setSuccess(Closure $success)
    {
        parent::setSuccess(function (array $r, array $args) use ($success) {
            if(isset($r['body'])) {
                $body = json_decode($r['body'], true);
                if ($body['errno'] == 0 && isset($body['data']) && isset($body['data']['news']) && isset($body['data']['news'][0])) {
                    $item = $body['data']['news'][0];
                    if ($this->validateContent($item['content'])) {
                        call_user_func($success, $item, $args);
                    } else {
                        call_user_func($success, [], $item);
                    }
                }
            }
        });
        return $this;
    }

    /**
     * @param Closure $fail
     * @return Abstracts
     */
    public function setFail(Closure $fail)
    {
        parent::setFail($fail);
        return $this;
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