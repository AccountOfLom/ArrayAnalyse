# ArrayAnalyse


## php 任意维度数组处理类

---

## * 测试数据
```

```

## 1、设置待处理数组

```php     
 /**
 * 入口方法
 * @param  array $data 需处理数组
 * @return ArrayPlus
 */
 public static function create(array $data) {}
```

```php
ArrayPlus::create($data);
```

## 2、传入闭包方法，递归处理每个元素
```php    
/**
 * 遍历数组每一个元素，并传入闭包进行处理并返回
 * @param $callback   闭包函数处理过程
 * @param bool $hasKey 是否显示原键名(相同键名元素将进行合并)
 * @param bool $viewPath 是否以地址的方式显示键名
 * @return $this
 * @throws Exception
 */
public function map($callback,$hasKey = true,$viewPath = false) {}
```

```php
ArrayPlus::create($data);
```




