<?php
/**
 * NoczCore
 *
 * @licence https://opensource.org/licenses/MIT
 * @link http://noczcore.github.io
 */

namespace NoczCore\Ini;

/**
 * Class IniReader
 * Read INI files
 * @package NoczCore\Ini
 */
class IniReader
{

    public $advanced_parsing = true;

    public function readFile($filename)
    {
        if (function_exists('file_get_contents')) {
            $ini = file_get_contents($filename);
        } elseif (function_exists('file')) {
            $ini = file($filename);
            if ($ini !== false)
                $ini = implode("\n", $ini);
        } elseif (function_exists('fopen') && function_exists('fread')) {
            $handle = fopen($filename, 'r');
            if (!$handle)
                return false;
            $ini = fread($handle, filesize($filename));
            fclose($handle);
        } else {
            return false;
        }
        return $this->readString($ini);
    }

    public function readString($string)
    {

        // PHP 5.3.3, an empty line return is needed at the end.
        $string .= "\n";

        $data = @parse_ini_string($string, true);
        $rawValues = @parse_ini_string($string, true, INI_SCANNER_RAW);

        $array = $this->decode($data, $rawValues);

        return $this->matchCollection($array);
    }

    private function matchCollection($array)
    {
        $collection = new IniCollection([]);
        foreach ($array as $k => $v) {
            if (is_array($array[$k]))
                $collection->set($k, $this->matchCollection($array[$k])->getAll());
            else
                $collection->set($k, $v);
        }
        return $collection;
    }

    private function decode($data, $raw)
    {
        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                if (strpos($k, '.') !== false) {

                }
                $v = $this->decode($v, $raw[$k]);
            }
            return $data;
        }

        $data = $this->decodeNull($data, $raw);
        $data = $this->decodeBoolean($data, $raw);

        if (is_numeric($data) && ((string)($data + 0) === $data))
            return $data + 0;

        return $data;
    }

    private function decodeBoolean($data, $rawValue)
    {
        if ($data === '1' || ($rawValue === 'true' || $rawValue === 'on' || $rawValue === 'yes'))
            return true;
        elseif ($data === '0' || ($rawValue === 'false' || $rawValue === 'off' || $rawValue === 'no'))
            return false;
        return $data;
    }

    private function decodeNull($data, $rawValue)
    {
        if ($data === '' || $rawValue === 'null')
            return null;
        return $data;
    }
}