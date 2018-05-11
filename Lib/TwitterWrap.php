<?php
require_once 'vendor/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Twitter APIへアクセスするラッパークラス
 *
 */
class TwitterWrap {

    const MAX_IMAGE_COUNT = 10;

    private $_connection;

    private $_config = [];

    /**
     * Constructor
     *
     * @param array $config Twitterへの認証情報など
     */
    public function __construct($config = [])
    {
        if(empty($config)){
            throw new Exception('Config is empty.');
        }

        $this->_config = $config;
    }

    /**
     * Twitterへの認証
     *
     */
    public function connect()
    {
        $this->_connection = new TwitterOAuth($this->_config['consumerKey'], $this->_config['consumerSecret'], $this->_config['accessToken'], $this->_config['accessTokenSecret']);
    }

    /**
     * Twitter検索
     *
     * @param array $params 検索条件
     * @return object json_decode済みの検索結果
     */
    public function search($params)
    {
        $tweets = $this->_connection->get('search/tweets', $params);
        return $tweets;
    }

    /**
     * tweet内の画像情報取得（最大10個まで）
     *
     * @param object $tweets json_decode済みのtweet
     * @return array
     */
    public function getImages($tweets)
    {
        if(!isset($tweets->statuses)){
            return [];
        }

        $image_src = [];
        foreach($tweets->statuses as $data){
            if(!isset($data->entities)){
                continue;
            }

            $entities = $data->entities;
            if(!isset($entities->media)){
                continue;
            }

            $image_src = array_merge($image_src, $this->_analizeMedia($entities->media));

            // 重複を除去しておく
            $image_src = array_unique($image_src);

            if(count($image_src) == self::MAX_IMAGE_COUNT){
                break;
            }
        }

        return $image_src;
    }

    /**
     * メディア関連の情報の中から画像情報を取得する
     *
     * @param object $medias
     * @return array
     */
    private function _analizeMedia($medias)
    {
        $result = [];
        foreach($medias as $media){
            if(count($result) == self::MAX_IMAGE_COUNT){
                return $result;
            }

            $image_src = $media->media_url_https ?? '';
            if($image_src == '' || !preg_match( '/(.*?(\.jpg|\.jpeg|\.gif|\.png))/i', $image_src)){
                continue;
            }

            $result[] = $image_src; 
        }

        return $result;
    }
}

