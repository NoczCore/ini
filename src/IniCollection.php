<?php
/**
 * NoczCore
 *
 * @licence https://opensource.org/licenses/MIT
 * @link http://noczcore.github.io
 */

namespace NoczCore\Ini;

/**
 * Class IniCollection
 * Advanced Array
 * @package NoczCore\Ini
 */
class IniCollection implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    private $_items = [];

    public function __construct(array $items)
    {
        $this->_items = $items;
    }

    public function set($key, $value)
    {
        $key = trim($key, '.');
        if (strpos($key, '.') !== false) {
            $array =& $this->_items;
            foreach (explode('.', $key) as $v)
                $array =& $array[$v];
            $array = $value;
        } else {
            $this->_items[$key] = $value;
        }
    }

    public function get($key)
    {
        $key = trim($key, '.');
        if ($this->has($key)) {
            $array =& $this->_items;
            if (strpos($key, '.') !== false) {
                foreach (explode('.', $key) as $v)
                    $array =& $array[$v];
                return $array;
            } else {
                return $this->_items[$key];
            }
        }
        return false;
    }

    public function has($key)
    {
        $key = trim($key, '.');
        if (strpos($key, '.') !== false) {
            $array =& $this->_items;
            foreach (explode('.', $key) as $v) {
                if (!isset($array[$v]))
                    return false;
                else
                    $array =& $array[$v];
            }
            return true;
        } else {
            return array_key_exists($key, $this->_items);
        }
    }

    public function remove($key)
    {
        $key = trim($key, '.');
        if ($this->has($key)) {
            $this->delete($this->_items, $key);
            return true;
        }
        return false;
    }

    private function delete(array &$array, $key)
    {
        $key = trim($key, '.');
        if (strpos($key, '.') !== false) {
            $explode = explode('.', $key);
            $this->delete($array[$explode[0]], $explode[1]);
        } else {
            unset($array[$key]);
        }
    }

    public function getAll()
    {
        return $this->_items;
    }

    public function rename($from, $to)
    {
        $from = trim($from, '.');
        $to = trim($to, '.');
        if ($this->has($from)) {
            $value = $this->get($from);
            $this->remove($from);
            $this->set($to, $value);
            return true;
        }
        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->_items = unserialize($serialized);
    }
}