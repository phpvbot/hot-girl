<?php

namespace Vbot\HotGirl;

use GuzzleHttp\Client;
use Hanson\Vbot\Console\Console;
use Hanson\Vbot\Message\Text;
use Hanson\Vbot\Support\File;
use Hanson\Vbot\Message\Image;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Hanson\Vbot\Extension\AbstractMessageHandler;

class HotGirl extends AbstractMessageHandler
{
    public $author = 'JaQuan';

    public $version = '1.0';

    public $name = 'hot_girl';

    public $zhName = '辣妹图';

    private static $prev_time = 0;

    private static $target = 'http://www.mmjpg.com';

    public function handler(Collection $message)
    {
        if ($message['type'] === 'text' && $message['pure'] == '妹子') {

            $username = $message['from']['UserName'];
            $now = time();

            if ($now - static::$prev_time >= 10) {
                static::$prev_time = $now;
            } else {
                return Text::send($username, '少年，要懂得节制，10 秒一次不要贪。');
            }

            $client = new Client([
                'timeout' => 5.0,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
                ],
                'debug'   => false,
            ]);

            // 随机 1 至当前此此站点文章最大 ID
            $number = random_int(1, 1015);

            try {
                # 获取随机 ID 数据
                $response = $client->request('GET', static::$target.'/mm/'.$number);

                # 解析页码获得文章内最大页数
                $crawler = new Crawler($response->getBody()->getContents());

                $page_links = $crawler->filter('#page>a');

                $last_page = (int) $page_links->eq($page_links->count() - 2)->html();

                # 获取随机 ID 中随机页数据
                $response = $client->request('GET', static::$target.'/mm/'.$number.'/'.random_int(1, $last_page));

                $crawler = new Crawler($response->getBody()->getContents());

                $image_src = $crawler->filter('#content>a>img')->attr('src');

                $response = $client->request('GET', $image_src);

                $file_path = vbot('config')['user_path'].'girls/'.md5($image_src).'.jpg';
                File::saveTo($file_path, $response->getBody()->getContents());

                return Image::send($username, $file_path);
            } catch (\Exception $e) {
                vbot('console')->log($e->getMessage(), Console::ERROR);

                return Text::send($username, '暂时无法提供服务，你，党之栋梁、国之人才，注意身体，千万！');
            }
        }
    }

    /**
     * 注册拓展时的操作.
     */
    public function register()
    {
    }
}