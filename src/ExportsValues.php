<?php

namespace dobron\LaravelDatabaseEnum;

/**
 * The exports values trait.
 *
 */
trait ExportsValues
{
    /**
     * Export the given value.
     *
     * @param mixed $value
     * @param int $initialPadding
     * @param int $incrementalPadding
     * @return string
     */
    protected function export($value, $initialPadding = 0, $incrementalPadding = 4)
    {
        if (is_array($value)) {
            return $this->exportArray($value, $initialPadding, $incrementalPadding);
        }

        return $this->exportScalar($value);
    }

    /**
     * Format the given scalar value to an exportable string.
     *
     * @param mixed $value
     * @return string
     */
    protected function exportScalar($value)
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return is_string($value) ? "'{$value}'" : "{$value}";
    }

    /**
     * Format the given array to an exportable string
     *
     * @param array $array
     * @param int $initialPadding
     * @param int $incrementalPadding
     * @return string
     */
    protected function exportArray(array $array, $initialPadding = 0, $incrementalPadding = 4)
    {
        $padding = $initialPadding + $incrementalPadding;
        $indentation = str_repeat(' ', $padding);
        $exported = [];

        foreach ($array as $key => $value) {
            $exportedKey = is_int($key) ? '' : "'{$key}' => ";
            $exportedValue = $this->export($value, $padding, $incrementalPadding);
            $exported[] = $indentation . $exportedKey . $exportedValue;
        }

        return "[\n" . implode(",\n", $exported) . ",\n" . str_repeat(' ', $initialPadding) . ']';
    }
}
