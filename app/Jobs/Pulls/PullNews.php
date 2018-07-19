<?php

namespace App\Jobs\Pulls;

use App\Exceptions\PullException;
use App\Plugins\Curl\Baidu\Detail as BaiduDetail;
use App\Plugins\DateTime\DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Modules\News\Interfaces as News;
use Closure;

class PullNews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $baidu;

    protected $timeout = 3600;

    protected $tries = 5;

    protected $urls = [];

    /**
     * PullNews constructor.
     * @param array $urls
     */
    public function __construct(array $urls)
    {
        $this->urls = $urls;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $detail = BaiduDetail::instance();

        $detail->setSuccess(Closure::fromCallable([$this, 'onSuccess']));

        $detail->details($this->urls);
    }

    /**
     * 成功
     * @param array $item
     * @param array $args
     */
    public function onSuccess(array $item, array $args) {
        if(empty($item)){
            PullNews::dispatch([$args['url']])->delay(now()->addMinutes(10));
        }else{
            $dateTime = DateTime::forString(substr($item['sourcets'], 0, 10));
            app(News::class)->create(
                [
                    'title' => $item['title'],
                    'link' => $item['url'],
                    'author' => $item['site'],
                    'pull_at' => $dateTime->format('Y-m-d H:i:s'),
                    'summary' => '',
                    'detail' => [
                        'content' => $this->filterDdetail($item['content']),
                    ],
                ]
            );
        }
    }

    /**
     * 过滤文章 or 下载图片
     * @param array $detail
     * @return string
     */
    protected function filterDdetail(array $detail): string
    {
        return collect($detail)->map(
            function ($item){
                if ($item['type'] === 'image') {
                    $filename = $this->filename($item['data']['original']['url']);
                    if($filename['exists'] === false){
                        // 这里放一个下载图片的队列
                    }
                    return '<!--[src=' . $filename['url'] . ']-->';
                }else{
                    return $item['data'];
                }
            }
        )->implode("\n\n");
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
            'exists' => false,
            'url' => $this->imageUrl($earlFile)
        ];
    }

    /**
     * @param $filePath
     * @return mixed
     */
    protected function imageUrl($filePath){
        return str_replace(storage_path('app/public') , '', $filePath);
    }

}
