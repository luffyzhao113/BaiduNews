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
   * 组件的初始数据
   */
  data: {

  },

  /**
   * 组件的方法列表
   */
  methods: {
    itemBindtap(event){
      wx.navigateTo({
        url: "../../pages/detail/index?id=" + event.target.dataset.id
      });
    }
  }
})
