<?php

/* Запуск функции из командной строки:
 * php -f task.php filename key
 * где filename - имя текстового файла, key - значнение ключа
 */

function findValue($filename, $key) {

  try {
    if (!file_exists($filename)) {
      throw new Exception('Такого файла не существует!');
    }

    $file = fopen($filename, "r");
    /* Так как во время бинарного поиска мы читаем из файла, начиная со второй
     * записи после позиции, заданной функции fseek, то мы не сможем найти
     * первую запись во время бинарного поиска, поэтому вначале проверяем
     * первую запись напрямую.
     */
    fseek($file, 0);
    /* Данная функция предполагает длину записи до 10000 байтов. 
     * В назначенный срок не было возможности решить задачу без ограничений
     * на длину записи.
     */
    $data = fread($file, 10000);
    $firstLine = explode(chr(0x0A), $data)[0];
    $firstItem = explode(chr(0x09), $firstLine);
    $firstKey = $firstItem[0];
    if ($firstKey == $key) {
      echo $firstItem[1]."\n";
      return $firstItem[1];
    } else if ($firstKey > $key) {
      echo "Такого ключа нет в файле.\n";
      return null;
    }

    $low = 0;
    $fileSize = filesize($filename);
    $high = $fileSize - 1;

    while ($low < $high) {

      $mid = ($low + $high)/2;
      fseek($file, $mid);
      $data = fread($file, 10000);
      rewind($file);
      $lines = explode(chr(0x0A), $data);
      $midLine = $lines[1];
      $midItem = explode(chr(0x09), $midLine);
      $midKey = $midItem[0];

      if ($key === $midKey) {
        echo $midItem[1]."\n";
        return $midItem[1];
      } else if ($key < $midKey) {
        /* Новая верхняя граница поиска - перед началом проверенной записи. */
        $high = $mid + strlen($lines[0]);
      } else {
        fseek($file, $mid + strlen($lines[0]) + strlen($lines[1]) + 2);
        $data = fread($file, 10000);
        rewind($file);
        $nextLine = explode(chr(0x0A), $data)[0];
        $nextKey = explode(chr(0x09), $nextLine)[0];
        if (!empty($nextKey) && $key >= $nextKey) {
          /* Новая нижняя граница поиска - начало проверенной записи. */
          $low = $mid + strlen($lines[0]) + 1;
        } else {
          /* Если данная запись является последней или искомый ключ должен
           * находиться между данной и следующей записью, то искомого ключа
           * нет в файле, и поиск можно завершить.
           */
          break;
        }
      }
    }
    echo "Такого ключа нет в файле.\n";
    return null;

  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

$start_time = microtime(true);
findValue($argv[1], $argv[2]);
$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
echo "execution time: ".$execution_time." sec";
