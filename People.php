<?php
/**
 * Автор: Забара Максим
 * Дата реализации: 01.05.2022
 * Дата изменения: 01.05.2022
 * MySql Server version: 8.0.28-0ubuntu0.20.04.3 (Ubuntu)
 * Запрос создания бд: create table people(id int primary key, name varchar(100) not null, surname varchar(100) not null, bornDate date not null, gender tinyint not null, bornCity varchar(100) not null);
 */

try {
    if (!class_exists('Person')) {
        throw new Exception('Class Person not exists.');
    }
} catch (Exception $e) {
    echo "Exception: ", $e->getMessage(), "\n";
}
/**
 * Класс People
 * Имеет одно поле - массив с id людей
 * Конструктор ведет поиск id людей по всем полям бд
 * Имеет методы получения массива экземпляров класса Person в по полученым id вконструкторе и 
 * удаление людей из бд используя экземпляры класса Person.
 * Не должен объявляться если не объявлен класс Person.
 */

class People
{
    public array $arrayId;

    /**
     * Конструктор класса ведет поиск id людей по всем полям бд. 
     * Принимает два параметра:
     * 1.$value - по котрому ведется поиск, возвращает id записей если хоть одно поле равно $value.
     * 2.$condition(не обязательный) - может принимать значение ">", "<", "<>". Если параметр установлен,
     * ведется соответсвующий поиск только по дате(не совсем понятно условие задания)
     */

    public function __construct($value, $condition = null)
    {
        if ($condition != null && !preg_match('/^<$|^>$|^<>$/', $condition)) {
            throw new InvalidArgumentException('$condition must contain only "<", ">", "<>"');
        }
        $pdo = $this->getConnection();
        if ($condition == null) {
            $statment = $pdo->prepare('SELECT id FROM people 
                                WHERE name LIKE :name
                                OR surname LIKE :surname 
                                OR bornDate LIKE :bornDate 
                                OR gender LIKE :gender 
                                OR bornCity LIKE :bornCity');
            $statment->execute(array('name' => $value, 'surname' => $value, 'bornDate' => $value, 'gender' => $value, 'bornCity' => $value));
            $arrayId = $statment->fetchALL(PDO::FETCH_COLUMN);
            
            try {
                if (empty($arrayId)) {
                    throw new Exception('Empty set. Class instance not created');
                }
            } catch (Exception $e) {
                echo 'Exeption:', $e->getMessage(), "\n";
                die();
            }

            $this->arrayId = $arrayId;
        } else {
            $statment = $pdo->prepare("SELECT id FROM people WHERE bornDate $condition CAST(:date as datetime)");
            $statment->execute(array('date' => $value));
            $arrayId = $statment->fetchAll(PDO::FETCH_COLUMN);

            try {
                if (empty($arrayId)) {
                    throw new Exception('Empty set. Class instance not created');
                }
            } catch (Exception $e) {
                echo 'Exeption:', $e->getMessage(), "\n";
                die();
            }

            $this->arrayId = $arrayId;
        }
    }

    public function getConnection()
    {
        $option = array(PDO::ATTR_EMULATE_PREPARES   => false);
        return new PDO('mysql:host=localhost;dbname=test', 'root', 'password', $option);
    }

    public function getPeopleList()
    {   
        $arrayPeople = [];
        foreach($this->arrayId as $id) {
            $arrayPeople []= new Person('', '', '', 1, '', $id);
        }
        
        return $arrayPeople;
    }

    public function deletePeopleByListId()
    {
        $arrayPeople = $this->getPeopleList();

        foreach($arrayPeople as $person) {
            $person->deleteById();
        }
    }
}