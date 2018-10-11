//index.js 获取手机号的小例子，如有疑问，请至https://www.huxinchun.com与我交流
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
          url: 'https://www.demo.com/demo/getphone.php', //接口请求地址
          data: {
            appid: " ", //小程序appid，登录微信后台查看
            secret: " ", //小程序secret，登录微信后台可查看
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