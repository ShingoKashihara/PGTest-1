<?php

if(!file_exists('Config/twitter.php')){
    echo "`Config/twitter.php` does not found.\n";
    exit(1);
}

require_once 'Config/twitter.php';
require_once 'Lib/TwitterWrap.php';

$save_dir = './twitter_images/';

// 何度でも実行できるように掃除をしておく
system("rm -f $save_dir*");

try {
    $twitter = new TwitterWrap($oauthConfig);

    $twitter->connect();

    // キーワードによるツイート検索
    $params = ['q' => 'JustinBieber', 'count' => 100];
    $tweets = $twitter->search($params);

    $images = $twitter->getImages($tweets);

    foreach($images as $image){
        echo "Target: $image\n";

        // ファイル名生成
        $fileNameTmp = explode('/', $image);
        $fileNameTmp = array_reverse( $fileNameTmp );
        $fileName = $fileNameTmp[0];

        // 画像データ取得
        $imageData = @file_get_contents($image);

        if ($imageData){
            // サーバ内へ出力する
            @file_put_contents($save_dir . $fileName, $imageData);
        }else{
            echo "`$image` could not be saved.\n";
        }
    }

}catch(Exception $e){
    echo $e->getMessage();
}
