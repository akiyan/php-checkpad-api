<?php
/**
 * Services_CheckPad Example Code
 * 
 * usage: php Services_CheckPad_Example_01.php {email...} {password..}
 */
error_reporting(E_ALL);
require_once 'Services/CheckPad.php';

if (isset($_SERVER['argv'][1]) == false || isset($_SERVER['argv'][2]) == false) {
    die("usage: php Services_CheckPad_Example_01.php {email...} {password...}\n\n");
}

$checkpad = new Services_CheckPad();
echo format_test_start_message("login");
$res = $checkpad->login($_SERVER['argv'][1], $_SERVER['argv'][2]);
if ($res !== true) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getListOfToDo", "1 of 3");
$listoftodo_before = $res = $checkpad->getListOfToDo();
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("addListOfToDo");
$new_listoftodo_id = $res = $checkpad->addListOfToDo('テスト用リスト作成');
if (!is_int($res)) {
  echo "Failed\n";
  exit;
}
echo "Success: new list of todo id is " . $new_listoftodo_id . "\n";

echo format_test_start_message("getListOfToDo", "2 of 3");
$listoftodo_after = $res = $checkpad->getListOfToDo();
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($listoftodo_after) - count($listoftodo_before) != 1) {
  echo "Failed: no added\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("editListOfToDoTitle");
$res = $checkpad->editListOfToDoTitle($new_listoftodo_id, 'テスト用リストタイトル変更');
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("editListOfToDoMemo");
$res = $checkpad->editListOfToDoMemo($new_listoftodo_id, 'メモ設定');
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("addToDo", "1 of 2");
$new_todo_id_1 = $res = $checkpad->addToDo($new_listoftodo_id, 'ほげ1');
if (!is_int($res)) {
  echo "Failed\n";
  exit;
}
echo "Success: new todo id is " . $new_todo_id_1 . "\n";

echo format_test_start_message("addToDo" ,"2 of 2");
$new_todo_id_2 = $res = $checkpad->addToDo($new_listoftodo_id, 'ほげ2');
if (!is_int($res)) {
  echo "Failed\n";
  exit;
}
echo "Success: new todo id is " . $new_todo_id_2 . "\n";

echo format_test_start_message("getToDoListNotYet", "1 of 5");
$res = $checkpad->getToDoListNotYet($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 2) {
  echo "Failed: amount is not 2\n";
}
echo "Success\n";

echo format_test_start_message("finishToDo", "1 of 2");
$res = $checkpad->finishToDo($new_todo_id_1);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListNotYet", "2 of 5");
$res = $checkpad->getToDoListNotYet($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 1) {
  echo "Failed: amount is not 1\n";
}
echo "Success\n";

echo format_test_start_message("finishToDo", "2 of 2");
$res = $checkpad->finishToDo($new_todo_id_2);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListNotYet" ,"3 of 5");
$res = $checkpad->getToDoListNotYet($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 0) {
  echo "Faild: amount is not 0\n";
}
echo "Success\n";

echo format_test_start_message("getToDoListDone", "1 of 2");
$res = $checkpad->getToDoListDone($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 2) {
  echo "Faild: amount is not 0\n";
}
echo "Success\n";

echo format_test_start_message("unfinishToDo");
$res = $checkpad->unfinishToDo($new_todo_id_1);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListDone", "1 of 2");
$res = $checkpad->getToDoListDone($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 1) {
  echo "Faild: amount is not 1\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListNotYet", "4 of 5");
$res = $checkpad->getToDoListNotYet($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 1) {
  echo "Notice: amount is not 1\n";
} else {
  echo "Success\n";
}

echo format_test_start_message("getToDoList");
$res = $checkpad->getToDoList($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) == 0) {
  echo "Failed: amount is zero\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("editToDo");
$res = $checkpad->editToDo($new_todo_id_1, 'ふが変更');
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("delNotYetToDo");
$res = $checkpad->delNotYetToDo($new_todo_id_1);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListNotYet", "5 of 5");
$res = $checkpad->getToDoListNotYet($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 0) {
  echo "Failed: amount is not 0\n";
}
echo "Success\n";

echo format_test_start_message("delDoneToDo");
$res = $checkpad->delDoneToDo($new_todo_id_2);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("getToDoListDone", "2 of 2");
$res = $checkpad->getToDoListDone($new_listoftodo_id);
if (!is_array($res)) {
  echo "Failed\n";
  exit;
}
if (count($res) != 0) {
  echo "Faild: amount is not 0\n";
  exit;
}
echo "Success\n";

echo format_test_start_message("delListOfToDo");
$res = $checkpad->delListOfToDo($new_listoftodo_id);
if (PEAR::isError($res)) {
  echo "Failed\n";
  exit;
}
if (count($listoftodo_before) == $res) {
  echo "Faild: no deleted\n";
  exit;
}
echo "Success\n";

function format_test_start_message($title, $numofnum = '')
{
    echo sprintf(
      '%-20s %-6s ... '
      , $title
      , $numofnum
    );
}
?>