USAGE:


1.  Create parameters.yml file form parameters.yml.dist
2.  Save your credentials in parameters.yml
3.  Create your database with this command:

		   php -f migrate.php create

4.  Create your mysql command txt file.
    Each line must contain one executable mysql command.

    EXAMPLE:
    //migrate-v1.txt
    CREATE TABLE test (name VARCHAR(50) NOT NULL);
    INSERT INTO test ('Test Person');

    //migrate-v2.txt
    DROP TABLE test;

5.  Execute all commands from the txt file with this command:

           php -f migrate.php migrate your_file.txt
    


HOW IT WORKS:

PHP script go through your_file.txt line by line. It will extecute each mysql command with try-catch script. If there is an error in the mySql script then no changes will be made on the database schema.

Migrate table:
PHP script creates a migrate table in your schema and registers all migration file name and date. Recommended to keep all the 