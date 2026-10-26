## README
### VERY simple timeclock routine.

### <u>How do I get set up?</u>  

### Database and account
if you wish to change the name of the database, the ip address, the account or the password  
you must also change the constant DB_NAME in file timeclock/include/connect.php
``` 
create database mdr_clock;
use mdr_clock;
GRANT ALL PRIVILEGES ON timeclock.* TO 'chronos'@'localhost' IDENTIFIED BY 'password';
```
The file timeclock/include/connect_utilities.php is for the admin users that run the site.  
The 2 are separate classes so you can have the admins users in another database if you wish.  

Engine for table employee must be MyISAM if MySQL version is < 5.6  

### Table 1 - employee  
This table contains all of the employee information
BARCODE and LNAME are the only required fields

````
drop table if exists employee;
create table employee(
  employeeid  integer unsigned  not null   auto_increment,
  is_active   boolean           not null   default 1,
  barcode     char(12)          not null,
  fname       varchar(30),
  lname       varchar(30)       not null,
  email       varchar(50),
  add1        varchar(30),
  add2        varchar(30),
  city        varchar(30),
  st          varchar(2),
  zip         varchar(10),
  phone       varchar(10),
  social      varchar(10),
  hire_date   timestamp         not null   default now(),
  group_id    varchar(30)       DEFAULT NULL,
  emergency_number  varchar(10) DEFAULT NULL,
  emergency_contact varchar(50) DEFAULT NULL,
  primary key(employeeid),
  unique key (barcode)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
````

### Table 2 - stamp
This table contains all of the timestamps.  
This is populated by program unless you have some history to add.  
```
drop table if exists stamp;
create table stamp (
  id            bigint  unsigned  not null  auto_increment,
  employeeid    integer unsigned  not null,
  punch         timestamp         not null  default now(),
  primary key(id),
  index(employeeid),
  index(punch),
  CONSTRAINT FK_id FOREIGN KEY (employeeid) REFERENCES employee(employeeid) 
) ENGINE=Innodb DEFAULT CHARSET=utf8;
```

### Table 3 - groups
This is for groups if you want to divide up the payroll  
MUST HAVE at least one record.  
```
drop table if exists groups;
create table groups(
  id            integer unsigned     not null     auto_increment,
  groupname     varchar(30)          not null,
  primary key(id),
  index(groupname)
) ENGINE=Innodb DEFAULT CHARSET=utf8;
``` 

### Table 4 - users
These are the users that can log into site and print reports  
The default user is 'admin' with a default password 'tc#egp2017!'   
```
DROP TABLE IF EXISTS users;
CREATE TABLE users (
	userid          int(11)     NOT NULL AUTO_INCREMENT,
	username        varchar(50) NOT NULL,
	hashed_password varchar(60) NOT NULL,
	fname           varchar(20),
	lname           varchar(20),
	PRIMARY KEY (userid)
)	ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

INSERT INTO users ( username, hashed_password, fname, lname ) VALUES 
('admin', '$2y$10$YWIyMDc3YWJkNzg0OGJiO.XajNA7hN8/YuihmMh19vDHTmBGR3qH.', '',       ''), 
```

Enter employees in table, the minimum information is barcode and lastname.
The barcode length is set to 12. You can change that requirement in timeclock/admin/add_employee.php.
I suggest you use checksum on the barcode to avoid reading errors.  

They are now ready to use timeclock.    

The username and passwords class (utilities) is separate from the MySQLiDatabase class 
so you can setup the accounts in another database.
If you are using Active Directory you can use LDAP class to login using your windows account.

If you change the encryption settings  
1 - login as admin  
2 - change the encryption settings (don't exit or quit session)  
3 - create a new account, this password will have your new settings.  
4 - make sure you can login with the new account.  Preferably in another browser so you can leave the admin signed in.  

if it doesn't work, restore encryption settings and try again.  

5 - if successful change the password of admin account, or delete it entirely.  
6 - pick an image for background called work.jpg  

You can contact me at edpol03@gmail.com


<hr>

## If you need, adds rc-status, rc-update, rc-service nginx restart
docker exec -it wwwx apk add openrc

Access MySQL.
```
$ docker exec -ti wwwm mariadb -u root -p
```

Run 2 commands in one line (use sh ot bash)  
With && it only executes the second command if the first one worked.  
With ; it will execute both commands regardless.
````
$ docker compose exec python-app sh -c "cd examples/names && python names2.py"
````

To call xdebug in SAFARI BROWSER
[timeclock](http://timeclock.test/index.php?XDEBUG_SESSION_START=PHPSTORM)
````
http://timeclock.test/index.php?XDEBUG_SESSION_START=PHPSTORM  
````

<hr style="border-color:cyan;"/> 

<hr style="border-color:yellow;"/>


## create composer.json
Build does not create the vendor file or the "composer.json" or "composer.lock"
Install dependencies in the container.  This will also create the vendor folder
````
$ docker compose exec php-server composer install
````
This creates composer.* files
```
$ docker exec -it ver2p composer global require laravel/installer  
$ docker exec -it ver2p composer require --dev phpunit/phpunit
```

> <hr style="background-color:cyan;"/>  
> *if you are using Namespace!!!*

Add namespace to <u>composer.json</u>   
Now you have to add:
- "namespace Includes" in the classes in the includes folder
- "use Includes" in all the files that use functions from includes folder
```
    "autoload": {
        "psr-4": {
            "Includes": "includes/",
        }
    },
```  

## Generate autoload files:
If you've modified your composer.json or have issues with the autoloader, <span style="color:yellow">regenerate the autoload files</span>.
````
$ docker exec -it wwwp composer dump-autoload
````

## Create the vendor folder
This command creates the vendor folder again, same as composer install
```
$ docker exec -it wwwp composer update  
```

## Set up for phpunit
this file tells PHPUnit where the unit tests are located (src/tests)  
src/phpunit.xml
````
<phpunit colors="true" bootstrap="vendor/autoload.php">  
    <testsuites>  
        <testsuite name="My Project Tests">  
            <directory>tests</directory>  
        </testsuite>  
    </testsuites>  
</phpunit>  
````
## Run phpunit
#### uses service name ``` docker compose exec php-server vendor/bin/phpunit ```
#### uses docker container name ``` docker exec -it wwwp vendor/bin/phpunit ```


Use <u>docker exec</u> when working with individual, standalone containers.  
Use <u>docker compose exec</u> when managing applications defined by a docker-compose.yml file, allowing you to easily target services within your multi-container setup.


because we defined the interpreter in the docker container when Intellij starts it fires off a pycharm_container and cs50p-python-app containers. If you add the --remove-orphans it will at least get rid of the cs50p containers.
````
$ docker compose up -d --remove-orphans  
````

### Install/update Composer dependencies (creates vendor/)
<span style="color:red; font-weight: bold;">DON'T INSTALL FROM HOST, USE DOCKER CONTAINER</span>
```
$ docker compose exec php-server composer install
$ docker compose exec php-server composer dump-autoload
```

### Run all PHPUnit tests
```
$ docker compose exec php-server vendor/bin/phpunit
```
### Run a single test file
````
$ docker compose exec php-server vendor/bin/phpunit tests/ExampleTest.php
````
### MySQL shell
````
$ docker exec -it ver2m mysql -u chronos -p tim
````

## 2 commands on one line
````
$ docker compose exec php-server sh -c "cd examples/names && python names2.py"
````

The End  