<?php
namespace Task3;

class SplFileIterator implements \SeekableIterator {

    private $file;

    /**
     * SplFileIterator constructor.
     * @param $path string путь к итерируемому файлу
     */
    public function __construct($path)
    {
        if(empty($path)) {
            throw new \InvalidArgumentException('Аргумент path класса' . self::class . ' не должен быть пустым');
        }

        if(!file_exists($path)) {
            throw new \InvalidArgumentException('Неверный путь');
        }

        if(!is_readable($path)) {
            throw new \InvalidArgumentException('Путь недоступен для чтения');
        }

        $this->file = new \SplFileObject($path);
    }

    /**
     * Возвращает текущую строку
     * @return string
     */
    public function current()
    {
        return $this->file->current();
    }

    /**
     * Переводит указатель на следующую строку
     */
    public function next()
    {
        $this->file->next();
    }

    /*
     * Возвращает ключ текущей строки
     * @return int
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * Возвращает false если достигнут конец файла
     * @return bool
     */
    public function valid()
    {
        return $this->file->valid();
    }

    /**
     * Сбрасывает значение ключа в 0
     */
    public function rewind()
    {
        $this->file->rewind();
    }

    /**
     * Переводит указатель на указанный ключ
     * @param int $position позиция указателя
     */
    public function seek($position)
    {
        if(!is_int($position)) {
            throw new \InvalidArgumentException('Аргумент position функции ' . __FUNCTION__ . ' должен быть integer');
        }

        if($position <= 0) {
            throw new \InvalidArgumentException('Аргумент position функции ' . __FUNCTION__ . ' должен быть больше 0');
        }

        $this->file->seek($position);
    }
}

class FgetsFileIterator implements \SeekableIterator {

    private $file;
    private $position;
    private $current;

    /**
     * SplFileIterator constructor.
     * @param $path string путь к итерируемому файлу
     */
    public function __construct($path)
    {
        if(empty($path)) {
            throw new \InvalidArgumentException('Аргумент path класса' . self::class . ' не должен быть пустым');
        }

        if(!file_exists($path)) {
            throw new \InvalidArgumentException('Неверный путь');
        }

        if(!is_readable($path)) {
            throw new \InvalidArgumentException('Путь недоступен для чтения');
        }

        $this->file = @fopen($path, 'r');
        if(!$this->file) {
            throw new \UnexpectedValueException("Не удалось открыть файл");
        }

        $this->position = 1;
        $this->current = fgets($this->file);

    }

    /**
     * Деструктор. Закрываем дескриптор
     */
    public function __destruct()
    {
        fclose($this->file);
    }

    /**
     * Возвращает текущую строку
     * @return string
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Переводит указатель на следующую строку
     */
    public function next()
    {
        if(!$val = fgets($this->file)) {
            return false;
        }

        $this->current = $val;
        $this->position++;

        return true;
    }

    /*
     * Возвращает ключ текущей строки
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Возвращает false если достигнут конец файла.
     * @return bool
     */
    public function valid()
    {
        return !feof($this->file);
    }

    /**
     * Сбрасывает значение ключа в 0
     */
    public function rewind()
    {
        rewind($this->file);
        $this->position = 1;
    }

    /**
     * Переводит указатель на указанный ключ
     * @param int $position позиция указателя
     */
    public function seek($position)
    {
        if(!is_int($position)) {
            throw new \InvalidArgumentException('Аргумент position функции ' . __FUNCTION__ . ' должен быть integer');
        }

        if($position <= 0) {
            throw new \InvalidArgumentException('Аргумент position функции ' . __FUNCTION__ . ' должен быть больше 0');
        }

        $this->rewind();

        if($position == 1) {
            return;
        }

        //Избегаем использования $this->next для лучшей скорости поиска
        $pos = 1;
        while (($pos <= $position) && ($current = fgets($this->file))) {
            $pos++;
        }

        $this->current = $current;
        $this->position = $pos - 1;
    }
}


/**
 * Замеряем параметры поиска
 * @param \SeekableIterator $iterator
 * @param $seek
 * @return string
 */
function trackSearch(\SeekableIterator $iterator, $seek)
{
    $className = get_class($iterator);
    $before = memory_get_usage();
    $start = microtime(true);
    $iterator->seek($seek);
    if($iterator->key() != $seek) {
        throw new \OutOfBoundsException("Ключ {$seek} не существует");
    }
    $s = $iterator->current();
    $took = round(microtime(true) - $start, 2);
    $consumed = round((memory_get_usage() - $before) / 1024, 2);
    return "{$className} использовал {$consumed} Kb памяти, поиск строки №{$seek} занял {$took} с.<br/> строка: {$s} <br/><br/>";
}

//Протестировано на файле размером 2Gb и длиной 25974026 строк
//echo trackSearch(new \Task3\SplFileIterator("file.txt"), 25974026); //Task3\SplFileIterator использовал 8.5 Kb памяти, поиск строки №25974026 занял 1.49 с.
//echo trackSearch(new \Task3\FgetsFileIterator("file.txt"), 25974026); //Task3\FgetsFileIterator использовал 0.37 Kb памяти, поиск строки №25974026 занял 13.63 с.
