<?php
/**
 * @copyright   Copyright © 2019 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        21.12.2019
 */

declare(strict_types=1);

namespace Modules\Course\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

/**
 * Class WpApi
 * @package App\Models\Challenges
 */
class WpApi
{
    protected static $url = 'https://old.foodpunk.de/wp-json/wp/v2/';

    /**
     * get Post by Id
     */
    public static function getPost(int $id): Collection|null
    {
        $url    = self::$url . 'pc_article/' . $id;
        $result = self::getJson($url);
        return is_null($result) ? $result : collect($result);
    }

    /**
     * @param $url
     * @return mixed|null
     */
    protected static function getJson($url)
    {
        $client  = new Client();
        $content = false;
        try {
            $response = $client->get(
                $url,
                [
                    'auth' => [
                        config('import.wordpress.basic_auth_login'),
                        config('import.wordpress.basic_auth_password')
                    ],
                    'headers' => [
                        'RequestType'   => 'Internal',
                        'Cache-Control' => 'no-cache',
                    ],
                    [
                        'timeout'         => 40,
                        'connect_timeout' => 10
                    ]
                ]
            );
            $content = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            logError($e);
        }

        return $content ? json_decode($content, true) : null;
    }

    /**
     * get Post by IDs
     */
    public static function getPosts(array $iDs): Collection|null
    {
        $url      = self::$url . 'pc_articles/' . implode(',', $iDs);
        $response = $result = self::getJson($url);
        return is_null($result) ? $result : collect($response);
    }

    /**
     * search post by title
     */
    public static function searchPosts(string $search): Collection|null
    {
        $url    = self::$url . 'pc_articles?s=' . $search;
        $result = self::getJson($url);
        return is_null($result) ? $result : collect($result);
    }
}
