<template>
  <div class="container container-detail" style="height: 100%">
      <div class="info-panel">
        <p class="info_title">张三xxx</p>
        <p class="mt10"><span style="color: #999;margin-right: 12px;">2018-04-19</span>{{user}}</p>
        <ul class="mt10">
          <li>
            <span class="left_word">申请时长</span><span>{{reply_time}}</span>
          </li>
          <li>
            <span class="left_word">申请类型</span><span>{{reply_type}}</span>
          </li>
          <li>
            <span class="left_word">开始时间</span><span>{{begin_time}}</span>
          </li>
          <li>
            <span class="left_word">结束时间</span><span>{{over_time}}</span>
          </li>
        </ul>
        <p class="reason mt10">我因【{{reason}}】于 【{{minuteListValue}}】 至 【{{minuteListValue2}}】请假,类型为{{type}}请审批 ! </p>
        <flexbox class="mt10">
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
          <span>审批人({{passTotalNo}})</span>
        </div>
        <div v-if="passTotalNo > 0">
          <!--<cell>-->
            <div id="select-passman" class="clearfix">
              <div class="options_man"><span></span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
              <div class="options_man"><span></span><img class="man" src="../../src/assets/user-minus.png" alt=""></div>
            </div>
          <!--</cell>-->
        </div>
      </div>
      <div class="pass-panel">
        <div class="pass-title" style="border-top: 1px solid #dddddd">
          <span>相关人({{relateTotalNo}})</span>
        </div>
        <div v-if="relateTotalNo > 0">
          <!--<cell>-->
            <div id="select-relateman" class="clearfix">
              <div class="options_man"><span></span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
              <div class="options_man"><span></span><img class="man" src="../../src/assets/user-minus.png" alt=""></div>
            </div>
          <!--</cell>-->
        </div>
      </div>
      <div class="pass-panel">
        <div class="pass-title" style="border-top: 1px solid #dddddd">
          <span>回复({{answerTotalNo}})</span>
        </div>
        <div v-if="answerTotalNo > 0">
            <template v-for="item in answerList">
              <div id="select-answer" class="clearfix">
                <div class="options_man"><span></span><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
                <span class="answer_info">{{answer_info}}</span>
                <span class="answer_time">{{answer_time}}</span>
              </div>
            </template>
        </div>
      </div>
  </div>
</template>

<script>
  import { XButton , Flexbox, FlexboxItem,Cell ,Group} from 'vux'
  export default {
    name: 'hello',
    data () {
      return {
        user:'张三',
        passTotalNo:0,
        relateTotalNo:2,
        answerTotalNo:3,
        reply_time:'1天',
        reply_type:'事假',
        begin_time:'2017-12-02',
        over_time:'2017-12-02',
        reason:'XXX',
        minuteListValue:'2017-12-02',
        minuteListValue2:'2017-12-02',
        type:'事假',
        answerList:[
          {name:'王五'},
          {name:'赵四'},
          {name:'甜甜'}
        ],
        answer_info:'同意',
        answer_time:'2017-12-02'
      }
    },
    components: {XButton ,Flexbox, FlexboxItem ,Cell ,Group},
    computed: {
    },
    methods: {}
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
