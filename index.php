<?php
/**
 * Автор: Забара Максим
 * Дата реализации: 01.05.2022
 * Дата изменения: 01.05.2022
 * MySql Server version: 8.0.28-0ubuntu0.20.04.3 (Ubuntu)
 */

include_once 'Person.php';
include_once 'People.php';

$oleg = new Person('Oleg', 'Ivanov', '1955-04-21', 1, 'Molodechno');
$tom = new Person('Tom', 'Smith', '1989-05-29', 1, 'Sietl');
$deril = new Person('Deril', 'Dixon', '1934-03-09', 1, 'Minsk');
$cara = new Person('Cara', 'Koul', '1997-03-22', 0, 'London');

$newDeril = $deril->formatGenderOrDate(true, true);
$oldman = new People('1960-01-01', '<');
print_r($oldman);
$oldman->deletePeopleByListId();
$mens = new People(1);
$arrayMens = $mens->getPeopleList();
print_r($arrayMens);