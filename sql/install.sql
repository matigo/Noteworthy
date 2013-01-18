CREATE TABLE IF NOT EXISTS `[DBNAME]`.`Type` (
    `Code`          varchar(12)             NOT NULL    ,
    `Descr`         varchar(80)             NOT NULL    DEFAULT '',
    
    `isDeleted`     enum('N','Y')           NOT NULL    DEFAULT 'N',
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `typPriIDX` ON `[DBNAME]`.`Type` (`Code`, `isDeleted`);

INSERT INTO `[DBNAME]`.`Type` (`Code`, `Descr`)
VALUES ('POST', 'Post Content'), ('POST-GPS', 'Post Entry GPS Location'), ('POST-AUTHOR', 'Post Author'),
       ('IMAGE', 'Image Content'), ('IMAGE-DTS', 'Image Date/Time Stamp'), ('IMAGE-GPS', 'Image GPS Location'),
       ('IMAGE-CAP', 'Image Caption'), ('IMAGE-BY', 'Image Creator'), ('TWEET', 'Tweet Content'),
       ('TWEET-DTS', 'Tweet Date/Time Stamp'), ('TWEET-GPS', 'Tweet GPS Location'), ('TAG', 'Item Tag'),
       ('POST-URL', 'URL of Object'), ('POST-NCUD', 'Notebook Create/Update/Delete Hash'), ('OTHER', 'Other (Unspecified) Content');

CREATE TABLE IF NOT EXISTS `[DBNAME]`.`Content` (
    `id`            int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
    `SiteID`		smallint(6)	   UNSIGNED NOT NULL	DEFAULT 0,
    `guid`          char(36)                NOT NULL    ,
    `TypeCd`        varchar(12)             NOT NULL    DEFAULT 'OTHER',
    `Title`         varchar(512)            NOT NULL    ,
    `Value`         text                    NOT NULL    ,
    `Hash`          varchar(40)                 NULL    ,
    `ParentID`      int(11)                     NULL    ,
    `PostURL`		text					NOT NULL	,
    `PostAuthor`	varchar(80)				NOT NULL	,
    `UpdateSeqID`	int(11)					NOT NULL	DEFAULT 0,

    `CreateDTS`     datetime                NOT NULL	,
    `UpdateDTS`     datetime                NOT NULL	,
    `DeleteDTS`     datetime                    NULL	,
    `EntryDTS`      timestamp               NOT NULL	DEFAULT CURRENT_TIMESTAMP,
    `isReplaced`    enum('N','Y')           NOT NULL	DEFAULT 'N',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `conPriIDX` ON `[DBNAME]`.`Content` (`id`, `ParentID`, `TypeCd`, `isReplaced`);
CREATE INDEX `conSitIDX` ON `[DBNAME]`.`Content` (`SiteID`, `TypeCd`, `isReplaced`);
ALTER TABLE `[DBNAME]`.`Content` ADD FULLTEXT(`Title`, `Value`, `PostURL`);

CREATE TABLE IF NOT EXISTS `[DBNAME]`.`Meta` (
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
CREATE INDEX `mtaPriIDX` ON `[DBNAME]`.`Meta` (`id`, `ContentID`, `ParentID`, `TypeCd`);
CREATE INDEX `mtaGuiIDX` ON `[DBNAME]`.`Meta` (`id`, `guid`, `TypeCd`);

CREATE TABLE IF NOT EXISTS `[DBNAME]`.`SysParm` (
    `Code`          varchar(12)             NOT NULL    ,
    `intVal`		int(11)					NOT NULL	DEFAULT 0,
    `strVal`        varchar(32)             NOT NULL    DEFAULT '',
    
    `isDeleted`     enum('N','Y')           NOT NULL    DEFAULT 'N',
    `UpdateDTS`     timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `typPriIDX` ON `[DBNAME]`.`SysParm` (`Code`, `isDeleted`);

INSERT INTO `[DBNAME]`.`SysParm` (`Code`, `intVal`)
VALUES ('DB_VERSION', 4);