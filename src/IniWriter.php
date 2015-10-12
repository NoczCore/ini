<?php
/**
 * This file is part of NoczCore/Ini.
 *
 * @author NoczCore <noczcore@gmail.com>
 * @link http://noczcore.github.io
 * @licence https://opensource.org/licenses/MIT
 */

namespace NoczCore\Ini;

/**
 * Class IniWriter
 * Write INI files.
 * @package NoczCore\Ini
 */
class IniWriter
{

    /**
     * Convert an array or an IniCollection to INI string in file.
     * @param string $filename
     * @param IniCollection|array $data
     * @param string $header Add header before INI content.
     * @throws IniWritingException
     */
    public function writeToFile($filename, $data, $header = '')
    {
        $ini = $this->writeToString($data, $header);
        if (!file_put_contents($filename, $ini))
            throw new IniWritingException(sprintf('Impossible to write to file %s', $filename));
    }

    /**
     * Convert an array or an IniCollection to INI string.
     * @param IniCollection|array $data
     * @param string $header Add header before INI content.
     * @return string
     * @throws IniWritingException
     */
    public function writeToString($data, $header = '')
    {
        if ($data instanceof IniCollection)
            $data = $data->toArray();
        else if (!is_array($data))
            throw new IniWritingException('The expected type is array or \NoczCore\Ini\IniCollection');

        $ini = '';
        if (!empty($header))
            $ini .= $header.PHP_EOL;
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                if (empty($v))
                    continue;
                $ini .= "[$k]" . PHP_EOL;
                foreach ($this->convertRecursive($v) as $key => $value) {
                    $last = explode('.', $key);
                    $last = end($last);
                    if (strpos($key, '.') !== false && is_numeric($last))
                        $key = substr($key, 0, -strlen('.' . $last)) . '[]';
                    $ini .= "$key = {$this->encode($value)}" . PHP_EOL;
                }
            } else {
                $ini .= "$k = {$this->encode($v)}" . PHP_EOL;
            }
        }
        return $ini;
    }

    /**
     * Transform a recursive array to a basic array with a point for the recursive keys.
     * @param array $array
     * @param null|string $parentKey
     * @return array
     */
    private function convertRecursive(array $array, $parentKey = null)
    {
        $return = [];
        if (!is_null($parentKey))
            $parentKey .= '.';

        foreach ($array as $k => $v) {
            if (is_array($v))
                $return[] = $this->convertRecursive($v, $parentKey . $k);
            else
                $return[$parentKey . $k] = $v;
        }
        return $this->array_flatten($return);
    }

    /**
     * Encode value.
     * @param mixed $value
     * @return string
     */
    private function encode($value)
    {
        if ($value === true)
            $value = "true";
        else if ($value === false)
            $value = "false";
        else if (is_string($value))
            $value = "\"$value\"";
        return $value;
    }

    /**
     * Flatten an array.
     * @param array $array
     * @return array
     */
    private function array_flatten(array $array)
    {
        $return = array();
        array_walk_recursive($array, function ($a, $k) use (&$return) {
            $return[$k] = $a;
        });
        return $return;
    }
}