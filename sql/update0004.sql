ALTER TABLE `[DBNAME]`.`Content` ADD COLUMN `SiteID` smallint(6) UNSIGNED NOT NULL DEFAULT 0 AFTER `id`;
CREATE INDEX `conSitIDX` ON `[DBNAME]`.`Content` (`SiteID`, `TypeCd`, `isReplaced`);
UPDATE `[DBNAME]`.`SysParm` SET `intVal` = 4, `UpdateDTS` = Now() WHERE `isDeleted` = 'N' and `Code` = 'DB_VERSION';