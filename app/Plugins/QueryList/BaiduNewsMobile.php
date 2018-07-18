<?php
/**
 * BaiduNews
 * BaiduNewsMobile.php.
 * @author luffyzhao@vip.126.com
 */

namespace App\Plugins\QueryList;

use Ares333\Curl\Curl;
use Ares333\Curl\Toolkit;
use Illuminate\Support\Str;
use QL\QueryList;

class BaiduNewsMobile
{
    protected const API = 'https://news.baidu.com/news';

    protected $curl;
    protected $ql;
    protected $pageSize;
    protected $httpOpt = [];
    protected $maxThread = 5;

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
        $queryList->bind(
            $name,
            function ($pageSize = 10) use ($queryList) {
                return new BaiduNewsMobile($queryList, $pageSize);
            }
        );
    }

    /**
     * @param $word
     * @param int $page
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public function search($word, $page = 1)
    {

        $data = $this->lists($word, $page);

        $data = $this->detail($data);

        return $data->map(
            function ($item) {
                return [
                    'title' => $item['title'],
                    'link' => $item['url'],
                    'author' => $item['author'],
                    'pull_at' => $item['publicTime'],
                    'summary' => '',
                    'detail' => $item['detail'],
                ];
            }
        );
    }


    /**
     * 获取文章列表
     * @param $word
     * @param int $page
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    protected function lists($word, $page = 1)
    {
        $data = collect([]);

        $this->get(
            self::API.'?'.http_build_query(
                [
                    'tn' => 'bdapinewsearch',
                    'word' => $word,
                    'pn' => $page,
                    'rn' => $this->pageSize,
                    'ct' => 0,
                ]
            ),
            function ($r, $args) use (&$data) {
                if ($r['body']) {
                    $body = json_decode($r['body'], true);
                    $data = collect($body['data']['list']);
                }

            }
        );

        $this->curl->start();

        return $data;
    }

    /**
     * @param $data
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    protected function detail($data)
    {
        // 文章内容
        $contentUrl = $data->map(
            function ($item) {
                return 'https://news.baidu.com/news?tn=bdapiinstantfulltext&src='.urldecode($item['url']);
            }
        );
        $data = $data->toArray();

        $this->getMany(
            $contentUrl,
            function ($r, $args) use (& $data) {
                $body = json_decode($r['body'], true);
                $content = $body['data']['news'][0]['content'];
                $data[$args[0]]['detail']['content'] = is_array($content) && count($content) > 1 ? json_encode($this->filterDdetail($content)) : null;
            }
        );

        $this->curl->start();

        return collect($data);
    }

    /**
     * 过滤文章 or 下载图片
     * @param $detail
     * @return mixed
     */
    protected function filterDdetail(array $detail)
    {
        return collect($detail)->map(
            function ($item){
                if ($item['type'] === 'image') {
                    $filename = $this->filename($item['data']['original']['url']);
                    if($filename['exists'] === false){
                        $this->download([
                            'file' => $filename,
                            'url' => $item['data']['original']['url']
                        ]);
                    }
                    return [
                        'type' => 'image',
                        'data' => $filename
                    ];
                }else{
                    return $item;
                }
            }
        )->all();
    }

    /**
     * 添加url
     * @param $url
     * @param null $success
     * @param $fail
     * @param array $args
     */
    protected function get($url, $success = null, $fail = null, array $args = [])
    {
        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_URL => $url,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_REFERER => self::API
                ],
                'args' => $args,
            ],
            $success,
            $fail
        );
    }

    /**
     * 批量添加url
     * @param $urls
     * @param null $success
     * @param null $fail
     */
    protected function getMany($urls, $success = null, $fail = null)
    {
//        开启任务运行状态
//        $this->curl->cache['enable'] = true;
//        $this->curl->cache['dir'] = storage_path('framework/cache/curl');
//        $this->curl->cache['compress'] = 9;
        $this->curl->maxThread = $this->maxThread;

        $urls->each(
            function ($item, $key) use ($success, $fail) {
                $this->get($item, $success, $fail, [$key]);
            }
        );
    }

    /**
     * 下载
     * @param $item
     * @param null $success
     * @param null $fail
     */
    protected function download($item, $success = null, $fail = null)
    {
        $fp = fopen($item['file'], 'w');
        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_REFERER => $item['url'],
                    CURLOPT_URL => $item['url'],
                    CURLOPT_FILE => $fp,
                    CURLOPT_HEADER => false,
                ],
                'args' => ['file' => $item['file']],
            ],
            $success,
            $fail
        );
    }

    /**
     * 文件名
     * @return array
     */
    protected function filename($file)
    {
        $filename = md5($file);
        $dir = storage_path('app/public/' . substr($filename, 0, 3));
        if (!is_dir($dir)) {
            mkdir($dir, true);
        }

        $earlFile = $dir.'/'.$filename.'.png';

        if(file_exists($earlFile)){
            return [
                'file' => $earlFile,
                'exists' => true
            ];
        }

        return [
            'file' => $earlFile,
            'exists' => false
        ];
    }

    /**
     * @param int $maxThread
     * @return BaiduNewsMobile
     */
    public function setMaxThread(int $maxThread): BaiduNewsMobile
    {
        $this->maxThread = $maxThread;

        return $this;
    }
}