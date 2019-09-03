<?php
/**
 * 极光推送 初始类
 * @Author: 卢训 1312431222@qq.com
 */

namespace App\Http\Service\JG;

use Exception;

class Init {

    protected $app_key;

    protected $master_secret;

    protected $param = [];

    public function __construct($app_key = null, $master_secret = null)
    {
        $this->app_key = empty($app_key) ? config('jg.app_key') : $app_key;

        $this->master_secret = empty($master_secret) ? config('jg.master_secret') : $master_secret;
    }

    /**HTTPS请求
     * @param $url 请求地址
     * @param string $type 请求类型
     * @param array $param 参数
     * @param array $header 请求头
     * @return mixed
     * @throws Exception
     */
    protected function jgHttps($url, $type = 'get', $param = [], $header = [])
    {

        if (!in_array($type, ['get','post','delete','put','patch'])) {
            throw new Exception('错误的请求类型');
        }

        if (empty($header)) {
            $header = $this->getHeader();
        }

        if ($type == 'get') {
            $param = http_build_query($param);
            $url = $url.'?'.$param;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($type == 'post') {
            $param = json_encode($param);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }

        if ($type == 'delete') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        if ($type == 'put') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PUT');
        }

        if ($type == 'patch') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PATCH');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $response = curl_exec($ch);

        if($response === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }

    /**获取推送唯一表示符
     * @param int $count
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    public function getCid($count = 1, $type = 'push')
    {
        $this->getHeader(true);
        $param = [
            'count'=> $count,
            'type'=> $type,
        ];
        $response = $this->jgHttps($this->cidUrl, 'get', $param);
        $response = json_decode($response, true);
        return $response['cidlist'];
    }

    /**获取header
     * @param bool $isCid
     * @return array
     */
    private function getHeader($isCid = false)
    {
        $base64 = base64_encode("$this->app_key:$this->master_secret");
        if ($isCid) {
            $header = array("Authorization: Basic $base64", 'Content-Type: text/plain', 'Accept: application/json');
        } else {
            $header = array("Authorization: Basic $base64", "Content-Type: application/json");
        }

        return $header;
    }
}