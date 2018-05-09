<?php

/**
 * HTTPリクエストクラス
 */
class Request {

    /**
     * GETでHTTPリクエストする
     *
     * @param string $url
     * @param array $headers
     * @return string
     */
    public function get($url, $headers = [])
    {
        $ch = curl_init($url);
         
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        //Locationをたどる
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
        //最大何回リダイレクトをたどるか
        curl_setopt($ch,CURLOPT_MAXREDIRS,10);
        //リダイレクトの際にヘッダのRefererを自動的に追加させる
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);

        $res_data = curl_exec($ch);
         
        $res_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         
        if (preg_match('/^20.$/', $res_code) !== 1 && $res_code != 302) {   // ステータスコードが200番台＋302以外の場合
            $res_data = NULL;
        }
         
        if (curl_errno($ch) != 0) {
            $res_data = NULL;
        }
         
        curl_close($ch);
         
        return $res_data;
    }
}
