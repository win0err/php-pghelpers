<?php declare(strict_types=1);

if (!function_exists('pg_array_parse')) {
  /**
   * Decodes PostgreSQL arrays to PHP arrays
   *
   * @param string $string String to be decoded
   * @param int $start
   * @param null $end
   * @return array|null
   */
  function pg_array_parse(string $string, $start = 0, &$end = null): ?array
  {
    if (empty($string) || $string[0] !== '{') {
      return null;
    }

    $return = [];
    $isString = false;
    $quote = '';
    $len = strlen($string);
    $v = '';

    for ($i = $start + 1; $i < $len; $i++) {
      $character = $string[$i];

      if (!$isString && $character === '}') {
        if ($v !== '' || !empty($return)) {
          $return[] = $v;
        }
        $end = $i;
        break;
      }

      if (!$isString && $character === '{') {
        $v = pg_array_parse($string, $i, $i);
      } elseif (!$isString && $character === ',') {
        $return[] = $v;
        $v = '';
      } elseif (!$isString && ($character === '"' || $character === "'")) {
        $isString = true;
        $quote = $character;
      } elseif ($isString && $character === $quote && $string[$i - 1] === "\\") {
        $v = substr($v, 0, -1) . $character;
      } elseif ($isString && $character === $quote && $string[$i - 1] !== "\\") {
        $isString = false;
      } else {
        $v .= $character;
      }
    }

    return $return;
  }
}