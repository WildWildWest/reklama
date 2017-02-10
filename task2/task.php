<?php
namespace Task2;

/**
 * Замеряет параметры поиска.
 * @param DomainSeeker $obj
 * @param $delimiter
 * @return array
 */
function trackSearch(DomainSeeker $obj, $delimiter)
{
    $className = get_class($obj);
    $start = microtime(true);
    $data = $obj->count();
    $took = round(microtime(true) - $start, 2);
    $consumed = round(memory_get_peak_usage() / 1024 / 1024, 2);
    echo "{$className} в пике использовал {$consumed} Mb памяти, поиск занял {$took} с. {$delimiter}";
    return $data;
}


/**
 * Возвращает PDO объект с подключением
 * @param $host
 * @param $name
 * @param $user
 * @param $pass
 * @return \PDO
 */
function getConn($host, $name, $user, $pass)
{
    try {
        return new \PDO("mysql:host={$host};dbname={$name}", $user, $pass);
    }
    catch(\PDOException $e) {
        exit($e->getMessage());
    }

}

/**
 * Сервис DomainSeeker осуществляющий подчест доменов
 * @package Task2
 * @property \PDO $db
 * @property int $limit Лимит записей за один запрос в базу.
 * @property string $table
 * @property string $field
 * @property \PDOStatement $querySelect
 */
class DomainSeeker {
    private $db;
    private $limit;
    private $querySelect;
    private $table;
    private $field;

    /**
     * DomainSeeker constructor.
     * @param \PDO $db
     * @param $limit
     * @param $table
     * @param $field
     */
    public function __construct(\PDO $db, $table = 'users', $field = 'email', $limit = 1000)
    {
        if(empty($db) || empty($table) || empty($field) || empty($limit)) {
            throw new \InvalidArgumentException('Аргументы db, table, field, limit функции ' . __FUNCTION__ . ' обязательны');
        }
        $this->db = $db;
        $this->limit = $limit;
        $this->table = $table;
        $this->field = $field;
        //Фильтрация по id позволяет избавиться от дорогостоящего OFFSET'а
        $this->querySelect = $this->db->prepare("SELECT id, {$this->field} FROM {$this->table} WHERE id > ? LIMIT {$this->limit}");
    }

    /**
     * Считает количество доменов
     * @return array
     */
    public function count()
    {
        $domains = [];

        $id = 1;
        while(($rows = $this->getRows($id)) && count($rows) > 0) {
            $this->countDomains($rows, $domains);
            $id = intval(array_pop($rows)['id']);
        }

        return $domains;
    }

    /**
     * Получает $this->limit записей после указанного id
     * @param $id
     * @return array
     */
    private function getRows($id)
    {
        if(!is_int($id)) {
            throw new \InvalidArgumentException('Аргумент id функции ' . __FUNCTION__ . ' должен быть integer');
        }

        $this->querySelect->execute([$id]);

        return $this->querySelect->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Считает количество запискей для n строк и записывает в результирующий массив
     * @param $rows
     * @param $domains
     */
    private function countDomains($rows, &$domains)
    {
        foreach($rows as $row) {
            if(empty($row[$this->field])){
                continue;
            }
            $emails = explode(',', $row[$this->field]);
            foreach($emails as $email) {
                $domain = $this->getDomain($email);
                @$domains[$domain]++;
            }
        }
    }

    /** Определяет домен email-а
     * @param $email
     * @return mixed
     */
    private function getDomain($email) {
        if(empty($email)) {
            throw new \InvalidArgumentException('Аргумент email функции ' . __FUNCTION__ . ' обязателен');
        }

        $parts = explode("@", $email);
        return $parts[1];
    }
}


$delimiter = isset($_SERVER['HTTP_USER_AGENT']) ? "<br/><br/>" : "\n";

$dbh = getConn('<host>', '<db_name>', '<user>', '<pass>');

//Поиск по таблице с 1 000 000 записей
$data = trackSearch(new DomainSeeker($dbh, 'users'), $delimiter); //Task2\DomainSeeker в пике использовал 1.65 Mb памяти, поиск занял 6.42 с.

foreach ($data as $domain => $cnt) {
    echo "{$domain}: {$cnt} {$delimiter}";
}