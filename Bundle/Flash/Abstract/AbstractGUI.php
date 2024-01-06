<?php

namespace Module\Dashboard\Bundle\Flash\Abstract;

abstract class AbstractGUI
{
    final public function stringify(?string $context): string
    {
        $context = '`' . str_replace('`', "\\`", addslashes($context ?? '')) . '`';
        $context = str_replace("\n", "\\n", $context);
        return $context;
    }

    final public function validateJSCallback(?string $callback): ?string
    {
        $callbackName = "[a-z_\$](?:[a-z0-9_\$\.]*)"; // no-capture
        $callback = trim(preg_replace("/;*$/", '', trim($callback ?? '')));
        $valid = preg_match("/^{$callbackName}$/i", $callback);
        return $valid ? $callback : null;
    }

    final public function createJavascriptObject(array $array, ?int $input = 0): ?string
    {
        $depth = $input === null ? 0 : abs((int)$input);
        $nl = $input === null ? '' : "\n";

        $result = "{" . $nl;

        foreach ($array as $key => $value) 
        {
            $shift = $input === null ? 0 : $depth + 1;
            $indent = $input === null ? '' : str_repeat(" ", 4 * $shift);
            
            if (!is_array($value)) {
                $valueString = is_bool($value) ? json_encode($value) : $value;
                $result .= sprintf("%s\"%s\": %s, %s", $indent, $key, $valueString, $nl);
                continue;
            }

            $result .= sprintf(
                "%s\"%s\": " . $this->createJavascriptObject($value, $input === null ? null : $shift) . ", %s", 
                $indent, 
                $key, 
                $nl
            );
        }
    
        $result .= str_repeat("    ", $depth) . "}";

        return $result;
    }

    final protected function generateJSCallback(?string $callback, ?string $value): ?string
    {
        if($callback !== null) {
            $callback = $this->validateJSCallback($callback);
            if($callback) {
                $value = !empty($value) ? " " . $this->stringify($value) : ' undefined';
                return 'event => ' . $callback . "(event,{$value})";
            };
        }
        return null;
    }
}