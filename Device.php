<?php
/**
 * 极光推送 查询、设置、更新、删除设备的 tag, alias 信息。
 * @Author: 卢训 1312431222@qq.com
 */


namespace App\Http\Service\JG;

use Exception;

class Device extends Init {

    //查询及设置设备相关属性地址
    private $devicesUrl = 'https://device.jpush.cn/v3/devices/';

    //查询及设置设备的别名
    private $aliasesUrl = 'https://device.jpush.cn/v3/aliases/';

    //查询及设置设备的标签
    private $tagUrl = 'https://device.jpush.cn/v3/tags/';

    /**根据registrationId 获取标签 别名 手机号信息
     * @param string $registrationId
     * @return mixed
     * @throws Exception
     */
    public function getDevices(string $registrationId)
    {
        $url = $this->devicesUrl.$registrationId;
        $result = $this->jgHttps($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**根据registrationId设置标签 别名 手机号
     * @param string $registrationId
     * @param array $addTag     增加tag
     * @param array $removeTag  移除tag
     * @param string $alias     设置别名
     * @param string $mobile    设置手机号
     * @return mixed            返回结果
     * @throws Exception
     */
    public function setDevices(string $registrationId, array $addTag, array $removeTag, string $alias, string $mobile)
    {
        $url = $this->devicesUrl.$registrationId;
        $param = [
            'tags' => '',
            'alias' => $alias,
            'mobile' => $mobile,
        ];

        if (!empty($addTag)) {
            $param['tags'] = ['add'=>$addTag];
        }

        if (!empty($removeTag)) {
            if (!isset($param['tags']['add'])) {
                $param['tags'] = ['remove'=>$removeTag];
            } else {
                $param['tags'] = array_merge(['remove'=>$removeTag], $param['tags']);
            }

        }

        $result = $this->jgHttps($url, 'post', $param);
        $result = json_decode($result, true);
        return $result;
    }

    /**获取别名下设备registration_id
     * @param string $alias     别名
     * @param string $platform  平台
     * @return mixed
     * @throws Exception
     */
    public function getRegId(string $alias, string $platform = null)
    {
        $platform = empty($platform) ? '':'?platform='.$platform;
        $url = $this->aliasesUrl.$alias.$platform;
        $result = $this->jgHttps($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**删除别名及该别名与设备的绑定关系
     * @param string $alias     别名
     * @param string $platform  平台
     * @return mixed
     * @throws Exception
     */
    public function delAlias(string $alias, string $platform = null)
    {
        $platform = empty($platform) ? '':'?platform='.$platform;
        $url = $this->aliasesUrl.'/v3/aliases/'.$alias.$platform;
        $result = $this->jgHttps($url, 'delete');
        $result = json_decode($result, true);
        return $result;
    }

    /**获取当前应用所有的标签列表
     * @return mixed
     * @throws Exception
     */
    public function getAllTags()
    {
        $url = $this->tagUrl;
        $result = $this->jgHttps($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**查询设备是否属于某个标签
     * @param string $tag             标签
     * @param string $registrationId  设备ID
     * @return mixed
     * @throws Exception
     */
    public function isBeLongTo(string $tag, string $registrationId)
    {
        $url = $this->tagUrl.$tag.'/registration_ids/'.$registrationId;
        $result = $this->jgHttps($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**在标签下添加设备
     * @param string $tag
     * @param array $regIds
     * @return mixed
     * @throws Exception
     */
    public function tagSetRegId(string $tag, array $regIds)
    {
        $url = $this->tagUrl.$tag;
        $param = [
            'registration_ids' => [
                'add' => $regIds
            ]
        ];
        $result = $this->jgHttps($url,'post', $param);
        $result = json_decode($result, true);
        return $result;
    }

    /**在标签下删除设备
     * @param string $tag
     * @param array $regIds
     * @return mixed
     * @throws Exception
     */
    public function tagRemoveRegId(string $tag, array $regIds)
    {
        $url = $this->tagUrl.$tag;
        $param = [
            'registration_ids' => [
                'remove' => $regIds
            ]
        ];
        $result = $this->jgHttps($url,'post', $param);
        $result = json_decode($result, true);
        return $result;
    }

    /**删除标签及标签和设备之间的联系
     * @param string $tag
     * @param string $platform
     * @return mixed
     * @throws Exception
     */
    public function delTag(string $tag, string $platform = null)
    {
        $platform = empty($platform) ? '':'?platform='.$platform;
        $url = $this->tagUrl.$tag.$platform;
        $result = $this->jgHttps($url,'delete');
        $result = json_decode($result, true);
        return $result;
    }
}