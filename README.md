# HTMLPurifier
 **描述**

 简单封装HTMLPurifier的富文本过滤器，自定义白名单机制，有效杜绝了用户提交表单中的非法HTML标签，从而可以防止XSS攻击！

 Laravel框架下的请参考：https://github.com/LukeTowers/Purifier

 **安装**

```php
   sudo composer require "yvan/purifier"
```

 **使用**

```php
$params = [
'one' => '<h1>one</h1>',
'two' => '<h1><script>alert(123);</script>two</h1>',
];
$purifier = new Purifier();
$result = $purifier->remove($params);
print_r($result);

`打印结果：`

Array
(
[one] => <p>one</p>
[two] => <p>two</p>
)
```

**白名单配置**

   `路径：config\purifier.php`

```php
    return [
        'encoding' => 'utf-8', //编码
        'finalize' => true, //固定
        'cachePath' => '/cache/purifier', //缓存路径
        'cacheFileMode' => 0755,
        'settings' => [
            'default' => [ //默认配置
                'HTML.Trusted' => true, //信任html元素，如form
                'AutoFormat.RemoveEmpty' => true, //移除空
            ],
            'help' => [ //自定义配置
                'HTML.AllowedElements' => 'p,span,img,a,strong,em,hr,br', //在受安全支持元素的范围下定义信任元素
                'CSS.AllowedProperties' => 'background,background-attachment,background-clip,background-color,background-image,background-origin,background-position,background-repeat,background-size,border,border-box,border-bottom,border-bottom-color,border-bottom-left-radius,border-bottom-right-radius,border-bottom-style,border-bottom-width,border-collapse,border-color,border-image,border-image-outset,border-image-repeat,border-image-slice,border-image-source,border-image-width,border-left,border-left-color,border-left-style,border-left-width,border-radius,border-right,border-right-color,border-right-style,border-right-width,border-spacing,border-style,border-top,border-top-color,border-top-left-radius,border-top-right-radius,border-top-style,border-top-width,border-width,box-decoration-break,box-shadow,box-sizing,box-snap,box-suppress,break-after,break-before,break-inside,caption-side,clear,color,color-interpolation-filters,display,display-inside,display-list,display-outside,font,font-family,font-feature-settings,font-kerning,font-language-override,font-size,font-size-adjust,font-stretch,font-style,font-synthesis,font-variant,font-weight,height,letter-spacing,lighting-color,list-style,list-style-image,list-style-position,list-style-type,margin,margin-bottom,margin-left,margin-right,margin-top,max-height,max-lines,max-width,min-height,min-width,padding,padding-bottom,padding-left,padding-right,padding-top,text-align,text-align-last,text-decoration,text-decoration-color,text-decoration-line,text-decoration-skip,text-decoration-style,text-emphasis,text-height,text-indent,text-justify,text-orientation,text-overflow,text-shadow,text-space-collapse,text-transform,text-underline-position,text-wrap,width,word-break,word-spacing,word-wrap,z-index,right,left,top,bottom,cursor,line-break,size',
                'AutoFormat.RemoveEmpty' => true,
            ],
        ],
        'custom_definition' => [//自定义不受支持的元素和属性
            'id' => 'definition_cache_id', //缓存ID、固定
            'rev' => 1,//自增值，固定
            'debug' => false,//false不打开调试，即不缓存
            'elements' => [//自定义元素
                ['section', 'Block', 'Flow', 'Common'],
                ['nav', 'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside', 'Block', 'Flow', 'Common'],
                ['header', 'Block', 'Flow', 'Common'],
                ['footer', 'Block', 'Flow', 'Common'],
                ['address', 'Block', 'Flow', 'Common'],
            ],
            'attributes' => [//自定义属性
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],
    ];
```

**备注**

   插件库默认只支持受信任的安全元素，其他元素需要在custom_definition选项里面添加，如：添加nav元素，在custom_definition.elements选项定义之后，若定义了HTML.AllowedElements选项还需把nav元素添加进去，表示允许信任这个元素。

   自定义元素的规则：http://htmlpurifier.org/docs/enduser-customize.html

   受信任的安全元素列表，其他需自定义：
```php
    //受信任的的元素
    'HTML.AllowedElements' => 'a,abbr,address,b,bdo,big,blockquote,br,caption,cite,code,col,colgroup,dd,del,div,dl,dt,em,font,h1,h2,h3,h4,h5,h6,hr,i,img,ins,li,ol,p,pre,s,small,span,sub,sup,strong,table,tbody,td,tfoot,th,thead,tr,tt,u,ul'

    //受信任的的属性
    'CSS.AllowedProperties' => 'background,background-attachment,background-color,background-image,background-position,background-repeat,border,border-bottom,border-bottom-color,border-bottom-style,border-bottom-width,border-collapse,border-color,border-left,border-left-color,border-left-style,border-left-width,border-right,border-right-color,border-right-style,border-right-width,border-spacing,border-style,border-top,border-top-color,border-top-style,border-top-width,border-width,caption-side,clear,color,font,font-family,font-size,font-style,font-variant,font-weight,height,letter-spacing,list-style,list-style-image,list-style-position,list-style-type,margin,margin-bottom,margin-left,margin-right,margin-top,padding,padding-bottom,padding-left,padding-right,padding-top,text-align,text-decoration,text-indent,text-transform,width,word-spacing'
```
