1.  Create parameters.yml file form parameters.yml.dist
2.  Save your credentials in parameters.yml
3.  Create your database with this command:
		   php -f migrate.php create
4.  Create your mysql command txt file.
    Each line must contain one executable mysql command
5.  Execute all commands from the txt file with this command:
    php -f migrate.php migrate your_file.txt
    