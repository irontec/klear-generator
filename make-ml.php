#!/usr/bin/php
<?php
if (!is_file(dirname(__FILE__).'/config/config.php')) {
    die("please copy config/config.php-default to config/config.php and modify.");
}

require_once('config/config.php');

if (!ini_get('short_open_tag')) {
    die("please enable short_open_tag directive in php.ini\n");
}

if (!ini_get('register_argc_argv')) {
    die("please enable register_argc_argv directive in php.ini\n");
}

/*
	parameters:
    	--database : databaseName
 */

 if (!in_array("--database", $argv)) {

	die("error: please provide one database parameter\n");

 } else {

	$dbname = '';

	for ($i = 0; $i < count($argv); $i++) {

		if ($argv[$i] == '--database') {

			$i++;
			$dbname =  $argv[$i];
			break;
		}
	}
 }

 if (empty($config['db.socket'])) {

 	$pdoString = 'mysql:host='.$config['db.host'].';port='.$config['db.port'].';dbname=' .$dbname; 

 } else {

	$pdoString = 'mysql:unix_socket='.$config['db.host'].';dbname=' . $dbname;
 }

 try {

	$pdo = new PDO(
		$pdoString,
		$config['db.user'],
	    $config['db.password']
	);

 } catch (Exception $e) {
		
	die("error: Could not connect to database\n");
 }
 
 /***
  * Fetch tables names
  */
 
 $res = $pdo->query('show tables')->fetchAll();
 $tables = array();
 foreach ($res as $table) {
    $tables[]=$table[0];
 }

 $pdo->query("SET NAMES UTF8");

 foreach ($tables as $table) {

 	$createTable = $pdo->query("show create table `".$table."`");

	if (!$createTable) {

		throw new Exception("`describe $table` returned false!.");

	} else {
			
		$createTable = $createTable->fetchAll();
		$createTable = $createTable[0]['Create Table'];
		$createTable = explode("\n", $createTable);

		//Eliminamos la primera y última línea
		array_shift($createTable);
		array_pop($createTable);

		//Eliminar espacios en blanco innecesarios		
		foreach ($createTable as $key => $val) {

			$val = trim($val);
			
			if ($val{0} == "`") {

				$createTable[$key] = $val;				

			} else {
				
				unset($createTable[$key]);
			}
		}
	}

	$tableFields = array();
	$tableFieldsStr = array();
	$mlFields = array();

	foreach ($createTable as $line) {

		preg_match('/^`(\w+)`\s+/', $line, $fieldName);

		if (!empty($fieldName)) {

			$fieldName =  end($fieldName);
			$tableFields[] = $fieldName;
			$tableFieldsStr[$fieldName] = $line;
		}

		preg_match('/`(\w+)`.+COMMENT\s\'(.+)\'/',$line,$comment);

		if (!empty($comment)) {

			$field = $comment[1];
			$comment = end($comment);

			if ( strpos($comment, '[ML]') !== false) {

				$mlFields[] = $field;
			}
		}
	}

	if (count($mlFields) > 0) {

		foreach ($mlFields as $key => $field) {

			foreach ($config["app.languages"] as $language) {

				$newField = $field . '_' . $language;			

				if (! in_array($newField, $tableFields)) {

					$str = substr($tableFieldsStr[$field], 0, strpos($tableFieldsStr[$field], " COMMENT"));
					$str = substr($str, strlen($field) +3 );

					$query = "ALTER TABLE `".$table."` ADD `" . $newField . "` ".$str." AFTER `".$field."`";

					echo $query . ";\n";
					$pdo->query($query);
				}
			}
		}
	}
 }

 echo "done!\n";

