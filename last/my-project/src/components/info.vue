<template>
  <div class="container container-detail" style="height: 100%">
      <div class="info-panel">
        <p class="info_title">{{userData.title}}</p>
        <p class="mt10"><span style="color: #999;margin-right: 12px;">{{userData.created_at}}</span>{{userData.username}}</p>
        <ul class="mt10">
          <li>
            <span class="left_word">申请时长</span><span>{{userData.continued_at}}</span>
          </li>
          <li>
            <span class="left_word">申请类型</span><span>{{userData.type}}</span>
          </li>
          <li>
            <span class="left_word">开始时间</span><span>{{userData.start_at}}</span>
          </li>
          <li>
            <span class="left_word">结束时间</span><span>{{userData.end_at}}</span>
          </li>
        </ul>
        <p class="reason mt10">{{userData.reson}}</p>
        <flexbox class="mt10" v-if="userData.can_review">
          <flexbox-item style="margin-right: 10px;">
              <x-button type="default">退审</x-button>
          </flexbox-item>
          <flexbox-item>
              <x-button type="primary">同意并结束</x-button>
          </flexbox-item>
        </flexbox>
      </div>
      <div class="pass-panel">
        <div class="pass-title">
          <span>审批人({{approver.length}})</span>
        </div>
        <div v-if="approver.length > 0" >
          <template v-for="item in approver">
              <div id="select-passman" class="clearfix">
                <div class="options_man"><span>{{item}}</span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
              </div>
          </template>
        </div>
      </div>
      <div class="pass-panel">
        <div class="pass-title" style="border-top: 1px solid #dddddd">
          <span>相关人({{relevant.length}})</span>
        </div>
        <div v-if="relevant.length > 0" >
          <template v-for="item in relevant">
              <div id="select-relateman" class="clearfix">
                <div class="options_man"><span>{{item}}</span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
              </div>
          </template>
        </div>
      </div>
      <div class="pass-panel">
        <div class="pass-title" style="border-top: 1px solid #dddddd">
          <span>回复({{retrial.length}})</span>
        </div>
        <div v-if="retrial.length > 0">
            <template v-for="item in retrial">
              <div id="select-answer" class="clearfix">
                <div class="options_man"><span>{{item.name}}</span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
                <span class="answer_info">{{item.status}}</span>
              </div>
            </template>
        </div>
      </div>
  </div>
</template>

<script>
  import { XButton , Flexbox, FlexboxItem,Cell ,Group,AjaxPlugin } from 'vux'
  export default {
    name: 'hello',
    data () {
      return {
        userData:{},
        approver:[],
        relevant:[],
        retrial:[],
      }
    },
    mounted:function () {
        this.getAttendanceInfo()
    },
    components: {XButton ,Flexbox, FlexboxItem ,Cell ,Group,AjaxPlugin },
    computed: {
    },
    methods: {
      getAttendanceInfo (){
        let dataStr={"id":1};
        AjaxPlugin.$http.post('http://www.performance.com/wechat/getAttendanceInfo', dataStr)
          .then((response) => {
//            if (cb) cb(response.data)
//            console.log(response.data)
            this.userData=response.data.info;
            this.approver=response.data.approver;
            this.relevant=response.data.relevant;
            this.retrial=response.data.retrial;
          })
      }
    },

  }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="less">
  .mt10{
    margin-top: 10px;
  }
  ul li{
    list-style-type: none;
    .left_word{
      color:#818181;
      margin-right:27px;
    }
  }
  .info-panel{
    width: 100%;
    min-height:300px;
    background-color: #ffffff;
    border-bottom: 1px solid #dddddd;
    padding: 10px 20px;
    .info_title{
      font-size: 21px;
    }
    .reason{
      width: 80%;
    }
  }
  .options_man{
    width: .5rem;
    height: .5rem;
    border: 1px solid #dddddd;
    float: left;
    margin-left: .1rem;
    position: relative;
    img.man {
      position: relative;
      top: .08rem;
      left:.1rem;
    }
    span {
      text-align: center;
      display: block;
      line-height: 52px
    }
  }
  .answer_info{
    display: inline-block;
    position: relative;
    top: 30px;
    left: 20px;
  }
  .pass-title{
    border-bottom: 1px solid #dddddd;
    padding: 6px 30px;
    color: #999;
  }
  #select-answer,#select-passman,#select-relateman{
    background-color: #ffffff;
    border-bottom: 1px solid #dddddd;
    padding: 20px;
  }
  .answer_time{
    float: right;
    color: #999;
  }

  .clearfix() {
      &:before,
      &:after {
         content: " ";
         display: table;
       }
      &:after {
         clear: both;
       }
  }
</style>
