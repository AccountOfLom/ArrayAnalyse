<?php

// +----------------------------------------------------------------------
// | Author: lom <lishalom@sian.com>
// +----------------------------------------------------------------------


class ArrayAnalyse
{

    private $data;

    /**
     * 分隔符
     * @var string
     */
    private $pathDelimiter = '.';

    /**
     * 入口方法
     * @param  array $data 待处理数组
     * @return ArrayAnalyse
     */
    public static function create(array $data)
    {
         return new static($data);
    }

    /**
     * 创建多维数组
     * @param array $pathArray 以地址数组的方式传入
     * @param string $pathDelimiter  键名分隔符号
     * @return ArrayAnalyse
     * @throws Exception
     */
    public static function builderArray(array $pathArray,$pathDelimiter)
    {
        $ins = self::create([]);
        $ins->setDelimiter($pathDelimiter);
        foreach ($pathArray as $key => $item)
        {
            $ins->setValue($key,$item);
        }
        return $ins;
    }

    /**
     * 构造方法
     * ArrayAnalyse constructor.
     * @param array $data 需处理数组
     */
    public function __construct(array $data)
    {
        $this->setArray($data);

    }


    /**
     * 键名分隔符设定
     * @param string $pathDelimiter 分隔符号
     * @return $this
     */
    public function setDelimiter($pathDelimiter)
    {
        $this->pathDelimiter = $pathDelimiter;
        return $this;
    }

    /**
     * 遍历数组每一个元素，并传入闭包进行处理并返回
     * @param $callback   闭包函数处理过程
     * @param bool $hasKey 是否显示原键名(相同键名元素将进行合并)
     * @param bool $viewPath 是否以地址的方式显示键名
     * @return $this
     * @throws Exception
     */
    public function map($callback,$hasKey = true,$viewPath = false)
    {
        $data = $this->getArray();
        $result = $this->mapping($data,$callback,$hasKey,$viewPath);
        $this->setArray($result);
        return $this;
    }

    /**
     * 得到处理后数组
     * @return mixed
     */
    public function getArray()
    {
        return $this->data;
    }

    /**
     * 设置数组对象
     * @param $data
     */
    public function setArray($data)
    {
        $this->data = $data;
    }

    /**
     * 遍历数组实现过程
     * @param array $data 待处理数组
     * @param $callback 闭包函数处理过程
     * @param bool $hasKey 是否使用原键名
     * @param bool $viewPath 是否以地址的方式显示键名
     * @param string $path 内部递归使用
     * @param array $result 内部递归使用
     * @return array|mixed
     * @throws Exception
     */
    private function mapping(array $data,$callback,$hasKey,$viewPath = false,$path = null,&$result = [])
    {
        if(!is_callable($callback))
        {
            throw new Exception('不是可执行函数');
        }
        foreach ($data as $key => $value)
        {
            if($viewPath)
            {
                $newKey = is_null($path) ? $key : $path.$this->pathDelimiter.$key;
            }
            else
            {
                $newKey = $key;
            }
            $callKey = is_null($path) ? $path : $path.$this->pathDelimiter.$key;
            $call = call_user_func($callback,$value,$key,$callKey);
            if($call)
            {
                if($hasKey)
                {
                    $result[$newKey] = isset($result[$newKey]) ?  array_merge_recursive((array) $result[$newKey],(array)$call) : $call;
                }
                else
                {
                    $result[] = $call;
                }
            }
            if(is_array($value))
            {
                $this->mapping($value,$callback,$hasKey,$viewPath,$newKey,$result);
            }
        }
        return $result;
    }

    /**
     * 根据自定义函数过滤数组
     * @param $callback  闭包函数处理过程
     * @param bool $hasKey 是否使用原键名
     * @param bool $viewPath 是否以地址的方式显示键名
     * @return $this
     * @throws Exception
     */
    public function filter($callback,$hasKey = true,$viewPath = false) {
        if (!is_callable($callback)) {
            throw new Exception('不是有效的回调函数');
        }
        $this->map(function ($value,$key,$path) use($callback){
            if(call_user_func($callback,$value,$key,$path))
            {
                return $value;
            }
        },$hasKey,$viewPath);
        return $this;
    }

    /**
     * 得到数组多有键名
     * @param string $search_value 根据键值查找键名
     * @param bool $viewPath 是否以地址的方式显示键名
     * @return $this
     * @throws Exception
     */
    public function getKeys($search_value = '',$viewPath = false)
    {
        $keys = $this->map(function ($value,$key) use ($search_value){
            if($search_value)
            {
                if(json_encode($search_value) == json_encode($value))
                {
                    return $key;
                }
            }
            else
            {
                return $key;
            }
        },true,$viewPath);
        $this->setArray(array_keys($keys->getArray()));
        return $this;
    }

    /**
     * 判断数组是否含有键名
     * @param string $keyPath 地址键名
     * @return bool
     * @throws Exception
     */
    public function hasKeys($keyPath)
    {
        $keys = $this->getKeys('',true);
        return in_array($keyPath,$keys);
    }

    /**
     * 合并数组
     * @param array $data 待合并数组
     * @param bool $recursive 是否递归合并
     * @return $this
     */
    public function mergeWith(array $data, $recursive = true) {
        if ($recursive) {
            $this->setArray(array_merge_recursive($this->getArray(), $data));
        } else {
            $this->setArray(array_merge($this->getArray(), $data));
        }
        return $this;
    }

    /**
     * 覆盖数组
     * @param array $data 待合并数组
     * @param bool $recursive 是否递归合并
     * @return $this
     */
    public function replaceWith(array $data, $recursive = true) {
        if ($recursive) {
            $this->setArray(array_replace_recursive($this->getArray(), $data));
        } else {
            $this->setArray(array_replace($this->getArray(), $data));
        }
        return $this;
    }

    /**
     * 根据键值自定义函数排序数组
     * @param $callback 闭包函数处理过程
     * @return $this
     * @throws Exception
     */
    public function userSortByValue($callback){
        if (!is_callable($callback)) {
            throw new Exception('不是有效的回调函数');
        }
        $array = $this->getArray();
        uasort($array, $callback);
        $this->setArray($array);
        $this->map(function ($value,$key,$path) use($callback){
            if(is_array($value))
            {
                uasort($value, $callback);
            }
           return is_null($path) ? $value : null;
        });
        return $this;
    }

    /**
     * 根据键名自定义函数排序数组
     * @param $callback 闭包函数处理过程
     * @return $this
     * @throws Exception
     */
    public function userSortByKey($callback){
        if (!is_callable($callback)) {
            throw new Exception('不是有效的回调函数');
        }
        $array = $this->getArray();
        uksort($array, $callback);
        $this->setArray($array);
        $this->map(function ($value,$key,$path) use($callback){
            if(is_array($value))
            {
                uksort($value, $callback);
            }
            return is_null($path) ? $value : null;
        });
        return $this;
    }

    /**
     * 在数组中根据条件取出一段值，并返回
     * @param int $offset  起始截取坐标；设置为负数则从末端截取
     * @param int $length  截取长度
     * @param bool $preserveKeys 保留键名还是重置键名;默认false重置键名
     * @return $this
     * @throws Exception
     */
    public function slice($offset, $length, $preserveKeys = false) {
        if (!is_numeric($offset)) {
            throw new Exception('参数offset不是有效数字');
        }
        if (!is_numeric($length)) {
            throw new Exception('参数length不是有效数字');
        }
        $array = array_slice($this->getArray(), $offset, $length, $preserveKeys);
        $this->setArray($array);
        return $this;
    }

    /**
     * 把数组分割为新的数组块
     * @param int $size 规定每个新数组包含多少个元素
     * @param bool $preserveKeys 默认false;每个结果数组使用从零开始的新数组索引
     * @return ArrayAnalyse
     * @throws Exception
     */
    public function chunk($size, $preserveKeys = false) {
        if (!is_numeric($size)) {
            throw new Exception('参数size不是有效数字');
        }
        $array = array_chunk($this->getArray(), $size, $preserveKeys);
        return new static($array);
    }

    /**
     * 根据地址键名地址获取对应的键值
     * @param string $keyPath 地址键名
     * @param mixed $defaultValue 如果取空，显示对应的默认值
     * @return mixed
     */
    public function getValue($keyPath, $defaultValue = null) {
        $array = $this->getArray();
        if (array_key_exists($keyPath, $array)) {
            return $array[$keyPath];
        }
        $keys = explode($this->pathDelimiter, $keyPath);
        do {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                break;
            }
            $value = $array[$key];
            if (!$keys) {
                return $value;
            }
            if (!is_array($value)) {
                break;
            }
            $array = $value;
        } while ($keys);
        return $defaultValue;
    }

    /**
     * 根据地址键名地址设置对应的键值
     * @param string $keyPath 地址键名
     * @param mixed $value 设置值
     * @return $this
     * @throws Exception
     */
    public function setValue($keyPath, $value) {
        $data = $this->getArray();
        $array = & $data;
        $keys = explode($this->pathDelimiter, $keyPath);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                $array[$key] = array();
            } elseif (!is_array($array[$key])) {
                throw new Exception(sprintf('%s 该路径对应的值不是数组',$key));
            }
            $array = & $array[$key];
        }
        $key = array_shift($keys);
        $array[$key] = $value;
        $this->setArray($data);
        return $this;
    }

    /**
     * 根据地址键名地址删除数组
     * @param string $keyPath 地址键名
     * @return $this
     * @throws Exception
     */
    public function remove($keyPath) {
        $data = $this->getArray();
        $array = & $data;
        $keys = explode($this->pathDelimiter, $keyPath);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!array_key_exists($key, $array)) {
                throw new Exception(sprintf('没有找到有效的键名'));
            } elseif (!is_array($array[$key])) {
                throw new Exception(sprintf('%s 该路径对应的值不是数组',$key));
            }
            $array = & $array[$key];
        }
        $key = array_shift($keys);
        if (!array_key_exists($key, $array)) {
            throw new Exception(sprintf('没有找到有效的键名'));
        }
        unset($array[$key]);
        $this->setArray($data);
        return $this;
    }

}
