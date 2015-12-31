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
 * Class IniReader
 * Read INI files.
 * @package NoczCore\Ini
 */
class IniReader
{

    /**
     * Parse INI file.
     * @param string $filename
     * @return bool|IniCollection
     * @throws IniReadingException
     */
    public function readFile($filename)
    {
        if (!is_string($filename))
            throw new IniReadingException('The expected type is string');

        if (!file_exists($filename) || !is_readable($filename))
            throw new IniReadingException(sprintf("The file %s doesn't exist or is not readable", $filename));

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

        if ($ini === false)
            throw new IniReadingException(sprintf('Impossible to read the file %s', $filename));

        return $this->readString($ini);
    }

    /**
     * Parse INI string.
     * @param string $string
     * @return IniCollection
     * @throws IniReadingException
     */
    public function readString($string)
    {
        if (!is_string($string))
            throw new IniReadingException('The expected type is string');

        // PHP 5.3.3, an empty line return is needed at the end.
        $string .= "\n";

        $data = @parse_ini_string($string, true);
        if ($data === false) {
            $e = error_get_last();
            throw new IniReadingException('Syntax error in INI configuration: ' . $e['message']);
        }

        $rawValues = @parse_ini_string($string, true, INI_SCANNER_RAW);

        return $this->toCollection($this->decode($data, $rawValues));
    }

    /**
     * Transform array to IniCollection.
     * @param array $array
     * @return IniCollection
     */
    private function toCollection(array $array)
    {
        $collection = new IniCollection([]);
        foreach ($array as $k => $v) {
            if (is_array($array[$k]))
                $collection->set($k, $this->toCollection($array[$k])->toArray());
            else
                $collection->set($k, $v);
        }
        return $collection;
    }

    /**
     * Detect and decode all value.
     * @param array|string $data
     * @param array|string $raw
     * @return bool|int|null|string
     */
    private function decode($data, $raw)
    {
        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                $v = $this->decode($v, $raw[$k]);
            }
            return $data;
        }

        $data = $this->decodeBoolean($data, $raw);
        $data = $this->decodeNull($data, $raw);

        if (is_numeric($data) && ((string)($data + 0) === $data))
            return $data + 0;

        if (is_string($raw) && empty($raw))
            return "";

        return $data;
    }

    /**
     * Detect and decode boolean value.
     * @param string $data
     * @param string $rawValue
     * @return bool
     */
    private function decodeBoolean($data, $rawValue)
    {
        if ($data === '1' && ($rawValue === 'true' || $rawValue === 'on' || $rawValue === 'yes'))
            return true;
        elseif (($data === '0' || empty($data)) && ($rawValue === 'false' || $rawValue === 'off' || $rawValue === 'no'))
            return false;
        return $data;
    }

    /**
     * Detect and decode null value.
     * @param string $data
     * @param string $rawValue
     * @return null
     */
    private function decodeNull($data, $rawValue)
    {
        if ($data === '' && $rawValue === 'null')
            return null;
        return $data;
    }
}
