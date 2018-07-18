<?php
/**
 * Created by PhpStorm.
 * User: luffyzhao
 * Date: 2018/7/16
 * Time: 22:07
 */

namespace App\Plugins\QueryList;


use QL\QueryList;

class BaiduNews
{
    protected const API = 'https://news.baidu.com/ns';
    protected const RULES = [
        'title' => ['h3','text'],
        'link' => ['h3>a','href'],
        'author' => ['.c-author', 'text'],
        'summary' => ['.c-summary .c-span-last', 'text'],
        'more_link' => ['a.c-more_link', 'href']
    ];
    protected const RANGE = '.result';

    protected $ql;
    protected $pageSize;
    protected $httpOpt = [];

    protected $site = '';
    /**
     * BaiduNews constructor.
     * @param QueryList $ql
     * @param $pageSize
     */
    public function __construct(QueryList $ql, $pageSize)
    {
        $this->ql = $ql->rules(self::RULES)->range(self::RANGE);
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
        $name = $opt[0] ?? 'baiduNews';
        $queryList->bind($name,function ($pageSize = 10) use ($queryList){
            return new BaiduNews($queryList,$pageSize);
        });
    }

    /**
     * @param array $httpOpt
     * @return BaiduNews
     */
    public function setHttpOpt(array $httpOpt): BaiduNews
    {
        $this->httpOpt = $httpOpt;

        return $this;
    }

    /**
     * @param string $site
     * @return BaiduNews
     */
    public function setSite(string $site): BaiduNews
    {
        $this->site = $site;

        return $this;
    }

    /**
     * 执行搜索
     * @method search
     * @param $word
     * @param int $page
     *
     * @return \Illuminate\Support\Collection
     *
     * @author luffyzhao@vip.126.com
     */
    public function search($word, $page = 1)
    {
        return $this->query($word, $page)->query()->getData(function ($item){
            $item['time'] = $this->formatTime($item['author']);
            $item['author'] = $this->formatAuthor($item['author']);
            $item['summary'] = $this->formatSummary($item['summary']);
            $item['title'] = trim($item['title']);
            $item['link'] = trim($item['link']);
            $item['more_link'] = $item['more_link'];
            return $item;
        });
    }

    /**
     * 执行搜索
     * @method query
     * @param $word
     * @param int $page
     *
     * @return QueryList
     *
     * @author luffyzhao@vip.126.com
     */
    protected function query($word, $page = 1)
    {
        $this->ql->get(self::API,[
            'word' => $this->formatWord($word),
            'clk' => 'sortbytime',
            'ie'=> 'utf-8',
            'rn' => $this->pageSize,
            'pn' => $this->pageSize * ($page)
        ],$this->httpOpt);
        return $this->ql;
    }

    /**
     * 格式化搜索词
     * @method formatWord
     * @param $word
     *
     * @return string
     *
     * @author luffyzhao@vip.126.com
     */
    protected function formatWord($word){
        if($this->site != ''){
            return 'site:'. $this->site . ' ' . $word;
        }
        return $word;
    }

    /**
     * 格式化时间
     * @param $time
     * @return mixed
     */
    protected function formatTime($time)
    {
        preg_match('/((\d{1,2}分钟前)|(\d{1,2}小时前)|([0-9]{4}年[0-9]{2}月[0-9]{2}日 [0-9]{2}:[0-9]{2}))/', $time, $matches);
        return $matches[0];
    }

    /**
     * 格式化来源
     * @param $author
     * @return string
     */
    protected function formatAuthor($author){
        preg_match('/((\d{1,2}分钟前)|(\d{1,2}小时前)|([0-9]{4}年[0-9]{2}月[0-9]{2}日 [0-9]{2}:[0-9]{2}))/', $author, $matches);
        return trim(str_ireplace($matches[0], '', $author));
    }

    /**
     * 格式化新闻
     * @param $summary
     * @return string
     */
    protected function formatSummary($summary){
        $summary = str_replace(['百度快照', '查看更多相关新闻&gt;&gt;  - '], '', $summary);
        $summary = preg_replace('/^([^\n]+)/', '', $summary);
        $summary = preg_replace('/((\d{1,2}分钟前)|(\d{1,2}小时前)|([0-9]{4}年[0-9]{2}月[0-9]{2}日 [0-9]{2}:[0-9]{2}))/', '', $summary);
        return trim($summary);
    }
}