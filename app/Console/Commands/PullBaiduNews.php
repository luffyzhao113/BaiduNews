<?php

namespace App\Console\Commands;

use App\Plugins\DateTime\DateTime;
use App\Plugins\QueryList\BaiduNews;
use App\Plugins\QueryList\BaiduNewsMobile;
use Illuminate\Console\Command;
use QL\QueryList;
use App\Repositories\Modules\News\Interfaces as News;
use Cache;

class PullBaiduNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull:baidu {keyword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pull baidu news in my databases';
    

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $searcher = $this->getSearcher();

        $pullTime = (int) Cache::get('pull:baidu:news:mobile');
        $lastTime = 0;

        $searcher->each(function ($item) use ($pullTime, &$lastTime){
            $time = DateTime::forString($item['pull_at']);
            if($pullTime >= $time->timestamp){
                return ;
            }else{
                $lastTime = $time->timestamp;
            }

            app(News::class)->create([
                'title' => $item['title'],
                'link' => $item['link'],
                'author' => $item['author'],
                'pull_at' => $time->format('Y-m-d H:i:s'),
                'summary' => $item['summary'],
                'detail'  => $item['detail']
            ]);
        });

        Cache::put('pull:baidu:news:mobile', $lastTime, 60*24);
    }

    /**
     * @return mixed
     */
    protected function getSearcher(){
        $ql = new QueryList();
        $ql->use(BaiduNewsMobile::class, 'baiduNewsMobile');
        $baidu = $ql->baiduNewsMobile(20);
        return $baidu->search($this->argument('keyword'));
    }




}
