<?php
/**
 * Класс для работы с базой данных<br>
 * Author: Alexey Pavlov<br>
 * Contact: vk.com/zytia
 */
class DataBase {
    private $connect;
    private $host = "";
    private $username = "";
    private $password = "";
    private $database = "";

    /**
     * DataBase конструктор.
     * Происходит подключение к базе данных
     * из указанных значений
     */
    public function __construct()
    {
        $this->connect = mysqli_connect("$this->host", "$this->username", "$this->password", "$this->database");
        mysqli_set_charset($this->connect, "utf8");
    }

    /**
     * Метод, возваращающий результат выборки из базы данных
     * @param $field_select - получаемая таблица в ходе выборки
     * @param $table - таблица, с которой работаем
     * @param $field_where - Поле для сверки
     * @param $data - Данные, которые сравниваем в поле
     * @return bool|mysqli_result  возвращает результат выборки
     */

    function query_select($field_select, $table, $field_where, $data){
        $field_select = $this->connect->real_escape_string($field_select);
        $table = $this->connect->real_escape_string($table);
        $field_where = $this->connect->real_escape_string($field_where);
        $data = $this->connect->real_escape_string($data);
        $query = mysqli_query($this->connect, "SELECT ".$field_select." FROM ".$table." WHERE ".$field_where." = '".$data."'");
        return $query;
    }

    /**
     *  Метод, возваращающий результат выборки из базы данных по двум сравнениям
     * @param $field_select - получаемая таблица в ходе выборки
     * @param $table - таблица, с которой работаем
     * @param $field_where1 - 1 Поле для сверки
     * @param $data1 - 1 Данные, которые сравниваем в поле
     * @param $field_where2 - 2 Поле для сверки
     * @param $data2 - 2 Данные, которые сравниваем в поле
     * @return bool|mysqli_result  возвращает результат выборки
     */

    function query_select2($field_select, $table, $field_where1, $data1, $field_where2, $data2){
        $field_select = $this->connect->real_escape_string($field_select);
        $table = $this->connect->real_escape_string($table);
        $field_where1 = $this->connect->real_escape_string($field_where1);
        $field_where2 = $this->connect->real_escape_string($field_where2);
        $data1 = $this->connect->real_escape_string($data1);
        $data2 = $this->connect->real_escape_string($data2);
        $query = mysqli_query($this->connect, "SELECT ".$field_select." FROM ".$table." WHERE ".$field_where1." = '".$data1."' AND ".$field_where2." = '".$data2."'");
        return $query;
    }

    /**
     * Метод, записывающий данные в базу
     * @param $field_select - Индекс поля из базы, откуда будут выбираться данные
     * @param $table - таблица, с которой работаем
     * @param $data - Данные, на которые ссылаемся из базы данных
     * @return bool|mysqli_result  возвращает результат вставки
     */

    function query_insert($field_select, $table, $data){
        $field_select = $this->connect->real_escape_string($field_select);
        $table = $this->connect->real_escape_string($table);
        $data = $this->connect->real_escape_string($data);
        $query = @mysqli_query($this->connect, "INSERT INTO '$table' SET '$field_select' = '$data'");
        return $query;
    }

    /**
     * Метод, удаляющий данные из базы
     * @param $table - таблица, с которой работаем
     * @param $field_where - Поле из строки, которую получили
     * @param $data - Данные, на которые ссылаемся из базы данных
     * @return bool|mysqli_result  возвращает результат удаления
     */

    function query_delete($table, $field_where, $data){
        $field_where = $this->connect->real_escape_string($field_where);
        $table = $this->connect->real_escape_string($table);
        $data = $this->connect->real_escape_string($data);
        $query = @mysqli_query($this->connect, "DELETE FROM '$table' WHERE '$field_where' = '$data'");
        return $query;
    }

    /**
     * Метод, обнавляющий данные в базе
     * @param $field_select - Индекс поля из базы, куда будут вписываться данные
     * @param $table - таблица, с которой работаем
     * @param $field_update - Поле, в котором будем обновлять данные
     * @param $data - Данные, на которые ссылаемся из базы данных
     * @param $data_to_update - данные, которые будем вписывать в таблицу
     * @return bool|mysqli_result возвращает результат обновления
     */
    function query_update($field_select, $table, $field_update, $data, $data_to_update){
        $field_select = $this->connect->real_escape_string($field_select);
        $table = $this->connect->real_escape_string($table);
        $field_update = $this->connect->real_escape_string($field_update);
        $data = $this->connect->real_escape_string($data);
        $data_to_update = $this->connect->real_escape_string($data_to_update);
        $query = mysqli_query($this->connect, "UPDATE `$table` SET `$field_select` = '$data_to_update' WHERE `$field_update` = '$data'");
        return $query;
    }

    /**
     * Метод для кастомного запроса(небезопасно!)
     * @param $sql - запрос
     * @return bool|mysqli_result возвращает результат запроса
     */

    function query($sql){
        $query = mysqli_query($this->connect, $sql);
        return $query;
    }
}


