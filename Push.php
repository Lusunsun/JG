<?php
/**
 * 极光推送 推送类
 * @Author: 卢训 1312431222@qq.com
 */

namespace App\Http\Service\JG;

use Exception;

class Push extends Init {

    //Push推送地址
    private $url = 'https://api.jpush.cn/v3/push';

    /**设置推送目标标签
     * @param array $tag
     * @return $this
     */
    public function setTagAudience(array $tag)
    {
        $tag = [
            'tag' => $tag,
        ];

        if (isset($this->param['audience'])) {
            $this->param['audience'] = array_merge($this->param['audience'], $tag);
        } else {
            $this->param['audience'] = $tag;
        }

        return $this;
    }

    /**设置推送目标标签(差集)
     * @param array $notTag
     * @return $this
     */
    public function setNotTagAudience(array $notTag)
    {
        $notTag = [
            'tag_not' => $notTag,
        ];

        if (isset($this->param['audience'])) {
            $this->param['audience'] = array_merge($this->param['audience'], $notTag);
        } else {
            $this->param['audience'] = $notTag;
        }

        return $this;
    }

    /**设置推送目标标签(并集)
     * @param array $andTag
     * @return $this
     */
    public function setAndTagAudience(array $andTag)
    {
        $andTag = [
            'tag_and' => $andTag,
        ];

        if (isset($this->param['audience'])) {
            $this->param['audience'] = array_merge($this->param['audience'], $andTag);
        } else {
            $this->param['audience'] = $andTag;
        }

        return $this;
    }

    /**设置推送目标别名
     * @param array $aliasTag
     * @return $this
     */
    public function setAliasAudience(array $aliasTag)
    {
        $this->param['audience']['alias'] = $aliasTag;

        return $this;
    }

    /**设置推送注册ID
     * @param array $regIds
     * @return $this
     */
    public function setRegIdsAudience(array $regIds)
    {
        $this->param['audience']['registration_id'] = $regIds;

        return $this;
    }

    /**设置推送分群ID
     * @param array $segment
     * @return $this
     */
    public function setSegmentAudience(array $segment)
    {
        $this->param['audience']['segment'] = $segment;

        return $this;
    }

    /**设置A/B测试ID
     * @param array $abTest
     * @return $this
     */
    public function setAbTestAudience(array $abTest)
    {
        $this->param['audience']['abtest'] = $abTest;

        return $this;
    }

    /**设置安卓平台推送参数
     * @param array $android
     * @return $this
     */
    public function setAndroidNotification(array $android)
    {
        if (isset($this->param['platform'])) {
            array_push($this->param['platform'],'android');
        } else {
            $this->param['platform'] = ['android'];
        }

        $this->param['notification']['android'] = $android;

        return $this;
    }

    /**设置iOS平台推送参数
     * @param array $ios
     * @return $this
     */
    public function setIosNotification(array $ios)
    {
        if (isset($this->param['platform'])) {
            array_push($this->param['platform'],'ios');
        } else {
            $this->param['platform'] = ['ios'];
        }

        $this->param['notification']['ios'] = $ios;

        return $this;
    }

    /**设置winPhone平台推送参数
     * @param array $winPhone
     * @return $this
     */
    public function setWinPhoneNotification(array $winPhone)
    {
        if (isset($this->param['platform'])) {
            array_push($this->param['platform'],'winphone');
        } else {
            $this->param['platform'] = ['winphone'];
        }

        $this->param['notification']['Winphone'] = $winPhone;

        return $this;
    }

    /**设置message自定义消息(穿透 不会弹出)
     * @param array $message
     * @return $this
     */
    public function setMessage(array $message)
    {
        $this->param['message'] = $message;

        return $this;
    }

    /**设置option可选参数
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->param['options'] = $options;

        return $this;
    }

    /**推送操作
     * @return mixed
     * @throws Exception
     */
    public function push()
    {
        $this->checkFields($this->param);

        vv($this->param);

        $result = $this->jgHttps($this->url, 'post', $this->param);
        return $result;
    }

    /**根据配置文件规则检测参数是否遗漏必须字段或出现未知字段
     * @param $param
     * @throws Exception
     */
    private function checkFields($param)
    {
        $fields = config('jg.Push.Fields');

        foreach ($fields as $f => $v) {
            $this->check(array_keys($fields['must']), array_keys($fields['notMust']), array_keys($param));
                foreach ($v as $type => $value) {
                    if (is_array($value)) {
                        foreach ($value as $kk => $vv) {
                            if ($kk != 'leastOne' && isset($param[$type])) {
                                $this->check(array_values($value['must']), array_values($value['notMust']), array_keys($param[$type]));
                            } else if ($kk == 'leastOne') {
                                if (count($param[$type]) < 1) {
                                    throw new Exception('leastOne:'.$type);
                                } else {
                                    $checkFields = (array_intersect(array_keys($param[$type]) ,array_keys($vv)));
                                    foreach ($checkFields as $field) {
                                        if (isset($vv[$field]['must']) && isset($vv[$field]['notMust'])) {
                                            $this->check(array_values($vv[$field]['must']), array_values($vv[$field]['notMust']), array_keys($param[$type][$field]));
                                        }
                                    }
                                }

                            }
                        }
                    }

                }
        }
    }

    /**检查参数字段 是否遗漏必须字段或携带不合法字段
     * @param array $must 必须携带字段
     * @param array $notMust 非必须携带字段
     * @param array $fields 参数
     * @throws Exception
     */
    private function check(array $must, array $notMust, array $fields)
    {
        $lost = array_diff($must, $fields);

        if (!empty($lost)) {
            $lost = current($lost);
            throw new Exception('lost must field:'."$lost");
        };

        $unKnow = array_diff($fields, array_merge($notMust, $must));
        if (!empty($unKnow)) {
            $unKnow = current($unKnow);
            throw new Exception('unKnow field:'."$unKnow");
        }
    }
}
