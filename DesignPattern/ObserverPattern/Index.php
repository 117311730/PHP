<?php
require_once("teacher.class.php");
require_once("student.class.php");
require_once("subject.class.php");

$subject = new Subject("Math");
$marcus = new Teacher("Marcus Brasizza");
$rafael = new Student("Rafael");
$vinicius = new Student("Vinicius");

// Include observers in the math Subject
$subject->attach($rafael);
$subject->attach($vinicius);
$subject->attach($marcus);

$subject2 = new Subject("English");
$renato = new Teacher("Renato");
$fabio = new Student("Fabio");
$tiago = new Student("tiago");

// Include observers in the english Subject
$subject2->attach($renato);
$subject2->attach($vinicius);
$subject2->attach($fabio);
$subject2->attach($tiago);

// Remove the instance "Rafael from subject"
$subject->detach($rafael);

// Notify both subjects
$subject->notify();
$subject2->notify();

echo "First Subject <br />";
echo "<pre>";
print_r($subject->GET_log());
echo "</pre>";
echo "<hr>";
echo "Second Subject <br />";
echo "<pre>";
print_r($subject2->GET_log());
echo "</pre>";