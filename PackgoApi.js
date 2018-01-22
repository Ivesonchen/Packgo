var PackgoApi = {
	ajaxURL: {
		mallList: '../../api/home/shop_list.php'
		, joinMallFirst: '../../api/shop/first_come.php'
		, postOrder: '../../api/order/check_and_post_order.php'
		, searchMalls: '../../api/search/shop.php'
		, getGoodsList: '../api/shop/shop_good_list.php'
		, getOrderList: '../../api/order/order_list.php'
		, searchGoodsInShop: '../../api/search/goodbyshop.php'
		, postCollectShop: '../../api/collect/shop_send.php'
		, getCollectMallList: '../../api/collect/shop_collect.php'
		, getOrderDetail: '../../api/order/orderstate.php'
		, cancelCollectMall: '../../api/collect/shop_cancel.php'
		, getUserInfo: '../../api/user/getuserbyopenid.php'
		, postRegistInfo: '../../api/user/jcsms.php'
		, sendMessage: '../../api/user/sendsms.php'
		, postComplain: '../../api/sys/re.php'
		, payOldOrder: '../../api/order/rewefee.php'
	},
	
	// 购买渠道
	payChannel: {
		wechat:    1,	// 微信
		android:   2,	// 安卓APP
		web:       3,	// 网页
		others:    4	// 其他
	},
	
	/* 支付方式 */
	payType: {
		Cash_on_del: '1',	// 货到付款
		Pos_on_del:  '2',	// POS机付款
		Alipay:      '3',	// 支付宝
		WeChatpay:   '4'	// 微信支付
	},
	
	loadGoodsState: false,
	loadOrderListState: false,
	
	/** 获取订单列表
	 * 
	 * @param _uid          用户id
	 * @param _currentPage  页码
	 * @param _pageSize     页面大小
	 * @param _obj          回调   { success: function(){成功回调} , fail: function(){失败回调} }
	 */
	getOrderList: function( _uid , _currentPage , _pageSize , _obj ){
		
		if( !this.loadOrderListState && !Order.isFinished )
		{
			this.loadOrderListState = true;
			
			$.post( this.ajaxURL.getOrderList , {
				uid: _uid,
				currentPage: _currentPage,
				pageSize: _pageSize,
			}, function(data, textStatus, xhr) {
				
				if( textStatus == 'success' )
				{
					var json = JSON.parse(data);
					// console.log("获取订单列表：",json);
					if( !json.error_code )
					{
						_obj.success( json.orders );
						PackgoApi.loadOrderListState = false;
						return true;
					}
				}
				PackgoApi.loadOrderListState = false;
				_obj.fail();
			});
		}

	},
	
	/** 获取某订单详情
	 * 
	 * @param obj	{ orderId: 订单号, success: function(){成功回调} , fail: function(){失败回调}}
	 */
	getOrderDetail: function(obj){
		
		$.post(this.ajaxURL.getOrderDetail, {
			orderId: obj.orderId
		}, function(data, textStatus, xhr) {
			
			// console.log(data);
			
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("获取订单详情：",json);
				
				if( !json.error_code )
				{
					obj.success( json );
					return true;
				}
			}
			
			return obj.fail();
		});
		
	},
	
	/* 获取商店列表
	 * 
	 * @param _longitude  经度
	 * @param _latitude   纬度
	 * @param _callback   回调函数列表 参数是商店列表
	 *                    {success:function(_shoplist){成功的回调}，fail:function(){失败的回调}}
	 */
	getMallList: function( _longitude , _latitude , _callback ){
		$.get( this.ajaxURL.mallList , {
			longitude: _longitude,
			latitude:  _latitude
		}, function(data, textStatus, xhr) {
			// console.log(data);
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("获取商店列表" , json);
				
				if( !json.error_code )
					_callback.success( json.shops , json.sliderImages );
				else
					_callback.fail();
			}
		});
	},
	
	/* 第一次进入超市时候获取超市信息
	 * 
	 * @param obj	{pagesize: 页面大小 , mallid: 商店id , currentpage: 页码 , success: function(sorts){成功回调} , fail: function(){失败回调} }
	 */
	JoinMallFirst: function( obj ){
		$.post( this.ajaxURL.joinMallFirst , {
			pageSize: obj.pagesize ? obj.pagesize:10,
			shopId: obj.mall.id,
			currentPage: obj.currentpage ? obj.currentpage : 0,
			random: Math.random()
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("第一次进入超市",json);
				
				if( !json.error_code )
					obj.success( json.firstSorts );
				else
					obj.fail();
			}
		});
	},
	
	/* 提交订单
	 * 
	 * @param _shopid		INT		商店id
	 * @param _openid		String	openid
	 * @param _uid			INT		用户id
	 * @param _paytype		String	支付方式
	 * @param _arriveTime	String	送达时间
	 * @param _name			String	用户名
	 * @param _address		String	地址
	 * @param _phone		String	联系电话
	 * @param _channel		String	购买渠道
	 * @param _goods_list	json	商品列表
	 * @param _callback		obj		回调		{success: function(json){成功的回调} , fail: function(){失败的回调}}
	 * 
	 */
	postOrder: function( _shopid , _openid , _uid , _paytype , _Time , _name , _address , _phone , _remarks , _goods_list , _callback){
		
		// console.log("postOrder" ,_shopid);
		
		$.post( this.ajaxURL.postOrder , {
			shopId: _shopid,
			uid: _uid,
			sendGoodsTime: _Time,
			remark: _remarks,
			name: _name,
			openid: _openid,
			address: _address,
			phone: _phone,
			type: _paytype,
			channel: this.payChannel.wechat,
			goods_list: _goods_list
		}, function(data, textStatus, xhr) {
			
			if( textStatus == 'success' )
			{
				json = JSON.parse(data);
				// console.log("提交订单回调：" , json);
				
				if(!json.error_code)
					_callback.success(json);
				else
					_callback.fail( json.error_code );
			}
			
		});
		
	},
	
	/* 获取收藏的超市列表
	 * 
	 * @param obj	{ uid: 用户id, success: function(){成功回调} , fail: function(){失败回调} , error_code: function(errorcode){}}
	 */
	getCollectMallList: function( obj ){
		
		$.post(this.ajaxURL.getCollectMallList, {
			uid: obj.uid,
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success' )
			{
				json = JSON.parse(data);
				// console.log( '获取收藏超市' , json );
				
				if( !json.error_code )
				{
					obj.success( json.shops );
					return false;
				}
				else{
					obj.error_code( json.error_code );
				}
				
			}
			
			obj.fail();
		});
		
		
	},
	
	/** 提交收藏超市
	 * 
	 * @param obj	{ uid: 用户id , shopId: , shopId: 商店Id , success: function(){} , fail: function(){} }
	 */
	postCollectMall: function( obj ){
		
		$.post( this.ajaxURL.postCollectShop , {
			uid: obj.uid,
			shopId: obj.shopId,
			isCollect: 1
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success' )
			{
				json = JSON.parse(data);
				// console.log( '提交收藏超市' + json );
				
				if( !json.error_code )
				{
					obj.success();
					return true;
				}
			}
			
			obj.fail();
		});
	},
	
	/** 删除收藏的超市
	 * 
	 * @param obj	{ uid: 用户id, shopId: 商店Id , success: function(){成功回调} , fail: function(){失败回调} }
	 */
	cancelCollectMall: function(obj){
		$.post( this.ajaxURL.cancelCollectMall , {
			uid: obj.uid,
			shopId: obj.shopId
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success')
			{
				if( !json.error_code )
				{
					obj.success();
				}
			}
			obj.fail();
		});
		
	},
	
	/* 获取搜索商店
	 * 
	 * @param _info        搜索内容
	 * @param _pagesize    页面大小
	 * @param _currentpage 页码
	 * @param _callpage    回调   { success: function(){成功回调} , fail: function(){失败回调} }
	 * 
	 * @return 商店列表
	 */
	getSearchMalls: function( _info , _pagesize , _currentpage , _callback ){
		
		var _self = this.getSearchMalls;
		PackgoApi.getSearchMalls = function(){console.log('正在搜索...');};
		
		$.post( this.ajaxURL.searchMalls , {
			'content': _info,
			'currentPage': _currentpage ? _currentpage : 0,
			'pageSize': _pagesize
		}, function(data, textStatus, xhr) {
			
			PackgoApi.getSearchMalls = _self;
			
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log( "搜索商店结果：" , json );
				if( !json.error_code )
				{
					_callback.success( json.shops );
					return false;
				}
			}
			_callback.fail();
		});
	},
	
	/* 搜索商店内商品
	 */
	SearchGoodsInShop: function( _info , _currentpage , _pagesize , _shopId , _callback){
		
		var _self = this.SearchGoodsInShop;
		PackgoApi.SearchGoodsInShop = function(){console.log("搜索商品中...");};
		
		// console.log( _currentpage );
		
		$.post( this.ajaxURL.searchGoodsInShop , {
			content: _info,
			currentPage: _currentpage,
			pageSize: _pagesize,
			shopId: _shopId,
		}, function(data, textStatus, xhr) {
			// console.log(data);
			PackgoApi.SearchGoodsInShop = _self;
			
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("商店内搜索商品" , json);
				if( !json.error_code )
				{
					_callback.success( json.shopGoods );
					return true;
				}
			}
			
			_callback.fail();
		});		
	},
	
	/** 上拉加载商品
	 * 
	 * @param obj  Object	{ 
								shopId: 商店ID,
								pageSize: 页面大小,
								currentPage: 页码,
								sortId : 类别,
								success: function(){成功回调},
								fail: function(){失败回调}
							}
	 */
	getGoodsList: function(obj){
		
		var _self = this.getGoodsList;
		PackgoApi.getGoodsList = function(){console.log('正在加载商品...');}
		
		$.post( this.ajaxURL.getGoodsList , {
			shopId: obj.shopId,
			pageSize: obj.pageSize?obj.pageSize:10,
			currentPage: obj.currentPage,
			sortId: obj.sortId
		}, function(data, textStatus, xhr) {
			PackgoApi.getGoodsList = _self;
			// console.log(textStatus);
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("上拉加载：" , json);
				
				if( !json.error_code )
				{
					obj.success( json.firstSorts[0] );
					return true;
				}
			}
			
			obj.fail();
		});
	},
	
	
	/** 获取用户信息
	 * 
	 * @param obj Object	{ success: function(){成功回调}, fail: function(){失败的回调} }
	 */
	getUserInfo: function(obj){
		
		$.post(this.ajaxURL.getUserInfo, {
			openid: User.OpenID
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("拉取用户信息：" , json);
				
				if( !json.error_code )
				{
					obj.success( json );
					return true;
				}
			}
			
			obj.fail();
		});
	},
	
	
	/** 发送验证码
	 * 
	 * @param obj Object	{ phone: 电话 , success: function(){成功回调}, fail: function(){失败回调} }
	 */
	sendMessage: function(obj){
		
		$.post(this.ajaxURL.sendMessage, {
			phone: obj.phone
		}, function(data, textStatus, xhr) {
			// console.log(data);
			if(textStatus == 'success')
			{
				var json = JSON.parse(data);
				// console.log("发送验证码：" , json);
				
				if( !json.error_code )
				{
					obj.success();
					return true;
				}
			}
			
			obj.fail();
		});
		
	},
	
	/** 发送注册信息
	 * 
	 * @param obj Object	{ phone: 手机号 , yzm: 验证码 success: function(){成功回调} , fail: function(){失败回调} }
	 */
	postRegistInfo: function(obj){
		
		var _self = this.postRegistInfo;
		PackgoApi.postRegistInfo = function(){console.log('正在注册...');}
		
		// console.log({
// 			phone: obj.phone,
// 			yzm: obj.yzm,
// 			openid: User.OpenID
// 		});
		
		$.post(this.ajaxURL.postRegistInfo
		, {
			phone: obj.phone,
			yzm: obj.yzm,
			openid: User.OpenID
		}, function(data, textStatus, xhr) {
			
			PackgoApi.postRegistInfo = _self;
			
			if(textStatus == 'success')
			{
				var json = JSON.parse(data);
				// console.log("提交注册用户：" , json);
				
				if( !json.error_code )
				{
					obj.success(json);
				}
				else
				{
					obj.fail(json.error_code);
				}
			}
			else
			{
				obj.fail();
			}
		});
	},
	
	/** 发送投诉信息
	 * 
	 * @param obj Object	{ message: 内容 , shopId: 投诉商家的id , usrId: 投诉用户的Id , success: function(){} , fail: function(){} }
	 */
	postComplain: function(obj){
		$.post( this.ajaxURL.postComplain , {
			uid: obj.usrId,
			bid: obj.shopId,
			remarks: obj.message
		}, function(data, textStatus, xhr) {
		  //optional stuff to do after success
			if( textStatus == 'success' )
			{
				var json = JSON.parse(data);
				// console.log("提交反馈：" , json);
				
				if( !json.error_code )
				{
					obj.success();
					return true;
				}
			}
			
			obj.fail();
		});
	},
	
	/** 支付已经生成的订单
	 * 
	 * @param obj Object	{ order_code: 订单号 , success: function(){} , fail: function(){} }
	 */
	payOldOrder: function(obj){
		$.post(this.ajaxURL.payOldOrder, {
			orderid: obj.order_code,
		}, function(data, textStatus, xhr) {
			if( textStatus == 'success')
			{
				var json = JSON.parse(data);
				// console.log("支付已经生成的订单：" , json);
				
				if( !json.error_code )
				{
					obj.success( json.OnlinePay );
					return true;
				}
			}
			
			obj.fail();
		});
		
	}
}



