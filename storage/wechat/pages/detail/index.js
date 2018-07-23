// pages/detail/index.js
import WxResource from "../../plugins/wx-resource/lib/index.js"

Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: {},
    data: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.WxResource = new WxResource('http://127.0.0.1:8000/wechat/lists/:id', {
      id: '@id',
    })
    this.setData(
      {
        options: options
      }
    );
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    this.WxResource.getAsync({id: this.options.id}).then(({data}) => {
      this.setData({
        data: data.data
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() {
    return {
      title: this.data.title,
    }
  },
  onShare(event){
    wx.showActionSheet({
      itemList: ['复制此新闻链接'],
      success({ tapIndex }) {
        if (tapIndex === 0) {
          wx.setClipboardData({
            data: event.target.dataset.link,
            success: function (res) {
              wx.showToast({
                title: '复制链接成功',
                duration: 2000
              })
            }
          })
        }
      }
    })
  }
})