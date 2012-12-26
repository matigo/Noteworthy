/* *************************************************************************
 * @author Jason F. Irwin
 * @copyright 2012
 *
 *  This is the main SQL DataTable Definition for the Noteworthy API
 * 
 * Change Log
 * ----------
 * 2012.04.14 - Created File (J2fi)
 * ************************************************************************* */
CREATE DATABASE `nworth` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE USER 'nw_api'@'localhost' IDENTIFIED BY 'JlM94sK0';
GRANT ALL ON `nworth`.* TO 'nw_api'@'localhost';

DROP TABLE IF EXISTS `nworth`.`Users`;
CREATE TABLE IF NOT EXISTS `nworth`.`Users` (
  `id`              int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
  `UserName`        varchar(40)             NOT NULL    ,
  `UserPass`        varchar(128)            NOT NULL    ,
  `DispName`        varchar(80)             NOT NULL    DEFAULT '',
  `EmailAddr`       varchar(120)            NOT NULL    DEFAULT '',

  `LangCd`          varchar(6)              NOT NULL    ,
  `TimeZone`        varchar(6)              NOT NULL    DEFAULT '+09:00',

  `CreateDTS`       datetime                NOT NULL    ,
  `LastLogin`       datetime                NOT NULL    ,
  `isDeleted`       enum('N','Y')           NOT NULL    DEFAULT 'N',
  `EntryDTS`        timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `usrPriIDX` ON `nworth`.`Users` (`id`, `isDeleted`);
CREATE INDEX `usrSecIDX` ON `nworth`.`Users` (`EmailAddr`, `isDeleted`);

DROP TABLE IF EXISTS `nworth`.`Type`;
CREATE TABLE IF NOT EXISTS `nworth`.`Type` (
    `Code`          varchar(12)             NOT NULL    ,
    `Descr`         varchar(80)             NOT NULL    DEFAULT '',
    
    `isDeleted`     enum('N','Y')           NOT NULL    DEFAULT 'N',
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `typPriIDX` ON `nworth`.`Type` (`Code`, `isDeleted`);

INSERT INTO `nworth`.`Type` (`Code`, `Descr`)
VALUES ('POST', 'Post Content'), ('POST-GPS', 'Post Entry GPS Location'), ('POST-AUTHOR', 'Post Author'),
       ('IMAGE', 'Image Content'), ('IMAGE-DTS', 'Image Date/Time Stamp'), ('IMAGE-GPS', 'Image GPS Location'),
       ('IMAGE-CAP', 'Image Caption'), ('IMAGE-BY', 'Image Creator'), ('TWEET', 'Tweet Content'),
       ('TWEET-DTS', 'Tweet Date/Time Stamp'), ('TWEET-GPS', 'Tweet GPS Location'), ('TAG', 'Item Tag'),
       ('POST-URL', 'URL of Object'), ('OTHER', 'Other (Unspecified) Content');

DROP TABLE IF EXISTS `nworth`.`Content`;
CREATE TABLE IF NOT EXISTS `nworth`.`Content` (
    `id`            int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
    `guid`          char(36)                NOT NULL    ,
    `TypeCd`        varchar(12)             NOT NULL    DEFAULT 'OTHER',
    `Title`         varchar(512)            NOT NULL    ,
    `Value`         text                    NOT NULL    ,
    `Hash`          varchar(40)                 NULL    ,
    `ParentID`      int(11)                     NULL    ,

    `CreateDTS`     datetime                NOT NULL    ,
    `UpdateDTS`     datetime                NOT NULL    ,
    `DeleteDTS`     datetime                    NULL    ,
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    `isReplaced`    enum('N','Y')           NOT NULL    DEFAULT 'N',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `conPriIDX` ON `nworth`.`Content` (`id`, `guid`, `TypeCd`);
CREATE INDEX `conDtsIDX` ON `nworth`.`Content` (`id`, `CreateDTS`, `DeleteDTS`);

DROP TABLE IF EXISTS `nworth`.`Meta`;
CREATE TABLE IF NOT EXISTS `nworth`.`Meta` (
    `id`            int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
    `ContentID`     int(11)                     NULL    ,
    `ParentID`      int(11)                     NULL    ,
    `guid`          char(36)                NOT NULL    ,
    `TypeCd`        varchar(12)             NOT NULL    DEFAULT 'OTHER',
    `Value`         varchar(2000)           NOT NULL    ,
    `Hash`          varchar(40)                 NULL    ,

    `isDeleted`     ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `mtaPriIDX` ON `nworth`.`Meta` (`id`, `ContentID`, `TypeCd`);
CREATE INDEX `mtaSecIDX` ON `nworth`.`Meta` (`id`, `ParentID`, `TypeCd`);
CREATE INDEX `mtaGuiIDX` ON `nworth`.`Meta` (`id`, `guid`, `TypeCd`);

DROP TABLE IF EXISTS `nworth`.`SysHash`;
CREATE TABLE IF NOT EXISTS `nworth`.`SysHash` (
    `Code`          varchar(12)             NOT NULL    ,
    `Value`         varchar(32)             NOT NULL    DEFAULT '',
    
    `isDeleted`     enum('N','Y')           NOT NULL    DEFAULT 'N',
    `UpdateDTS`     timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `typPriIDX` ON `nworth`.`SysHash` (`Code`, `isDeleted`);

INSERT INTO `nworth`.`SysHash` (`Code`, `UpdateDTS`)
VALUES ('LANDING', '2012-01-01'), ('SIDEBAR', '2012-01-01'), 
       ('TWEETS', '2012-01-01');