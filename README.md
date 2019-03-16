# Wafer On SCF

## 项目概况：

> 该项目基于腾讯云小程序会话服务器(Wafer)的二次开发。修改某些配置，破除一套服务器对应一个小程序或公众号哦的限制。添加了小程序、微信公众号、企业微信相关的AccessToken管理接口，JSAPI_TICKET管理接口等，更新mysql库到mysqli。适配了SCF架构，可直接部署到SCF上，通过API网关，提供内外网环境下的会话管理服务。

本人小白，完全不懂PHP，原项目在代码层面无法支持多个小程序或公众号，所以在此做了修改和适配。本项目可搭配官方Wafer的客户端SDK使用，但客户端SDK需要做一定的适配修改。

- 成立时间：2019-3-15
- 开发语言：PHP5
- 运行环境：SCF PHP5.6
- 项目地址：[GITHUB]()
- 原项目地址GITHUB：[Wafer 会话服务器](https://github.com/tencentyun/wafer-session-server)

## 功能说明：

1. 小程序鉴权功能：由于微信官方并未实现小程序鉴权，直接前端暴露openid的方式虽然可行但是并不安全，所以Wafer实现了小程序的鉴权。通过生成自己的userid和userkey的方式在后端实现鉴权。本功能具体请参看官方Wafer介绍
1. 小程序信息接密：小程序中敏感数据需要进行接密，如运动数据、用户信息等，直接调用本服务即可
1. 公众号AccessToken托管：公众号AccessToken生命周期托管。
1. 腾讯云COS签名
1. 企业微信自有应用的AccessToken、JSAPI_Ticket生命周期托管（只适配了企业微信企业内部应用，第三方开发商是否可用我不知道）

## 目录说明：

- application：应用目录
  - controllers：各接口功能
    - cosauth：COS鉴权相关服务
	- minaauth：小程序&服务号鉴权相关服务
	- workauth：企业微信鉴权相关服务
  - services：数据库相关操作
    - minauth：小程序&服务号
	- workauth：企业微信
- db：数据库创建语句
- system：系统配置等文件
- index.php：入口文件

## 使用方法：

1. 配置`/system/db/db.ini`中为你的MySQL连接参数
1. 数据库执行`db.sql`初始化表结构等
1. 打包所有文件为`XXX.zip`
1. SCF新建一个PHP5.6的运行环境
1. 上传并部署ZIP包
1. 申请API网关并添加API等，配置域名（如果有需要），配置接口后端等，具体请看官方文档
1. 开始使用吧。

## 开发内容：

> 流程介绍：通过程序入口index.php文件调取了system目录下parse_request.php从而解析上传的json入参对象根据json中不同的interfacename来判断调取application/controllers/qcloud/minaauth/Auth.php中对应的方法。Auth.php通过调用application/services/qcloud/minaauth/Cappinfo_Service.php对数据库进行操作。

- 项目增加了微信AccessToken的管理，可以自动进行AccessToken的生命周期托管，用户只需调用相关接口并使用即可，无需担心微信端请求次数限制。具体实现方式可查看`/application/controllers/qcloud/minaauth/Auth.php`中的`get_access_token`方法。
- 由于AccessToken是有失效机制的，同一个appid，如果用户自己调用接口获取了AccessToken，则本系统中的AccessToken会在一定时间后即失效，但系统判断失效是按数据库中的有效期进行判断的，所以可能会存在通过本服务获取的AccessToken是无效的（当然也有force方式强制获取本系统中的AccessToken）。
- 综上所述，推荐大家把同一应用的AccessToken等方法都迁移到本服务中，进行统一管理和维护。

修改内容：

- 修改mysql方法为mysqli方法
- 适配SCF入口函数
- 新增企业微信应用的鉴权接口
- 修改数据库cAuth表，插入了其他的小程序appid和SecretKey。
- 修改parse_request.php中的解析内容，加入了appid参数的解析。
- 修改Auth.php中的方法，新增了appid的入参。
- 修改Cappinfo_Service.php中的方法，加入了appid的入参，并修改了sql语句。

---
通过上述修改，实现了带appid参数调用会话服务器，服务器根据对应的appid和secretKey调用微信服务器的接口解析用户登录信息，并生成第三方session保存到数据库cAuth中的sessioninfo表中。

## 接口说明：

测试语句：`curl API网关域名/release/mina_auth/ -X POST -H 'content-type:application/json' -d '{下面的json入参}'`

> 小程序鉴权：id_skey接口,客户端传入appid和code,后台返回生成的skey和id，创建用户成功

```json
    {
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cam.id_skey",
		"para":{
			"appid":"",
			"code":""
			}
	    }
    }
```

> 小程序userinfo解密接口。客户端传入id，skey，encrypt_data,iv，会话服务器进行解密并更新用户信息和返回揭秘数据

```json
    {
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cam.userinfo",
		"para":{
			"id":"",
			"skey":"",
			"encrypt_data":"",
			"iv":""
		    }
	    }
    }
```

> 小程序auth接口。客户端鉴权，入参id,skey,appid。

```json
    {
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cam.auth",
		"para":{
			"id":"",
			"skey":"",
			"appid":""
			}
	    }
    }
```

> 小程序decrypt接口。数据解密接口，通过传入id，skey,encrypt_data,iv进行数据解密并返回

```json
    {
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cam.auth",
		"para":{
			"id":"",
			"skey":"",
			"encrypt_data":"",
			"iv":""
			}
	    }
    }
```

> 小程序&公众号access_token接口。根据appid获取有效的accesstoken

```json
	{
		"version":1,
		"componentName":"MA",
		"interface":{
			"interfaceName":"qcloud.mimi.access_token",
			"para":{
				"appid":"",
				"force":false,//强制刷新
			}
		}
	}
```

> 腾讯云COS Reusable签名接口。根据传入的参数获取COS长效签名

```json
{
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cos.auth.reusable",
		"para":{
			"appid":"",
			"sid":"",
			"skey":"",
			"bucket":"",
			"expiration":"",
			"filepath":"" || null
		}
	}
}
```

> 腾讯云COS NonReusable签名接口。根据传入的参数获取COS一次性签名

```json
{
	"version":1,
	"componentName":"MA",
	"interface":{
		"interfaceName":"qcloud.cos.auth.nonreusable",
		"para":{
			"appid":"",
			"sid":"",
			"skey":"",
			"bucket":"",
			"filepath":"" || null
		}
	}
}
```

> 企业微信access_token接口。根据agentid,corpid获取有效的accesstoken

```json
	{
		"version":1,
		"componentName":"MA",
		"interface":{
			"interfaceName":"qcloud.work.access_token",
			"para":{
				"corpid":"",//企业ID
				"agentid":"",//应用ID
				"force":false,//强制刷新
			}
		}
	}
```

> 企业微信新增或重置应用配置接口

```json
	{
		"version":1,
		"componentName":"MA",
		"interface":{
			"interfaceName":"qcloud.work.initagent",
			"para":{
				"corpid":"",//企业ID
				"agentid":"",//应用ID
				"agentsecret":"",//应用对应的secret
				"remark":"备注"//备注信息
			}
		}
	}
```

> 企业微信jsapi_ticket接口。根据agent,corpid获取有效的jsapi_ticket

```json
	{
		"version":1,
		"componentName":"MA",
		"interface":{
			"interfaceName":"qcloud.work.ticket",
			"para":{
				"corpid":"",//企业ID
				"agentid":"",//应用ID
				"force":false,//强制刷新
			}
		}
	}
```

> 企业微信应用的jsapi_ticket接口。根据agent,corpid获取有效的jsapi_ticket

```json
	{
		"version":1,
		"componentName":"MA",
		"interface":{
			"interfaceName":"qcloud.work.agent.ticket",
			"para":{
				"corpid":"",//企业ID
				"agentid":"",//应用ID
				"force":false,//强制刷新
			}
		}
	}
```