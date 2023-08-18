create database timeclock;
use timeclock;

CREATE USER 'chronos'@'localhost' IDENTIFIED BY 'madmax';
GRANT ALL PRIVILEGES ON timeclock.* TO 'chronos'@'localhost';
ALTER  USER 'chronos'@'localhost' IDENTIFIED WITH mysql_native_password BY 'madmax';
FLUSH PRIVILEGES;
    
-------------------------------------------------------------------------------------------------------------------------------------------------------------

/*
 *    This table contains all of the employee information
 */
drop table if exists employees;
create table employees(
  id          integer unsigned  not null   auto_increment,
  is_active   boolean           not null   default 1,
  barcode     char(12)          not null,
  fname       varchar(30),
  lname       varchar(30),
  email       varchar(50),
  add1        varchar(30),
  add2        varchar(30),
  city        varchar(30),
  st          varchar(2),
  zip         varchar(10),
  phone       varchar(10),
  social      varchar(10),
  grp         varchar(30),
  hire_date   timestamp         not null   default now(),
  emergency_contact  varchar(50),
  emergency_phone    varchar(10),
  primary key(id),
  unique key (barcode)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

INSERT INTO employees (id, barcode, is_active, fname,lname,email,add1,add2,city,st,zip,phone)
VALUES
('102','123456789',0,'CHRISSY', 'KRULIK', '','',                         '','',           '',  '',      '         0'),
('103','234567890',0,'ZENA',    'CLARKE', '','',                         '','',           '',  '',      '         0'),
('104','345678901',1,'ABEL',    'CARLO',  '','390 W. PALMETTO PK. RD',   '','BOCA RATON', 'FL','33432', '5613911352'),
('105','456789012',1,'MUNTAZ',  'HOSEIN', '','10360 NW 30TH COURT #106', '','SUNRISE',    'FL','33322', '9547428904');

-------------------------------------------------------------------------------------------------------------------------------------------------------------

/*
 *    now i need a table that will have the timestamps in it
 *    idx timestamp. Shouldnt have a table with the same name as db
 */
drop table if exists punches;
create table punches(
  id            bigint  unsigned  not null  auto_increment,
  employee_id   integer unsigned  not null,
  punch         timestamp         not null  default now(),
  primary key(id),
  index(employee_id),
  index(punch),
  CONSTRAINT FK_id FOREIGN KEY (employee_id) REFERENCES employees(id) 
  ON UPDATE CASCADE
  ON DELETE CASCADE;
) ENGINE=Innodb DEFAULT CHARSET=utf8;

/*
ALTER TABLE timeclock ADD 
CONSTRAINT FK_id FOREIGN KEY (employeeid) REFERENCES employee(employeeid) 
ON UPDATE CASCADE
ON DELETE CASCADE;
*/

INSERT INTO punches (employee_id, punch) VALUES ('115','2015-04-01 07:21:00'), ('115','2015-04-01 14:46:00'), ('115','2015-04-01 15:49:00'), ('115','2015-04-01 19:14:00');
INSERT INTO punches (employee_id, punch) VALUES ('134','2015-04-01 07:45:00'), ('134','2015-04-01 16:33:00');

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE users (
	id              int(11)     NOT NULL AUTO_INCREMENT,
	username        varchar(50) NOT NULL,
	hashed_password varchar(60) NOT NULL,
	fname           varchar(20),
	lname           varchar(20),
	PRIMARY KEY (id)
)	ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO users VALUES                                                         
(1,'edpol',  '$2y$10$OWRlOTg5YTQ1ZmVlYjcwNOYsomm9WySPmuxOMBMj.PxTZXw812zXm','Edward' ,'Pol'     ),
(2,'steve',  '$2y$10$OWNiMmQ1NDE0OGJiYzA2NeN8SlEQO37Fh.SxV6YOYEEiUSFOhI2Te','Steve'  ,'Kuras'   );


--------------------------------------------------------------------------------------------------------------------------------------------------------------------------

/*
    3/12/2016 - 
    Added Table Groups to the employee table
	groups is a reservered word so use groupz
 */

drop table if exists groupz;
create table groupz(
  id            integer unsigned     not null     auto_increment,
  groupname     varchar(30),
  primary key(id),
  index(groupname)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

Insert into groupz (groupname) values ('Clientele'), ('MDR Fitness'), ('Rejuvenetics');
