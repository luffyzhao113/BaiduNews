<?php

namespace App\Console\Commands;

use App\Jobs\Pulls\PullNews;
use App\Plugins\Curl\Baidu\Lists;
use App\Plugins\QueryList\BaiduNewsMobile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use QL\QueryList;
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
        $this->getSearcher();
    }

    /**
     * @return mixed
     */
    protected function getSearcher(){

        $lists = Lists::instance();

        $lists->setSuccess(function (array $item, array $args){
            $pullTime = (int) Cache::get('pull:baidu:news:mobile');
            $collert = collect($item);
            $lastTime = $collert->max('publicTime');
            $urls = array_column($collert->where('publicTime', '>', $pullTime)->all(), 'url');
            $lastTime > $pullTime && Cache::put('pull:baidu:news:mobile', $lastTime, 60 * 24);
            // 分发任务
            PullNews::dispatch($urls);
        });

        $keys = [
            ['word' => $this->argument('keyword'), 'page' => 1]
        ];
        $lists->handle($keys);
    }




}
