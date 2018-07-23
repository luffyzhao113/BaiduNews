<?php
/**
 * BaiduNews
 * Pull.php.
 * @author luffyzhao@vip.126.com
 */

namespace App\Plugins\Curl\Files;


use App\Plugins\Curl\Abstracts;
use Ares333\Curl\Curl;

class Pull extends Abstracts
{

    public function handle(array $images): void
    {
        collect($images)->each(function ($item){
            $this->add($item['url'], $item);
        });
    }

    /**
     * 添加url
     * @method add
     * @param string $url
     * @param array $args
     *
     * @author luffyzhao@vip.126.com
     */
    protected function add(string $url, array $args): void
    {
        if($fd = $this->getFd($args['file']) === false){
            return ;
        }
        $this->curl->add(
            [
                'opt' => [
                    CURLOPT_URL => $url,
                    CURLOPT_FILE => $fd,
                    CURLOPT_HEADER => false,
                ],
                'args' => $args,
            ],
            $this->success,
            $this->fail
        );
    }

    /**
     * 获取文件资源
     * @method getFd
     * @param $file
     *
     * @return bool|resource
     *
     * @author luffyzhao@vip.126.com
     */
    protected function getFd($file){
        $filename = storage_path('app/public/news/') .$file;
        if(file_exists($filename)){
            return false;
        }
        $dir = pathinfo($filename, PATHINFO_DIRNAME);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }

        return fopen($filename, 'w');
    }
}