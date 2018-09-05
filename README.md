Markdown 转 Html 或 PDF

## 安装

``` bash
$ composer require jormin/md-convert -vvv
```

## 通用响应

| 参数  | 类型  | 是否必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| success | bool | 是 | false：操作失败 true:操作成功 |
| message | string | 是 | 结果说明 |
| data | array | 否 | 返回数据 |


## 使用

### 转换Html

```php
/**
 * 转换Html
 *
 * @param string $mdFile Markdown源文件绝对路径，需要可读
 * @param string $savePath 转换后文件存储目录，需要可读可写，为空默认使用源文件目录
 * @param array $saveName 转换后文件名称，为空默认使用源文件名称
 * @return array
 */
Jormin\MDConvert\MDConvert::toHtml($mdFile, [$savePath, $saveName=null]);
```

### 转换PDF

```php
/**
 * 转换PDF
 *
 * @param string $mdFile Markdown源文件绝对路径，需要可读
 * @param string $savePath 转换后文件存储目录，需要可读可写，为空默认使用源文件目录
 * @param array $saveName 转换后文件名称，为空默认使用源文件名称
 * @return array
 */
Jormin\MDConvert\MDConvert::toPDF($mdFile, [$savePath, $saveName=null]);
```

## 参考扩展

1. [erusev/parsedown](https://github.com/erusev/parsedown)

2. [mpdf/mpdf](https://github.com/mpdf/mpdf)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
