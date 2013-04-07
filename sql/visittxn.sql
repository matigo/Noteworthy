CREATE TABLE IF NOT EXISTS `[DBNAME]`.`[TXNTABLE]` (
    `id`            int(11)        UNSIGNED NOT NULL    AUTO_INCREMENT,
    `DateStamp`     datetime                NOT NULL    ,
    `SiteID`        int(11)        UNSIGNED NOT NULL    ,
    `VisitURL`      varchar(256)            NOT NULL    DEFAULT '',
    `VisitorIP4`    varchar(15)                 NULL    ,
    `VisitorIP6`    varchar(40)                 NULL    ,
    `UserAgent`     varchar(256)                NULL    ,
    `Browser`       varchar(40)                 NULL    ,
    `BrowserVer`    varchar(20)                 NULL    ,
    `Platform`      varchar(80)                 NULL    ,

    `ReferURL`      varchar(256)            NOT NULL    DEFAULT '',

    `isAPI`         ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isRSS`         ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isResource`    ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `isOwner`       ENUM('N','Y')           NOT NULL    DEFAULT 'N',

    `isDeleted`     ENUM('N','Y')           NOT NULL    DEFAULT 'N',
    `EntryDTS`      timestamp               NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE INDEX `txnPriIDX` ON `[DBNAME]`.`[TXNTABLE]` (`DateStamp`, `SiteID`, `isOwner`, `isAPI`, `isRSS`, `isResource`);