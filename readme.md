# Setting-up
Copy project repository
> git clone git@github.com:UldisLasmanis/Edurio.git 

Go to project root folder
> cd Edurio

Install composer dependencies
> composer install

Build and start Docker containers
> docker-compose up -d

Run Doctrine migration to create empty table `source` in database `foo`
> docker exec edurio_php /home/wwwroot/edurio/bin/console doctrine:migrations:execute "DoctrineMigrations\Version20201011172904"

# Using
To add 1M records to database, open:
> localhost:8080/add

To see JSON API, open:
> localhost:8080/dbs/foo/tables/source/json?page_size=SOME_VALUE&page=SOME_VALUE

To see CSV API, open:
> localhost:8080/dbs/foo/tables/source/csv

To run tests, run command (It will download packages on first run): 
> ./bin/phpunit

To see database, open:
> localhost:8081 


# Testing
Unfortunately I didnt manage to successfully run tests on Docker containers, 
because some additional PHPStorm configurations needs to be done in order for tests to run accordingly.
Tests can be executed outside Docker env.

Edit etc/hosts file:
> 127.0.0.1 localhost

Edit Apache2 available sites configurations at etc/apache2/sites-available:
> <VirtualHost *:80>
       ServerAdmin admin@example.com
       DocumentRoot /var/www/html/Edurio/public
       ServerName edurio.test
       ServerAlias edurio.test
  
       <Directory /var/www/html/Edurio/public/>
            AllowOverride All
            Options -Indexes +FollowSymLinks -MultiViews
            Order Allow,Deny
            Allow from All
       </Directory>
  
       ErrorLog ${APACHE_LOG_DIR}/error.log
       CustomLog ${APACHE_LOG_DIR}/access.log combined
  </VirtualHost>

Run this command, if `docker-compose up -d` was already executed:
> docker-compose down

Edit .env file in project root:
> DATABASE_URL=mysql://root:root@localhost:3306/foo?serverVersion=5.7

Run Doctrine migration to create empty table `source` in database `foo`:
> php bin/console doctrine:migrations:execute "DoctrineMigrations\Version20201011172904"

Fill `source` table with data:
> edurio.test/add

Run tests:
> ./bin/phpunit

 
