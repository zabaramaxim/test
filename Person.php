<?php
/**
 * Автор: Забара Максим
 * Дата реализации: 01.05.2022
 * Дата изменения: 01.05.2022
 * MySql Server version: 8.0.28-0ubuntu0.20.04.3 (Ubuntu)
 * Запрос создания бд: create table people(id int primary key, name varchar(100) not null, surname varchar(100) not null, bornDate date not null, gender tinyint not null, bornCity varchar(100) not null);
 */
/**
 * Класс People
 * Имеет поля - id, имя, фамилия, дата рождения, пол, город
 * Сохроняет в бд поля экземпляра класса, удаление человека по id объекта,
 * статические функции преобразования пола из двоичной системы в строку (0 - жен., 1 - муж.) и даты в возраст,
 * форматирование человека с преобразованеи пола и (или) возраста.
 * 
 */

class Person
{
    public $id;
    public $name;
    public $surname;
    public $bornDate;
    public $gender;
    public $bornCity;

    /**
     * Конструктор класа в зависимость от преданых параметров:
     * 1. Переданы все параметры кроме id - создает новую запись в бд с заданой информацией
     * 2. Переданы все параметры - проверят есть ли такой id в бд. Если есть создает экземпляр класса по полям из бд,
     * если нет - создает запись в бд с предаными параметрами.
     */

    public function __construct(string $name, string $surname, string $bornDate, int $gender, string $bornCity, $id = null)
    {   
        $pdo = $this->getConnection();
        $statment = $pdo->prepare('SELECT * FROM people WHERE id = :id');
        $statment->bindValue('id', $id); 
        $statment->execute();
        $person = $statment->fetch(PDO::FETCH_LAZY);

        if (empty($person)) {
            Person::validate($name, $surname, $bornDate, $gender, $bornCity, $id);
            $statment = $pdo->query('SELECT id FROM people ORDER BY id DESC LIMIT 1');
            $lastId = $statment->fetch(PDO::FETCH_LAZY); 
            
            if (empty($lastId)) {
                $id = 1;
            } else {
                $id = $lastId['id'] + 1;
            }
            
            $this->id = $id;
            $this->name = $name;
            $this->surname = $surname;
            $this->bornDate = $bornDate;
            $this->gender = $gender;
            $this->bornCity = $bornCity;
            $this->saveMan();
        } else {
            $this->id = $person->id;
            $this->name = $person->name;
            $this->surname = $person->surname;
            $this->bornDate = $person->bornDate;
            $this->gender = $person->gender;
            $this->bornCity = $person->bornCity;
        }
    }

    private static function validate($name, $surname, $bornDate, $gender, $bornCity, $id)
    {
        if ($id != null && $id <= 0) {
            throw new InvalidArgumentException('$id must be greater than zero');
        }

        if (!preg_match('/^[A-Za-z]{1}[a-z]*[a-z]{1}$/', $name)) {
            throw new InvalidArgumentException('$name must contain only latin letters');
        }

        if (!preg_match('/^[A-Za-z]{1}[a-z]*[a-z]{1}$/', $surname)) {
            throw new InvalidArgumentException('$surname must contain only latin letters');
        }

        if ($gender < 0 || $gender > 1){
            throw new InvalidArgumentException('$gender should consists of 0 or 1 only, (1 - male, 0 - female)');
        }

        if (!preg_match('/^[A-Za-z]{1}[a-z]*[a-z]{1}$/', $bornCity)) {
            throw new InvalidArgumentException('$bornCity must contain only latin letters');
        }
        
    }

    public function getConnection()
    {
        $option = array(PDO::ATTR_EMULATE_PREPARES   => false);
        return new PDO('mysql:host=localhost;dbname=test', 'root', 'password', $option);
    }

    private function saveMan()
    {
        $pdo = $this->getConnection();
        $statment = $pdo->prepare('INSERT INTO people(id , name, surname, bornDate, gender, bornCity) VALUES(:id, :name, :surname, CAST(:date as datetime), :gender, :city)');
        $statment->execute(array('id'       => $this->id,
                             ':name'    => $this->name,
                             ':surname' => $this->surname,
                             ':date'    => $this->bornDate,
                             ':gender'  => $this->gender,
                             ':city'    => $this->bornCity));

    }

    public function deleteByID()
    {
        $pdo = $this->getConnection();
        $statment = $pdo->prepare('DELETE FROM people WHERE id = :id');
        $statment->bindValue('id', $this->id);
        $statment->execute();
    }

    public static function bornDateToAge(string $bornDate)
    {
        $bornDate = DateTime::createFromFormat('Y-m-d', $bornDate);
        $curentDate = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        $interval = $curentDate->diff($bornDate);
        $age = $interval->y;
        return $age;
    }

    public static function  genderTOString($gender)
    {
        if ($gender == 0) {
            return 'Female';
        } elseif ($gender == 1) {
            return 'Male';
        }
    }

    public function formatGenderOrDate(bool $gender, bool $age)
    {
        if (!$gender && !$age) {
            throw new InvalidArgumentException('arguments cannot be false at the same time');
        }

        $personArray = (array)$this;

        if ($gender) {
            $personArray['gender'] = Person::genderTOString($personArray['gender']);
        }

        if ($age) {
            $personArray['bornDate'] = Person::bornDateToAge($personArray['bornDate']);
            $personArray['age'] = $personArray['bornDate'];
            unset($personArray['bornDate']);
        }

        return (object)$personArray;
    }
}

