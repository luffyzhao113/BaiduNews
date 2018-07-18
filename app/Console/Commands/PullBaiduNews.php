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

//        $searcher->each(function ($item){
//            if(Cache::has(md5($item['link']))){
//                return ;
//            }
//
//            $news = app(News::class)->create([
//                'title' => $item['title'],
//                'link' => $item['link'],
//                'author' => $item['author'],
//                'pull_at' => DateTime::forString($item['time'])->format('Y-m-d H:i:s'),
//                'summary' => $item['summary'],
//            ]);
//
//            Cache::put(md5($item['link']), $item['title'], 60*24);
//        });


    }

    protected function getSearcher(){
        $ql = new QueryList();
        $ql->use(BaiduNewsMobile::class, 'baiduNewsMobile');
        $baidu = $ql->baiduNewsMobile(40);
        return $baidu->search($this->argument('keyword'));

//        $ql->use(BaiduNews::class, 'badiduNews');
//
//        $baidu = $ql->badiduNews(40);
//
//        return $baidu->search($this->argument('keyword'));
    }




}
