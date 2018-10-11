##小程序获取用户绑定手机号##


### 纲要 ###
1. 页面绑定button
2. 小程序组键
3. php服务端
4. 微信官方各语言版本解密文件

### 正文 ###
使用小程序获取用户手机号，需要企业认证（个体工商认证的企业号）的账号，所以个人账号无法使用此功能。

#### 1.页面构建 ####
小程序的getPhoneNumber功能必须绑定在button事件上，所以构建页面需要提供button按钮进行授权操作。

*index.wxml*

```
<view>
  <button type='primary' open-type="getPhoneNumber" bindgetphonenumber="getPhoneNumber">授权登录</button>
</view>
```

#### 2.小程序getPhoneNumber组键应用 ####

调用wx.login登录接口后会获得code，所以需要配置参数，你的小程序APPID和你的小程序appsecret，然后定义data变量。

<pre name="code" class="javascript">
var app = getApp();
var phoneObj = "";
page({
    data: {
        tokenobj:'',
        phoneObj:''
    },
    ....
</pre>

index.js

<pre>
//获取应用实例
const app = getApp()
//定义变量
var phoneObj = "";

Page({
  data: {
    tokenobj: '',
    phoneObj: '',
  },

  onLoad: function () {  
  },

  //通过绑定手机号登录
  getPhoneNumber: function (e) {
    var ivObj = e.detail.iv
    var telObj = e.detail.encryptedData
    var codeObj = "";
    var that = this;
    //执行Login
    wx.login({
      success: res => {
        //console.log('code转换', res.code); 
        //用code传给服务器调换session_key
        wx.request({
          url: 'https://disqus.huxinchun.com/demo/getphone.php', //接口请求地址
          data: {
            appid: "wx5c686ac37579f0a3", //小程序appid，登录微信后台查看
            secret: "3779c2acddf6b23be2b9c8356510a8b7", //小程序secret，登录微信后台可查看
            code: res.code,
            encryptedData: telObj,
            iv: ivObj
          },
          header: {
            'content-type': 'application/json' // 默认值
          },
          //成功返回数据
          success: function (res) {
            phoneObj = res.data.phoneNumber;
            //console.log("手机号=", phoneObj)
            //存储数据并准备发送给下一页使用
            wx.setStorage({
              key: "phoneObj",
              data: res.data.phoneNumber,
            })
            //弹出提示
              wx.showModal({ 
                title: '手机号为：', content: phoneObj, 
              success: function (res) { 
                if (res.confirm) { console.log('确定') } 
                else if (res.cancel) {console.log('取消') } 
              } 
            }) 
          
          }
        })
      }
    })
  }

})
</pre>


#### 3.php服务端构建 ####

小程序解密需要利用官方给出的解密文件，该解密文件有多个语言版本，可以根据开发所使用的语言选择。

首先在getphone.php中需要获得小程序端传来的参数
<pre>
//获取小程序参数
$appid =$_GET['appid'];
$secret =$_GET['secret'];
$js_code=$_GET['code'];
$iv = ($_GET['iv']);
$encryptedData=($_GET['encryptedData']);
$grant_type='authorization_code';
</pre>
然后使用参数发起接口请求,获取到我们所需要的session_key，最后将数据$decodeData发给wxBizDataCrypt.php来解析.
<pre>
http_curl("https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=$grant_type");
</pre>

最后使用php curl模拟https请求
<pre>
//模拟https
function http_curl($url){
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,30);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    // https请求 不验证证书和hosts
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    //执行命令
    $response=curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //返回数据
    return $response;
}
</pre>

其中wxBizDataCrypt.php、errorCode.php、pkcs7Encoder.php都是官方提供的解密文件无需修改。

#### 4.官方解密文件及演示 ####

**[官方解密文件下载](https://developers.weixin.qq.com/miniprogram/dev/demo/aes-sample.zip "解密文件下载")**

演示：

![演示](http://www.baidu.com)
