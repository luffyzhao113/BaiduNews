// components/lists/index.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    lists: {
      type: Array,
      default: []
    }
  },

  /**
   * 组件的方法列表
   */
  methods: {
    itemBindtap(event){
      wx.navigateTo({
        url: "../../pages/detail/index?id=" + event.target.dataset.id
      });
    },
    onShare(event) {
      wx.showActionSheet({
        itemList: ['复制此新闻链接'],
        success({ tapIndex }) {
          if(tapIndex === 0){
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
  }
})
