<?php
require_once 'Lib/Http/Request.php';

/**
 * クローリングクラス
 */
class Crawl {

    private $_base_url = '';

    private $_file_path = '';

    private $_fp = null;

    private $_url = [];

    private $_result = [];

    /**
     * Constructor
     *
     * @param string $url
     * @param string $file_path
     */
    public function __construct($url, $file_path = '')
    {
        $this->_base_url = $url;
        $this->_file_path = $file_path;
    }

    /**
     * 実行関数
     * （ファイル出力先が指定されている場合は結果がファイル出力される）
     *
     * @return array
     */
    public function execute()
    {
        $base_url = $this->_base_url;

        if($this->_file_path != ''){
            $fp = fopen($this->_file_path, "w");
        }else{
            $fp = false;
        }

        if($fp !== false){
            $this->_fp = $fp;
        }

        $this->_process($base_url);

        if($fp !== false){
            fclose($fp);
        }

        return $this->_result;
    }

    /**
     * クローリングのメイン処理 
     *
     * @param array | string $urls
     * @return void
     */
    private function _process($urls)
    {
        if(! is_array($urls)){
            $urls = (array)$urls;
        }

        if(count($urls) === 0){
            return;
        }

        $get_urls = [];
        foreach($urls as $url){
            $get_urls = array_merge($get_urls, $this->analiyze($url));
        }

        // 処理対処のURLがあれば、再帰的に処理を継続する
        if(count($get_urls) > 0){
            $unique_urls = array_unique($get_urls);
            $this->_process($unique_urls);
        }
    }

    /**
     * ターゲットのURLにアクセスし、DOM解析する 
     *
     * @param string $url
     * @return array
     */
    public function analiyze($url)
    {
        if(is_null($url)){
            return [];
        }

        // /~ も含める
        if(preg_match("/^\//",$url)){
            $url = $this->_base_url.$url;
        }

        // "/" で終わるように調整する
        if( !preg_match("/\/$/",$url)){
            $url .= '/';
        }

        // クローリング済みの内部URLの場合、何もしない
        if(array_search($url, $this->_url) !== false){
            return [];
        }

        // ターゲットURLへアクセス
        $request = new Request();
        $html = $request->get($url);
        if($html == null){
            return [];
        }

        // titile
        if(preg_match('/<title>(.*?)<\/title>/ms', $html, $matched)){
            $title = trim($matched[1]);
        }else{
            $title = '';
        }

        $this->_url[] = $url;
        $this->_result[] = ['url' => $url, 'title' => $title];

        $this->_write("url: $url, title: $title");

        // URL
        if(!preg_match_all('/<a href="(.*?)".*?>/ms', $html, $matched)){
            return [];
        }

        $urls = $matched[1];

        $result = $this->_filterUrl($urls);

        return $result;
    }

    /**
     * 不要なURLを除去する
     *
     * @param array $urls
     * @return array
     */
    private function _filterUrl($urls)
    {
        $_urls = array_unique($urls);
        $got_urls = $this->_url;
        $base_url = $this->_base_url;

        $filter_func = function($url) use($base_url, $got_urls){
            if($url === $base_url || $url === $base_url.'/'){
                return false;
            }

            // 外部サイトは除去する
            if(strpos($url, $base_url) === false){
                return false;
            }

            // 取得済みのURLも除去する
            if(array_search($url, $got_urls) !== false){
                return false;
            }

            return true;
        };

        $urls = array_filter($_urls, $filter_func);

        return $urls;
    }

    /**
     * ファイル出力
     * 
     * @param string $string
     */
    private function _write($string)
    {
        if(strlen($string) === 0 || is_null($this->_fp)){
            return;
        }

        fwrite($this->_fp, $string."\n");
    }
}
