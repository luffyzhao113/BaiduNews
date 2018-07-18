<?php
/**
 * BaiduNews
 * BaiduNewsMobile.php.
 * @author luffyzhao@vip.126.com
 */

namespace App\Plugins\QueryList;

use Ares333\Curl\Curl;
use QL\QueryList;

class BaiduNewsMobile
{
    protected const API = 'https://news.baidu.com/news';

    protected $curl;
    protected $ql;
    protected $pageSize;
    protected $httpOpt = [];

    /**
     * BaiduNews constructor.
     * @param QueryList $ql
     * @param $pageSize
     */
    public function __construct(QueryList $ql, $pageSize)
    {
        $this->curl = new Curl;
        $this->pageSize = $pageSize;
    }

    /**
     *
     * @method install
     *
     * @static      * @param QueryList $queryList
     * @param QueryList $queryList
     * @param array ...$opt
     *
     * @author luffyzhao@vip.126.com
     */
    public static function install(QueryList $queryList, ...$opt)
    {
        $name = $opt[0] ?? 'baiduNewsMobile';
        $queryList->bind($name,function ($pageSize = 10) use ($queryList){
            return new BaiduNewsMobile($queryList,$pageSize);
        });
    }

    /**
     *
     * @method query
     * @param $word
     * @param $page
     *
     * @author luffyzhao@vip.126.com
     */
    public function search($word, $page = 1){
        $this->get([
            'tn' => 'bdapinewsearch',
            'word' => $word,
            'pn' => $page,
            'rn' => $this->pageSize
        ], function ($r, $args){
            if($r['body']){
                $body = json_decode($r['body']);
                print_r($body);
            }

        });

        $this->curl->start();
    }

    /**
     * 获取数据
     * @method get
     * @param $args
     * @param $callback
     *
     * @author luffyzhao@vip.126.com
     */
    protected function get(array $args, $callback){
        $this->curl->add([
            'opt' => [
                CURLOPT_URL => self::API . '?'. http_build_query($args),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true
            ]
        ], $callback, function($error){
//            print_r($error);
        });
    }
}