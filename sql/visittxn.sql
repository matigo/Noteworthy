CREATE TABLE IF NOT EXISTS `[DBNAME]`.`[TXNTABLE]` (
    `dsvID`         varchar(40)             NOT NULL    ,
    `DateStamp`     datetime                NOT NULL    ,
    `SiteID`        int(11)        UNSIGNED NOT NULL    ,
    `VisitURL`      varchar(256)            NOT NULL    DEFAULT '',
    `ReferURL`      varchar(256)            NOT NULL    DEFAULT '',
    `Hits`          int(11)        UNSIGNED NOT NULL    DEFAULT 0,

    `isResource`    ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isDeleted`     ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `UpdateDTS`     datetime                NOT NULL    ,
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`dsvID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `txnPriIDX` ON `[DBNAME]`.`[TXNTABLE]` (`DateStamp`, `SiteID`, `isResource`);
CREATE TABLE IF NOT EXISTS `[DBNAME]`.`[DTLTABLE]` (
    `id`            int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
    `SiteID`        int(11)        UNSIGNED NOT NULL    ,
    `DateStamp`     datetime                NOT NULL    ,
    `VisitURL`      varchar(256)            NOT NULL    DEFAULT '',
    `ReferURL`      varchar(256)            NOT NULL    DEFAULT '',
    `SearchQuery`   varchar(256)            NOT NULL    DEFAULT '',
    `Hits`          int(11)        UNSIGNED NOT NULL    DEFAULT 1,

    `isResource`    ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isSearch`      ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isArchive`     ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isDeleted`     ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `UpdateDTS`     datetime                NOT NULL    ,
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `txnPriIDX` ON `[DBNAME]`.`[DTLTABLE]` (`SiteID`, `DateStamp`, `isResource`, `isSearch`, `isArchive`);