-- --------------------------------------------------------
-- 호스트:                          183.111.182.249
-- 서버 버전:                        10.1.13-MariaDB - MariaDB Server
-- 서버 OS:                        Linux
-- HeidiSQL 버전:                  12.11.0.7074
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- 테이블 df77_eroom.df_banner_main 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_banner_main` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `upfile_pc01` varchar(50) DEFAULT NULL,
  `upfile_pc02` varchar(50) DEFAULT NULL,
  `upfile_mo01` varchar(50) DEFAULT NULL,
  `upfile_mo02` varchar(50) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `url_link` char(1) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `prior` bigint(20) DEFAULT NULL,
  `showset` char(1) DEFAULT NULL,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_counter_browser 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_counter_browser` (
  `cb_pm` varchar(1) DEFAULT NULL,
  `cb_browse` varchar(255) NOT NULL DEFAULT '',
  `cb_hit` int(11) NOT NULL DEFAULT '0',
  `cb_uptime` int(11) NOT NULL DEFAULT '0',
  `cb_code` varchar(50) DEFAULT NULL,
  KEY `idx_cb_code_pm` (`cb_code`,`cb_pm`),
  KEY `idx_cb_code_pm_browse` (`cb_code`,`cb_pm`,`cb_browse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_counter_display 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_counter_display` (
  `cd_width` smallint(6) NOT NULL DEFAULT '0',
  `cd_height` smallint(6) NOT NULL DEFAULT '0',
  `cd_hit` int(11) NOT NULL DEFAULT '0',
  `cd_uptime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_counter_ip 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_counter_ip` (
  `ci_pm` varchar(1) DEFAULT NULL,
  `ci_ip` varchar(15) NOT NULL DEFAULT '',
  `ci_domain` varchar(100) NOT NULL DEFAULT '',
  `ci_yy` tinyint(2) unsigned zerofill NOT NULL DEFAULT '00',
  `ci_mm` tinyint(2) unsigned zerofill NOT NULL DEFAULT '00',
  `ci_dd` tinyint(2) unsigned zerofill NOT NULL DEFAULT '00',
  `ci_ww` tinyint(4) NOT NULL DEFAULT '0',
  `ci_hh` tinyint(2) unsigned zerofill NOT NULL DEFAULT '00',
  `ci_hit` int(11) NOT NULL DEFAULT '0',
  `ci_uptime` int(11) NOT NULL DEFAULT '0',
  `ci_code` varchar(50) DEFAULT NULL,
  UNIQUE KEY `uq_ip_count` (`ci_code`,`ci_pm`,`ci_ip`,`ci_yy`,`ci_mm`,`ci_dd`,`ci_hh`) USING BTREE,
  KEY `ci_code` (`ci_code`),
  KEY `ci_ip` (`ci_ip`),
  KEY `ci_hh` (`ci_hh`),
  KEY `ci_pm` (`ci_pm`,`ci_yy`,`ci_mm`,`ci_dd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_counter_now 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_counter_now` (
  `session_id` varchar(50) NOT NULL DEFAULT '',
  `pm` varchar(1) DEFAULT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `uptime` int(11) DEFAULT NULL,
  PRIMARY KEY (`session_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_counter_url 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_counter_url` (
  `cu_pm` varchar(1) DEFAULT NULL,
  `cu_url` mediumtext NOT NULL,
  `cu_hit` int(11) NOT NULL DEFAULT '0',
  `cu_uptime` int(11) NOT NULL DEFAULT '0',
  `cu_code` varchar(50) DEFAULT NULL,
  UNIQUE KEY `uq_counter_url` (`cu_code`,`cu_pm`,`cu_url`(250)) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_admin 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_admin` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `passwd` varchar(128) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `resno` varchar(14) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `tphone` varchar(14) DEFAULT NULL,
  `hphone` varchar(14) DEFAULT NULL,
  `post` varchar(7) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `part` int(11) DEFAULT NULL,
  `permi` mediumtext,
  `last` datetime DEFAULT NULL,
  `wdate` datetime DEFAULT NULL,
  `descript` mediumtext,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_bbs 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_bbs` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) DEFAULT NULL,
  `parno` int(10) unsigned DEFAULT NULL,
  `prino` int(10) unsigned DEFAULT NULL,
  `depno` int(10) unsigned DEFAULT '0',
  `notice` char(1) DEFAULT 'N',
  `grp` varchar(80) DEFAULT NULL,
  `grp_2` varchar(80) DEFAULT NULL,
  `grp_3` varchar(80) DEFAULT NULL,
  `memid` varchar(20) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` longtext,
  `faq_a` longtext,
  `ctype` enum('T','H') DEFAULT 'H',
  `privacy` enum('Y','N') DEFAULT NULL,
  `passwd` varchar(255) DEFAULT NULL,
  `count` mediumint(8) unsigned DEFAULT '0',
  `recom` mediumint(8) unsigned DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `wdate` datetime DEFAULT NULL,
  `wdate_original` datetime DEFAULT NULL,
  `orderid` varchar(30) DEFAULT NULL,
  `prdcode` varchar(30) DEFAULT NULL,
  `sns_link` varchar(255) DEFAULT 'N',
  `event_sdate` varchar(10) DEFAULT NULL,
  `event_edate` varchar(10) DEFAULT NULL,
  `event_win` enum('Y','N') DEFAULT 'N',
  `event_winner` longtext,
  `rpermi` varchar(255) DEFAULT NULL,
  `upfile` varchar(255) DEFAULT NULL,
  `upfile_name` varchar(255) DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `center_name` varchar(255) DEFAULT NULL,
  `app_target` varchar(255) DEFAULT NULL COMMENT '교육소식게시판용 - 참가대상',
  `app_date` varchar(255) DEFAULT NULL COMMENT '교육소식게시판용 - 일시',
  PRIMARY KEY (`idx`),
  KEY `IX_site_bbs_01` (`idx`) USING BTREE,
  KEY `IX_site_bbs_02` (`memid`),
  KEY `IX_site_bbs_03` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_bbsinfo 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_bbsinfo` (
  `bbs_category` varchar(30) DEFAULT NULL,
  `bbs_order` int(11) DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `titleimg` varchar(40) DEFAULT NULL,
  `header` varchar(255) DEFAULT NULL,
  `footer` varchar(255) DEFAULT NULL,
  `grp` varchar(255) DEFAULT NULL,
  `grp_2` varchar(255) DEFAULT NULL,
  `grp_3` varchar(255) DEFAULT NULL,
  `lpermi` varchar(6) DEFAULT NULL,
  `rpermi` varchar(6) DEFAULT NULL,
  `wpermi` varchar(6) DEFAULT NULL,
  `apermi` varchar(6) DEFAULT NULL,
  `cpermi` varchar(6) DEFAULT NULL,
  `bbstype` enum('BBS','PHOTO','GALLERY','MEDIA') DEFAULT NULL,
  `skintype` varchar(20) DEFAULT NULL,
  `usetype` enum('Y','N') DEFAULT NULL,
  `privacy` enum('Y','N') DEFAULT NULL,
  `upfile` enum('Y','N') DEFAULT NULL,
  `comment` enum('Y','N') DEFAULT NULL,
  `remail` enum('Y','N') DEFAULT NULL,
  `imgview` enum('Y','N') DEFAULT NULL,
  `abuse` enum('Y','N') DEFAULT NULL,
  `abtxt` mediumtext,
  `rows` smallint(5) unsigned DEFAULT NULL,
  `lists` smallint(5) unsigned DEFAULT NULL,
  `new` smallint(5) unsigned DEFAULT NULL,
  `hot` smallint(5) unsigned DEFAULT NULL,
  `editor` enum('Y','N') DEFAULT NULL,
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_bbs_files 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_bbs_files` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bbsidx` int(10) unsigned DEFAULT NULL,
  `upfile` varchar(255) DEFAULT NULL,
  `upfile_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idx`),
  KEY `IX_site_bbs_files_01` (`bbsidx`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_content 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_content` (
  `idx` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '',
  `lang` varchar(2) DEFAULT NULL,
  `isuse` enum('Y','N') DEFAULT NULL,
  `scroll` enum('Y','N') DEFAULT NULL,
  `posi_x` smallint(5) unsigned DEFAULT NULL,
  `posi_y` smallint(5) unsigned DEFAULT NULL,
  `size_x` smallint(5) unsigned DEFAULT NULL,
  `size_y` smallint(5) unsigned DEFAULT NULL,
  `sdate` date DEFAULT NULL,
  `edate` date DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext,
  `wdate` date DEFAULT NULL,
  `poptype` varchar(20) DEFAULT 'pop',
  `close_bg` varchar(7) DEFAULT NULL,
  `close_align` varchar(10) DEFAULT NULL,
  `close_txt` varchar(100) DEFAULT NULL,
  `close_txt_color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_content_mobile 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_content_mobile` (
  `idx` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '',
  `lang` varchar(2) DEFAULT NULL,
  `isuse` enum('Y','N') DEFAULT NULL,
  `sdate` date DEFAULT NULL,
  `edate` date DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext,
  `wdate` date DEFAULT NULL,
  `close_bg` varchar(7) DEFAULT NULL,
  `close_align` varchar(10) DEFAULT NULL,
  `close_txt` varchar(100) DEFAULT NULL,
  `close_txt_color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_member 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_member` (
  idx INT AUTO_INCREMENT PRIMARY KEY,
wdate DATETIME DEFAULT CURRENT_TIMESTAMP,
wip VARCHAR(50),
last_login DATETIME,
f_user_id VARCHAR(128) NOT NULL UNIQUE,
f_password VARCHAR(500) NOT NULL,
f_user_name VARCHAR(128) NOT NULL,
f_birth_date VARCHAR(10) NOT NULL,
f_tel VARCHAR(20) NOT NULL,
f_mobile VARCHAR(20) NOT NULL UNIQUE,
f_email VARCHAR(200) NOT NULL UNIQUE,
f_gender ENUM('F','M') NOT NULL,
f_addr_zip VARCHAR(30) NOT NULL,
f_addr_basic VARCHAR(500) NOT NULL,
f_addr_detail VARCHAR(500),
is_del ENUM('N','Y') DEFAULT 'N',
is_out ENUM('N','Y') DEFAULT 'N',
sns_gubun VARCHAR(128),
sns_id VARCHAR(500),
sns_date datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_page 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_page` (
  `idx` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '',
  `subimg` varchar(100) DEFAULT NULL,
  `content` mediumtext,
  `addinfo` mediumtext,
  `addinfo2` mediumtext,
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

-- 테이블 df77_eroom.df_site_siteinfo 구조 내보내기
CREATE TABLE IF NOT EXISTS `df_site_siteinfo` (
  `site_name` varchar(50) DEFAULT NULL,
  `site_url` varchar(200) NOT NULL DEFAULT '',
  `site_email` varchar(120) DEFAULT NULL,
  `site_tel` varchar(16) DEFAULT NULL,
  `site_hand` varchar(16) DEFAULT NULL,
  `admin_title` varchar(250) NOT NULL DEFAULT '',
  `com_num` varchar(20) DEFAULT NULL,
  `com_name` varchar(30) DEFAULT NULL,
  `com_owner` varchar(20) DEFAULT NULL,
  `com_post` varchar(7) DEFAULT NULL,
  `com_address` varchar(120) DEFAULT NULL,
  `com_kind` varchar(50) DEFAULT NULL,
  `com_class` varchar(50) DEFAULT NULL,
  `com_tel` varchar(20) DEFAULT NULL,
  `com_fax` varchar(20) DEFAULT NULL,
  `site_title` varchar(255) DEFAULT NULL,
  `site_keyword` varchar(255) DEFAULT NULL,
  `site_intro` varchar(255) DEFAULT NULL,
  `site_image` varchar(100) DEFAULT NULL,
  `site_clip` mediumtext,
  `title_01-1` varchar(50) DEFAULT NULL,
  `title_03-1` varchar(50) DEFAULT NULL,
  `title_03-1-0` varchar(50) DEFAULT NULL,
  `title_03-1-1` varchar(50) DEFAULT NULL,
  `title_03-1-2` varchar(50) DEFAULT NULL,
  `title_03-1-3` varchar(50) DEFAULT NULL,
  `title_03-1-4` varchar(50) DEFAULT NULL,
  `title_03-1-5` varchar(50) DEFAULT NULL,
  `title_04-1` varchar(50) DEFAULT NULL,
  `title_04-2` varchar(50) DEFAULT NULL,
  `title_04-3` varchar(50) DEFAULT NULL,
  `title_05-1` varchar(50) DEFAULT NULL,
  `title_05-2` varchar(50) DEFAULT NULL,
  `title_06-1` varchar(50) DEFAULT NULL,
  `title_06-2` varchar(50) DEFAULT NULL,
  `title_06-3` varchar(50) DEFAULT NULL,
  `title_06-4` varchar(50) DEFAULT NULL,
  `g_user` varchar(100) DEFAULT NULL,
  `g_app_password` varchar(50) DEFAULT NULL,
  `g_manager_email` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 내보낼 데이터가 선택되어 있지 않습니다.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
