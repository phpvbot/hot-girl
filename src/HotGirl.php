<?php

namespace Vbot\HotGirl;

use Vbot\Http\Http;
use Hanson\Vbot\Message\Text;
use Hanson\Vbot\Support\File;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Console\Console;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Hanson\Vbot\Extension\AbstractMessageHandler;

class HotGirl extends AbstractMessageHandler
{
    public $author = 'JaQuan';

    public $version = '1.0.3';

    public $name = 'hot_girl';

    public $zhName = '辣妹图';

    public $baseExtensions = [
        Http::class,
    ];

    private static $target = 'http://www.mmjpg.com';

    private static $http_config = [
        'timeout' => 10.0,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
        ],
    ];

    /**
     * @var null|\Symfony\Component\DomCrawler\Crawler
     */
    private static $crawler = null;

    public function handler(Collection $message)
    {
        if ($message['type'] === 'text' && $message['pure'] == $this->config['keyword']) {

            $username = $message['from']['UserName'];

            // 随机 1 至当前此此站点文章最大 ID
            $number = random_int(1, 1054);

            try {
                # 获取随机 ID 数据
                $response = Http::request('GET', static::$target.'/mm/'.$number, static::$http_config);

                # 解析页码获得文章内最大页数
                static::$crawler->clear();
                static::$crawler->addHtmlContent($response);

                $page_links = static::$crawler->filter('#page>a');

                $last_page = (int) $page_links->eq($page_links->count() - 2)->html();

                # 获取随机 ID 中随机页数据
                $uri = static::$target.'/mm/'.$number.'/'.random_int(1, $last_page);
                $response = Http::request('GET', $uri, static::$http_config);

                # 解析页码获得文章内大图地址
                static::$crawler->clear();
                static::$crawler->addHtmlContent($response);

                $image_src = static::$crawler->filter('#content>a>img')->attr('src');

                $response = Http::request('GET', $image_src, static::$http_config);

                # 存储图片至本地
                $file_path = $this->config['image_path'].md5($image_src).'.jpg';
                File::saveTo($file_path, $response);

                return Image::send($username, $file_path);
            } catch (\Exception $e) {
                vbot('console')->log($e->getMessage(), Console::ERROR);

                return Text::send($username, $this->config['error_message']);
            }
        }
    }

    /**
     * 注册拓展时的操作.
     */
    public function register()
    {
        static::$crawler = new Crawler();

        $default_config = [
            'keyword'       => '妹子',
            'image_path'    => vbot('config')['user_path'].'girls/',
            'error_message' => '暂时无法为您提供服务！',
        ];

        $this->config = array_merge($default_config, $this->config);
    }
}
