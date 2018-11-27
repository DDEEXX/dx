<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.11.18
 * Time: 14:33
 */

abstract class listArray implements Iterator
{

    protected $list = array();

    /**
     * lists constructor.
     * @param array $list
     */
    public function __construct(array $list)
    {
        if (is_array($list)) {
            $this->list = $list;
        }
    }

    /**
     * Return the current element - возвращает текущий элемент
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->list);
    }

    /**
     * Return the key of the current element - возвращает ключ текущего элемента
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->list);
    }

    /**
     * Move forward to next element - передвигаемся вперед на один элемент
     * // возвращает текущий элемент
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->list);
    }

    /**
     * Checks if current position is valid - проверяет, достигли ли мы конца массива
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return current($this->list) !== false;
    }

    /**
     * Rewind the Iterator to the first element - возвращает указатель на начало списка
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->list);
    }

    /**
     * метод для добавления настройки в хранилище
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->list[$key] = $value;
        return $this;
    }

    /**
     * !!!d метод для получения настройки из хранилища
     * что возвращать если $key неопределен
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->list[$key];
    }

}

class listDevices extends ArrayIterator {

}

class selectOption extends listArray {

    /**
     * selectOption constructor.
     */
    public function __construct() {}
}