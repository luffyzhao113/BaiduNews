<?php

namespace App\Jobs\Pulls;

use App\Plugins\Curl\Files\Pull;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PullImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $images = [ ];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $images)
    {
        $this->images = $images;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = Pull::instance();
        $file->setSuccess(function ($item, $args) {
            Log::info("文件下载成功：". $args['url']);
        });
        $file->handle($this->images);
    }
}
