//index.js
import WxResource from "../../plugins/wx-resource/lib/index.js"

Page({
  data: {
    data: []
  },
  onLoad(){
    this.WxResource = new WxResource('http://127.0.0.1:8000/wechat/lists/:id', {
      id: '@id',
    })
  },
  onReady() {
    this.WxResource.getAsync().then(({data}) => {
      this.setData({
        data: data.data.data
      });
    });
  }
})
