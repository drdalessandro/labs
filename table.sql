CREATE TABLE IF NOT EXISTS `form_labs` (
`id`                bigint(20)      NOT NULL auto_increment,
`uuid`              binary(16)      DEFAULT NULL,
`date`              datetime        default NULL,
`pid`               bigint(20)      default 0,
`user`              varchar(255)    default NULL,
`groupname`         varchar(255)    default NULL,
`authorized`        tinyint(4)      default 0,
`activity`          tinyint(4)      default 0,
`glucose`           FLOAT(7,2)      default 0,
`cholesterol`       FLOAT(7,2)      default 0,
`triglycerides`     FLOAT(7,2)      default 0,
`uric_acid`         FLOAT(5,2)      default 0,
`cholinesterase`    FLOAT(7,2)      default 0,
`urinary_phenol`    FLOAT(7,2)      default 0,
`note`              TEXT            default NULL,
PRIMARY KEY (id),
UNIQUE KEY `uuid` (uuid)
) ENGINE=InnoDB;
