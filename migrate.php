<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

if (!file_exists('parameters.yml')) {
    echo "The file parameters.yml does not exist!\nCreate this file form parameters.yml.dist\nand save your database credentials!\n";
    die;
}

$value = Yaml::parse(file_get_contents('parameters.yml'));

$db_host = $value['parameters']['database_host'];
$db_name = $value['parameters']['database_name'];
$db_user = $value['parameters']['database_user'];
$db_port = $value['parameters']['database_port'];
$db_password = $value['parameters']['database_password'];


$db = new mysqli(
        $db_host, $db_user, $db_password
);

//check connection
if ($db->connect_errno) {
    echo $db->connect_error;
    die;
}

//get run parameters
switch ($argv[1]) {
    case 'create':
        createDatabase($db_name, $db);
        break;
    case 'mig-table':
        createMigrateTable($db_name, $db);
        break;
    case 'migrate':
        migrateDatabase($db_name, $db, $argv);
        break;
    default:
        echoHelper();
}

function createDatabase($db_name, $db) {
    echo "Create database\n";
    $sql = 'CREATE DATABASE IF NOT EXISTS ' . $db_name;
    $result = $db->query($sql);
    $db->select_db($db_name);
    $migrate_table = 'CREATE TABLE migrate (id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, name VARCHAR(50) NOT NULL, created DATETIME NOT NULL) ENGINE=InnoDB;';
    $resultMigrate = $db->query($migrate_table);
    if (!$resultMigrate) {
        echo "Error in creating migrate table\n";
        echo $db->error;
        die;
    }
}

function echoHelper() {
    echo "Usage of PHP MYSQL Migration:\n\n***********************\n\n";
    echo "1.  Create parameters.yml file form parameters.yml.dist\n\n";
    echo "2.  Save your credentials in parameters.yml\n\n";
    echo "3.  Create your database with this command:\n    php -f migrate.php create\n\n";
    echo "4.  Create your mysql command txt file.\n    Each line must contain one executable mysql command\n\n";
    echo "5.  Execute all commands from the txt file with this command:\n    php -f migrate.php migrate your_file.txt\n\n";
    echo "***********************\n\n";
    die;
}


function createMigrateTable($db_name, $db) {
    echo "Create migrate table\n";
    $db->select_db($db_name);
    $sql = 'CREATE TABLE migrate (id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, name VARCHAR(50) NOT NULL, created DATETIME NOT NULL) ENGINE=InnoDB;';
    $result = $db->query($sql);
}

function migrateDatabase($db_name, $db, $argv) {
    $file = $argv[2];
    echo "Migrating table(s) " . $file . "\n";
    $db->select_db($db_name);
    $pathToFile = __DIR__ . '/' . $file;
    if (file_exists($pathToFile)) {
        //get file content lineByLine and store it in array
        $h = fopen($pathToFile, 'r');
        if ($h) {
            while (($line = fgets($h)) !== false) {
                $transactions[] = trim($line);
            }
            if (!feof($h)) {
                echo "Oops: fgets returned false before end of file";
            }
            fclose($h);
        }
    } else {
        echo "The file $pathToFile does not exist\n";
        die;
    }

    //Try the migration, if error occurs roll back the whole process
    try {
        $db->autocommit(FALSE);
        foreach ($transactions as $tr) {
            $result = $db->query($tr);
            if ($result === false) {
                throw new Exception($db->error);
            }
        }
        $db->commit();
        $db->autocommit(TRUE);
        echo "Migration successful!\n";
        //register the migration if mig table exist
        registerMigration($db_name, $file, $db);
    } catch (Exception $ex) {
        $db->rollback();
        $db->autocommit(TRUE);
        echo $ex;
    }
}

function registerMigration($db_name, $file, $db) {
    $db->select_db($db_name);
    //$sql = "INSERT INTO migrate (name, created) VALUES (" . $file . ", NOW());";
    $sql = "INSERT INTO migrate (name, created) VALUES ('$file' , NOW());";
    $result = $db->query($sql);
    if (!$result) {
        echo $db->error;
    } else {
        echo "Migration registered in migrate table\n";
    }
}
