# PHP-Demo
这是一个基于tp5的前后端分离的完整的一套关于币支付的Demo
1. 用户发起充值
	前端请求php发起充值,php向币支付请求充值地址接口,将请求返回的地址返回给前端。
2. 正在充值
	前端一直轮询php服务,php查询是否有充值服务到账
3. 币支付发钱回调
	1. 根据币支付请求回调中的confirm参数判断该笔充值是否成功!
	2. 当confirm=1时代表该笔充值已经到账。
	3. 当confirm=0时代表该笔充值还在确认,随后币支付会发起第二个回调,通知这笔充值后续状态。(注意不管这笔充值如何第一个回调都是会有的,当第一次回调就已经成功时,将不进行第二次回调。)
4. 用户充值成功
	当币支付回调接口成功进行对应的业务逻辑处理,对应的增加相对用户的余额。
5. 查询充值记录
	用户可查询币支付的充值记录。
[前端](https://github.com/coinWinApi/Api-PHP-Demo/tree/master/demo "前档")
[后端](https://github.com/coinWinApi/Api-PHP-Demo/tree/master/WalletAppServer "后端")
