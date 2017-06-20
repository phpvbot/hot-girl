# hot-girl
辣妹图服务，在 [妹子网](http://www.mmjpg.com/) 上抓取图片返回至微信。

## 安装

```
composer require vbot/hot-girl
```

## 扩展属性

```php
name: hot_girl
zhName: 辣妹图
author: JaQuan
```

## 扩展配置

```php
* image_path - 图片下载保存路径，默认值为项目微信用户目录下的 girls 目录内
* error_message - 服务异常时的提示

// 配置示范
'hot_girl' => [
    'image_path'    => 'girls/',
    'error_message' => '暂时无法为您提供服务',
],
```

## 触发关键字

妹子

## 扩展负责人

[JaQuan](https://github.com/springjk)

chinese.jk@gmail.com