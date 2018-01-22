/************************************************************************************************
											packgo.sql
										 购物网站数据库架构

@autor		IthilQuessir
@email		itimecracker@gmail.com
@version	1.1.2
@comments	生成packgo网站数据库


@Modified Date	2015-6-9  IthilQuessir
	v1.1.0		多数据库合并成一个以提高前期效率,增添部分新字段数、据统计表单、历史记录表单。
	v1.1.1		更改文件中与mariadb不兼容的语句，修改和添加部分字段。
	v1.1.2		地址表地址细分
	v1.1.3		部分表合并
	v1.1.4		添加部分字段

*************************************************************************************************/


/************************************************************************************************
											总数据库
*************************************************************************************************/

-- 创建数据库并且设置字符集为UTF8
CREATE DATABASE phpdbusr_packgodb CHARACTER SET utf8 COLLATE utf8_general_ci;

SET character_set_client = utf8;

USE phpdbusr_packgodb;


/** 商家信息表 */
CREATE TABLE bussinesstb(
	id INT auto_increment NOT NULL,			-- 商家id
	
	openid CHAR(28),						-- openid
	
	latitude DOUBLE,						-- 商家地址·纬度
	
	longitude DOUBLE,						-- 商家地址·经度
	
	userphone CHAR(15),						-- 商家账户·手机
	
	usermail CHAR(255),						-- 商家账户·邮箱
	
	password TEXT,							-- 商家密码
	
	checkstatus	TINYINT NOT NULL,			-- 审核状态
																					-- 完善信息	等待审核	审核通过	审核失败
																					--    0        1       2       3
	
	checkerror TEXT,						-- 审核失败原因
	
	paymode TEXT,							-- 支付方式
	
	mallname CHAR(255),						-- 超市名称
	
	head_img TEXT,							-- 超市头像URL
	
	province CHAR(20),						-- 超市地址 · 省
	city CHAR(20),							-- 超市地址 · 市
	reg CHAR(40),							-- 超市地址 · 区
	address CHAR(255),						-- 超市地址 · 详细地址
	
	
	manager CHAR(255),						-- 负责人
	
	phone CHAR(15),							-- 联系电话
	
	email CHAR(255),						-- 联系邮箱
	
	remarks CHAR(255),						-- 备注
	
	isProved bit,							-- 是否认证
	
	proveURL CHAR(255),						-- 证件图片URL
	
	dataline BIGINT,						-- 证件到期日
	
	registertime BIGINT,					-- 注册时间
	
	scale TINYINT,							-- 超市规模
																						--     小型    中型     大型
																						-- ENUM('SMALL','MEDIUM','BIG')
	
	malltype TEXT,							-- 超市类型
																						-- ENUM('PERSONAL','LINK')
																						-- NOT NULL,
	
	prestige TINYINT,						-- 信誉
																						-- ENUM('0','1','2','3','4','5')
																						-- NOT NULL DEFAULT '3',
	victory INT,							-- 收到的赞
	
	defeats INT,							-- 收到的差评数量
	
	starttime BIGINT,						-- 营业开始时间
	
	endtime BIGINT,							-- 营业结束时间
	
	price SMALLINT,							-- 起送价格
	
	distribution_money FLOAT,				-- 配送费
	
	sending_time FLOAT,						-- 配送时间
	
	description CHAR(255),					-- 商店描述
	
	send_info CHAR(255),					-- 配送信息
	
	user_scroe TEXT,						-- 用户积分
	
	is_scroe bit,							-- 是否支持积分制度
	
	PRIMARY KEY (id)
);






/************************************************************************************************
											商户商品数据表
*************************************************************************************************/

-- 商品总表
CREATE TABLE tradertb(
	id INT auto_increment NOT NULL,			-- 物品id
	
	name CHAR(255) NOT NULL,				-- 物品名称
	
	picture CHAR(255) NOT NULL,				-- 物品图片URL
	
	classify TINYINT NOT NULL,				-- 物品类别
																						-- SET('drink','food')
																						-- NOT NULL,

	price FLOAT NOT NULL,					-- 推荐价格
	
	manufacturer CHAR(255),					-- 生产厂商
	
	barcode CHAR(255),						-- 条形码
	
	remarks CHAR(255),						-- 备注
	
	PRIMARY KEY (id)
);


-- 商品表
CREATE TABLE goodstb_1(						-- 商家名+goodstb命名该表，该表每个商户一份
	id INT AUTO_INCREMENT NOT NULL,			-- 商户商品id
	
	bid INT,								-- 商家id
	
	price FLOAT NOT NULL,					-- 价格
	
	isdiscount bit NOT NULL,				-- 是否打折
	
	discountprice FLOAT NOT NULL,			-- 折扣后的价格
	
	stock BIT NOT NULL,						-- 库存（有/无）
	
	volume INT NOT NULL,					-- 销量
	
	name CHAR(255) NOT NULL,				-- 物品名称
	
	picture CHAR(255) NOT NULL,				-- 物品图片URL
	
	classify TINYINT NOT NULL,				-- 物品类别
																						-- SET('drink','food')
																						-- NOT NULL,
	
	barcode CHAR(255),						-- 条形码
	
	remarks CHAR(255),						-- 备注
	
	PRIMARY KEY (id)
);




/************************************************************************************************
											用户数据表
*************************************************************************************************/


CREATE TABLE infotb(
	id int auto_increment NOT NULL,		-- 用户id
	
	name CHAR(20),						-- 用户名	昵称
	
	openid CHAR(28),					-- openid	用户微信检索用户
	
	phone CHAR(15),						-- 手机号	通过手机注册的账户名
	
	email CHAR(255),					-- 邮箱		通过邮箱注册的账户名  暂时不提供邮箱注册
	
	password CHAR(35),					-- 密码		加密算法定完通知其他人
	
	IMEI CHAR(255),						-- IMEI		手机唯一序列码
	
	prestige TINYINT,					-- 信誉度	默认100，算法待定，暂时写死
	
	sex bit,							-- 性别
	
	register BIGINT,					-- 注册时间
	
	address TEXT,						-- 地址		用户存储的地址  第一个地址作为默认地址
	
	old_location TEXT,					-- 曾用定位	用户曾经使用过的定位地址
	
	shop_collect TEXT,					-- 收藏的商家
	
	goods_collect TEXT,					-- 收藏的商品
	
	search_shop TEXT,					-- 常用超市搜索	用户曾经用于搜索的操作
	
	search_goods TEXT,					-- 常用物品搜索	用户曾经用于搜索的操作
	
	malls TEXT,							-- 购买过的超市	记录用于统计、分析用户信息
	
	PRIMARY KEY (id)
);



-- 订单（包含历史订单）
CREATE TABLE orderhistorytb_1(
	id INT auto_increment NOT NULL,		-- 订单id
	
	order_code CHAR(32) NOT NULL UNIQUE,-- 订单号
	
	wechat_order_code CHAR(32),			-- 如果是微信支付，则支付成功后会返回这么一个微信订单号，与商户订单号不是同一个。
	
	refund_code CHAR(32),				-- 商户退款单号  如果不退款，相关退款字段全部为NULL
	
	wechat_refund_code CHAR(32),		-- 微信退款单号
	
	refund_fee INT,						-- 退款金额 单位分
	
	uid INT,							-- 用户id
	
	openid CHAR(28),					-- openid
	
	bid	INT,							-- 商家id
	
	time BIGINT NOT NULL,				-- 购买时间
	
	arrive_time BIGINT NOT NULL,		-- 送达时间
	
	handle_time CHAR(255) NOT NULL,		-- 用户设置的时间 或者是自提
	
	channel TINYINT NOT NULL,			-- 购买渠道  微信  APP  // 网页 暂未开放 //
	
	price DOUBLE NOT NULL,				-- 总价
	
	user_name CHAR(20),					-- 用户名
	
	address TEXT NOT NULL,				-- 地址
	
	phone CHAR(15) NOT NULL,			-- 联系电话
	
	status TINYINT NOT NULL,			-- 订单状态
																						-- ENUM('WAIT','SEND','SUCCESS','FAIL')
																						-- NOT NULL DEFAULT 'WAIT',
	
	is_fee bit NOT NULL,				-- 是否已经付款   货到付款默认未付，如果是在线支付，此状态变成支付的时候再通知商家
	
	remark CHAR(255),					-- 订单备注
	
	type TINYINT NOT NULL,				-- 支付方式
																						--   到付   在线付款  
																						-- SET('NORMAL','SPACIALTIME')
																						-- NOT NULL,
	goodslist TEXT NOT NULL,			-- 订单商品
	
	PRIMARY KEY (id)
);

-- -- 订单商品信息（包含历史订单）
-- CREATE TABLE goodshistorytb(
-- 	ohid int,								-- 历订单id
--
-- 	gname VARCHAR(255) NOT NULL,			-- 商品名称
--
-- 	num MEDIUMINT NOT NULL,					-- 商品数量
--
-- 	gpic MEDIUMINT NOT NULL,				-- 商品总价
--
-- 	gmanufacturer VARCHAR(255) NOT NULL,	-- 商品生产商
--
-- 	FOREIGN KEY (ohid) REFERENCES orderhistorytb(id)
-- );

-- 投诉表
CREATE TABLE complaintb_1(
	uid int,							-- 用户id
	
	bid int,							-- 商户id
	
	remarks TEXT NOT NULL,				-- 投诉说明
	
	checkstatus							-- 处理状态
		ENUM('WAIT','CHECK','FINISH')
		NOT NULL DEFAULT 'WAIT',
	
	result TEXT							-- 审核结果
);

-- 反馈表
CREATE TABLE feedback(
	
	id int NOT NULL,					-- 投诉者id
	
	target TINYINT NOT NULL,			-- 商家或者是用户或者是我们自己
	
	remarks TEXT NOT NULL,				-- 反馈内容
	
	checkstatus							-- 处理状态
		ENUM('WAIT','CHECK','FINISH')
		NOT NULL DEFAULT 'WAIT',
	
	result TEXT							-- 审核结果
)
s
/************************************************************************************************
											统计数据表
*************************************************************************************************/

