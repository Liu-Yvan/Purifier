<?php

namespace Yvan\Purifier;

/**
 * Html过滤
 * User: yvan
 * Date: 2016/12/7
 * Time: 13:46
 */

use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;

class Purifier {
    /**
     * HTMLPurifier
     * @var object
     */
    public $htmlPurifier = null;

    /**
     * 配置数组
     * @var array
     */
    public $config = [];

    /**
     * Purifier constructor.
     * @param string $configPath 配置文件路径
     */
    public function __construct($configPath = '') {

        $path = !empty($configPath) ? $configPath : realpath(__DIR__ . '/../config/purifier.php');

        $this->config = require $path;
    }

    /**
     * 过滤函数
     * 备注：当内容含有图片地址时，不需要转义，否则无法识别图片地址
     * @param $params
     * @param string $configName
     * @return string|string[]
     */
    public function remove($params, $configName = '') {

        $htmlPurifier = $this->getInstance($configName);

        return is_array($params) ? $htmlPurifier->purifyArray($params) : $htmlPurifier->purify($params);
    }

    /**
     * 获取实例化
     * @param string $configName
     * @return HTMLPurifier|object
     */
    private function getInstance($configName = '') {
        if (!$this->htmlPurifier || $configName) {
            $this->htmlPurifier = $this->setUp($configName);
        }
        return $this->htmlPurifier;
    }

    /**
     * 检查/创建缓存目录
     */
    private function checkCacheDirectory() {
        $cachePath = $this->config['cachePath'];

        if ($cachePath) {
            if (!file_exists($cachePath)) {
                $mode = $this->config['cacheFileMode'];
                @mkdir($cachePath, $mode, true);
            }
        }
    }

    /**
     * 获取配置
     * @param null $configName
     * @return array
     */
    protected function getConfig($configName = null) {

        $default_config = [];
        $default_config['Core.Encoding'] = $this->config['encoding'];
        $default_config['Cache.SerializerPath'] = $this->config['cachePath'];
        $default_config['Cache.SerializerPermissions'] = $this->config['cacheFileMode'];

        if (!$configName) {
            $defined_config = $this->config['settings']['default'];
        } elseif (is_string($configName)) {
            $defined_config = $this->config['settings'][$configName];
        }

        if (!is_array($defined_config)) {
            $defined_config = [];
        }

        $merge_config = $default_config + $defined_config;

        return $merge_config;
    }

    /**
     * 客户自定义
     * @param array $definitionConfig
     * @param null $configObject
     * @return HTMLPurifier_Config|null
     */
    private function addCustomDefinition(array $definitionConfig, $configObject = null) {

        if (!$configObject) {
            $configObject = HTMLPurifier_Config::createDefault();
            $configObject->loadArray($this->getConfig());
        }

        // 加载客户定义
        $configObject->set('HTML.DefinitionID', $definitionConfig['id']);
        $configObject->set('HTML.DefinitionRev', $definitionConfig['rev']);

        // 调试模式清空缓存
        if (isset($definitionConfig['debug']) && $definitionConfig['debug']) {
            $configObject->set('Cache.DefinitionImpl', null);
        }

        // 自定义配置
        if ($def = $configObject->maybeGetRawHTMLDefinition()) {
            // 定义属性
            if (!empty($definitionConfig['attributes'])) {
                $this->addCustomAttributes($definitionConfig['attributes'], $def);
            }
            // 定义元素
            if (!empty($definitionConfig['elements'])) {
                $this->addCustomElements($definitionConfig['elements'], $def);
            }
        }

        return $configObject;
    }

    /**
     * 添加自定义属性
     * @param array $attributes
     * @param $definition
     * @return mixed
     */
    private function addCustomAttributes(array $attributes, $definition) {

        foreach ($attributes as $attribute) {
            $required = (isset($attribute[3]) && !empty($attribute[3])) ? true : false;
            $onElement = $attribute[0];
            $attrName = $required ? $attribute[1] . '*' : $attribute[1];
            $validValues = $attribute[2];

            $definition->addAttribute($onElement, $attrName, $validValues);
        }

        return $definition;
    }

    /**
     * 添加自定义元素
     * @param array $elements
     * @param $definition
     * @return mixed
     */
    private function addCustomElements(array $elements, $definition) {

        foreach ($elements as $element) {
            $name = $element[0];
            $contentSet = $element[1];
            $allowedChildren = $element[2];
            $attributeCollection = $element[3];
            $attributes = isset($element[4]) ? $element[4] : null;

            if (!empty($attributes)) {
                $definition->addElement($name, $contentSet, $allowedChildren, $attributeCollection, $attributes);
            } else {
                $definition->addElement($name, $contentSet, $allowedChildren, $attributeCollection);
            }
        }

        return $definition;
    }

    /**
     * 启动
     */
    private function setUp($configName) {

        // 获取配置
        if (empty($this->config)) {
            throw new Exception('Configuration parameters not loaded!');
        }

        // 检查缓存目录
        $this->checkCacheDirectory();

        // 创建默认的配置对象
        $configObject = HTMLPurifier_Config::createDefault();

        // 允许配置修改
        if (!$this->config['finalize']) {
            $configObject->autoFinalize = false;
        }

        $configObject->loadArray($this->getConfig($configName));

        // 加载自定义
        if ($definitionConfig = $this->config['custom_definition']) {
            $this->addCustomDefinition($definitionConfig, $configObject);
        }

        $this->htmlPurifier = new HTMLPurifier($configObject);

        return $this->htmlPurifier;
    }

}

