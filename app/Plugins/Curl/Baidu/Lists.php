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

/**
 * @property Closure succes
 */
class Lists extends Abstracts
{
    const API = 'https://news.baidu.com/news?';

    protected $pageSize = 40;

    protected $httpOpt = [
        CURLOPT_REFERER => 'https://news.baidu.com/news',
        CURLOPT_HTTPHEADER => ['Host:news.baidu.com'],
    ];

    /**
     * @param int $pageSize
     * @return Lists
     */
    public function setPageSize(int $pageSize): Lists
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * 开始搜索
     * @method handle
     * @param array $urls
     *
     * @author luffyzhao@vip.126.com
     */
    public function handle(array $urls) : void
    {
        collect($urls)->each(function ($item){
            $this->add($this->formatUrl($item['word'],  $item['page'] ?? 1), $item);
        });
        $this->start();
    }

    /**
     *
     * @method setSuccess
     * @param Closure $success
     *
     * @return $this|Abstracts
     *
     * @author luffyzhao@vip.126.com
     */
    public function setSuccess(Closure $success)
    {
        parent::setSuccess( function (array $r, array $args) use ($success) {
            $body = json_decode($r['body'], true);
            if (isset($body['errno']) && $body['errno'] === 0) {
                call_user_func($success, $body['data']['list'], $args);
            }
        });
        return $this;
    }

    /**
     *
     * @method setFail
     * @param Closure $fail
     *
     * @return $this|Abstracts
     *
     * @author luffyzhao@vip.126.com
     */
    public function setFail(Closure $fail)
    {
        parent::setFail(function ($r, $args)use ($fail){
            call_user_func($fail, $r, $args);
        });
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