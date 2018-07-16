<?php

namespace App\Console\Commands;

use App\Plugins\QueryList\BaiduNews;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use QL\QueryList;

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
        $ql = new QueryList();

        $ql->use(BaiduNews::class, 'badiduNews');

        $baidu = $ql->badiduNews(10);
        
        $searcher = $baidu->search($this->argument('keyword'));
        

        $searcher->each(function ($item) use($ql){
            $this->info('title:'. $item['title']);
            $this->info('link:'. $item['link']);
            $this->info('author:'. $item['author']);
            $this->info('time:'. $item['time']);
            $this->info('more_link:', $item['more_link']);
            $this->info('summary:'. $item['summary']);
        });
    }




}
