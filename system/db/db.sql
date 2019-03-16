DROP TABLE IF EXISTS `cAppinfo`;
CREATE TABLE `cAppinfo`  (
  `appid` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '表示小程序唯一 Id',
  `secret` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '表示小程序密钥，用于敏感 API 操作',
  `login_duration` int(11) DEFAULT 30 COMMENT '登录过期时间，单位为天，默认 30 天',
  `session_duration` int(11) DEFAULT 2592000 COMMENT '会话过期时间，单位为秒，默认为 2592000 秒(即30天)',
  `qcloud_appid` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'appid_qcloud',
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0.0.0.0',
  `access_token` varchar(521) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '该账号对应的access_token',
  `expires_in` int(11) DEFAULT 7200 COMMENT 'access_token的过期时间，默认7200秒',
  `update_time` datetime(0) DEFAULT NULL COMMENT 'access_token的上次更新时间',
  PRIMARY KEY (`appid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '小程序信息表 cAppinfo' ROW_FORMAT = Dynamic;
DROP TABLE IF EXISTS `cSessionInfo`;
CREATE TABLE `cSessionInfo`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skey` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_time` datetime(0) NOT NULL,
  `last_visit_time` datetime(0) NOT NULL,
  `open_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_info` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `union_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'unionID',
  `appid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应的appid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `auth`(`uuid`, `skey`) USING BTREE,
  INDEX `weixin`(`open_id`, `session_key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 164423 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会话管理用户信息' ROW_FORMAT = Dynamic;
DROP TABLE IF EXISTS `cAppinfo_work`;
CREATE TABLE `cAppinfo_work`  (
  `corpid` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '表示企业唯一 ID',
  `agentid` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '表示应用唯一ID',
  `agentsecret` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '表示应用唯一密钥',
  `login_duration` int(11) DEFAULT 30 COMMENT '登录过期时间，单位为天，默认 30 天',
  `session_duration` int(11) DEFAULT 2592000 COMMENT '会话过期时间，单位为秒，默认为 2592000 秒(即30天)',
  `qcloud_appid` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'appid_qcloud',
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0.0.0.0',
  `access_token` varchar(521) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '该账号对应的access_token',
  `expires_in` int(11) DEFAULT 7200 COMMENT 'access_token的过期时间，默认7200秒',
  `update_time` datetime(0) DEFAULT NULL COMMENT 'access_token的上次更新时间',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `ticket` varchar(521) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业的jsapi_ticket',
  `ticket_expires_in` int(11) DEFAULT 7200 COMMENT '企业的jsapi_ticket过期时间',
  `ticket_update_time` datetime(0) DEFAULT NULL COMMENT '企业的jsapi_ticket上次更新时间',
  `agent_ticket` varchar(521) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '企业应用的jsapi_ticket',
  `agent_update_time` datetime(0) DEFAULT NULL COMMENT '企业应用的jsapi_ticket上次更新时间',
  UNIQUE INDEX `PrimaryKey`(`corpid`, `agentid`) USING BTREE,
  INDEX `SearchKey`(`corpid`, `agentsecret`(255)) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '企业微信应用信息表 cAppinfo_work' ROW_FORMAT = Dynamic;