<template>
  <div class="container container-detail" style="height: 100%">
           <search
             @result-click="resultClick"
             @on-change="getResult1"
             v-model="passVal"
             :results="results"
             position="absolute"
             auto-scroll-to-top top="0"
             @on-cancel="onCancel"
             ref="search"
             v-if="isShow"
           ></search>
            <search
              @result-click="resultClick2"
              @on-change="getResult2"
              v-model="relateVal"
              :results="results"
              position="absolute"
              auto-scroll-to-top top="0"
              @on-cancel="onCancelRelate"
              ref="search"
              v-if="isShowRelate"
            ></search>
           <div class="reason-panel">
             <div class="header-wrap">
               <popup-picker title="类型" :data="type_list" v-model="type_list_default"></popup-picker>
             </div>
             <div class="reason-title">
               <group>
                 <cell title="name"></cell>
                 <x-textarea :max="200" name="detail" :show-counter="false" v-model="reason">

                 </x-textarea>
               </group>
             </div>
           </div>
           <div class="time-panel mt10">
             <group>
               <datetime title="开始时间" v-model="minuteListValue" format="YYYY-MM-DD HH:mm"
                         :minute-list="['00', '15', '30', '45']" @on-change="change"></datetime>
               <datetime title="截止时间" v-model="minuteListValue2" format="YYYY-MM-DD HH:mm"
                         :minute-list="['00', '15', '30', '45']" @on-change="change"></datetime>
               <popup-picker title="申请时长" :data="list_time" v-model="list_time_default"></popup-picker>
             </group>
           </div>
           <div class="pass-panel">
             <div class="pass-title">
               <!--<group>-->
               <x-switch title="审批人" v-model="passMan"></x-switch>
               <!--</group>-->
             </div>
             <group>
               <cell>
                 <div id="select-passman">
                   <div class="options_man" v-if="isShowManOne" @click="confirm_del_one"><span>{{oneValue}}</span><img class="del" src="../../src/assets/cancel-circle.png" alt="" v-if="isCancel"></div>
                   <div class="options_man" v-if="isShowManTwo" @click="confirm_del_two"><span>{{otherValue}}</span><img class="del" src="../../src/assets/cancel-circle.png" alt="" v-if="isCancel"></div>
                   <div class="options_man" @click="add_pass_man"><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
                   <div class="options_man" @click="del_pass_man"><img class="man" src="../../src/assets/user-minus.png" alt=""></div>
                 </div>
               </cell>
             </group>
           </div>
           <div class="pass-panel">
             <div class="pass-title">
               <!--<group>-->
               <x-switch title="相关人" v-model="busiMan"></x-switch>
               <!--</group>-->
             </div>
             <group>
               <cell>
                 <div class="options_man" v-if="isShowRelateManOne" @click="confirm_del_relate_one"><span>{{oneRelate}}</span><img class="del" src="../../src/assets/cancel-circle.png" alt="" v-if="isCancelRelate"></div>
                 <div class="options_man" v-if="isShowRelateManTwo" @click="confirm_del_relate_two"><span>{{twoRelate}}</span><img class="del" src="../../src/assets/cancel-circle.png" alt="" v-if="isCancelRelate"></div>
                 <div class="options_man" @click="add_relate_man"><img class="man" src="../../src/assets/user-plus.png" alt=""></div>
                 <div class="options_man" @click="del_relate_man"><img class="man" src="../../src/assets/user-minus.png" alt=""></div>
               </cell>
             </group>
           </div>
           <flexbox class="mt10">
             <flexbox-item>
               <x-button>保存为草稿</x-button>
             </flexbox-item>
             <flexbox-item>
               <x-button type="primary">立即提交</x-button>
             </flexbox-item>
           </flexbox>
  </div>
</template>

<script>
  import {
    Datetime,
    Group,
    XSwitch,
    XTextarea,
    Selector,
    Cell,
    Flexbox,
    XButton,
    FlexboxItem,
    PopupPicker,
    Search,
    Masker
  } from 'vux'

  let hours = []
  let day = []
  for (var i = 0; i <= 30; i++) {
    if (i <= 23) {
      hours.push(i)
    }
    day.push(i)
  }

  export default {
    name: 'hello',
    data () {
      return {
        name: '张三',
        reason_text: 'xxxx',
        value1: true,
        value2: false,
        title: '加載上次 ',
        minuteListValue: '',
        minuteListValue2: '',
        detail: '',
        list: [{key: 'matter', value: '事假'}, {key: 'sick', value: '病假'}],
        time_range: '1天00',
        passMan: false,
        busiMan: false,
        continue_value: "申请时长",
        list_time: [day, ['天'], hours, ['小时']],
        list_time_default: ['0', '天', '0', '小时'],
        type_list: [['事假', '病假']],
        type_list_default: ['事假'],
        results: [],
        passVal: 'test',
        relateVal:'test',
        isShow: false,
        isShowRelate:false,
        isShowManOne: false,
        isShowManTwo: false,
        isShowRelateManOne:false,
        isShowRelateManTwo:false,
        isCancel:false,
        isCancelRelate:false,
        otherValue:'',
        oneValue:'',
        oneRelate:'',
        twoRelate:''
//        isShowMask:false
      }
    },
    components: {
      Datetime,
      Group,
      XSwitch,
      XTextarea,
      Selector,
      Cell,
      Flexbox,
      XButton,
      FlexboxItem,
      PopupPicker,
      Search,
      Masker
    },
    computed: {
      reason: function () {
        var minuteListValue = this.minuteListValue;
        var minuteListValue2 = this.minuteListValue2;
        var type = this.type_list_default;
        return '我因【】于【' + minuteListValue + '至' + minuteListValue2 + '】请假,类型为' + type + '请审批!';
      }
    },
    methods: {
      change (value) {
      },
      confirm_del_one(){
        if(this.isCancel==true){
          this.isCancel=false;
          this.isShowManOne=false;
        }
      },
      confirm_del_two(){
        if(this.isCancel==true){
          this.isCancel=false;
          this.isShowManTwo=false;
        }
      },
      confirm_del_relate_one(){
        if(this.isCancelRelate==true){
          this.isCancelRelate=false;
          this.isShowRelateManOne=false;
        }
      },
      confirm_del_relate_two(){
        if(this.isCancelRelate==true){
          this.isCancelRelate=false;
          this.isShowRelateManTwo=false;
        }
      },
      add_pass_man(){
        this.isShow = true;
      },
      add_relate_man(){
        this.isShowRelate = true;
      },
      del_pass_man(){
        this.isCancel=true;
      },
      del_relate_man(){
        this.isCancelRelate=true;
      },
      resultClick () {
        this.isShow = false;
        this.isCancel=false;
        if(this.oneValue=='' || this.isShowManOne==false){
          this.oneValue=this.passVal;
          this.isShowManOne = true;
        }else{
          this.otherValue=this.passVal;
          this.isShowManTwo = true;
        }
      },
      resultClick2(){
        this.isShowRelate = false;
        this.isCancelRelate=false;
        if(this.oneRelate=='' || this.isShowRelateManOne==false){
          this.oneRelate=this.relateVal;
          this.isShowRelateManOne = true;
        }else{
          this.twoRelate=this.relateVal;
          this.isShowRelateManTwo = true;
        }
      },
      getResult1 (val) {
        this.results = val ? getResult1(this.passVal) : []
      },
      getResult2 (val) {
        this.results = val ? getResult2(this.relateVal) : []
      },
//      onSubmit () {
//        this.$refs.search.setBlur()
//        this.$vux.toast.show({
//          type: 'text',
//          position: 'top',
//          text: 'on submit'
//        })
//      },
      onCancel () {
        this.isShow = false;
      },
      onCancelRelate(){
        this.isShowRelate=false;
      }
    }
  }
  function getResult1(val) {
    let rs = []
    for (let i = 0; i < 20; i++) {
      rs.push({
        title: `${val} result: ${i + 1} `,
        other: i
      })
    }
    return rs
  }
  function getResult2(val) {
    let rs = []
    for (let i = 0; i < 20; i++) {
      rs.push({
        title: `${val} result: ${i + 1} `,
        other: i
      })
    }
    return rs
  }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="less">
  .options_man {
    width: .5rem;
    height: .5rem;
    border: 1px solid #dddddd;
    float: left;
    margin-left: .1rem;
    position: relative;

  img.man {
    position: relative;
    top: .08rem;
    left: -.06rem;;
  }
  img.del{
    position: absolute;
    top: 0;
    right: 0;
    width: 18px;
  }

  span {
    text-align: center;
    display: block;
    line-height: 52px
  }

  }
  .mt10 {
    margin-top: 10px;;
  }

  .weui-label {
    display: inline;
  }

  .weui-cells {
    margin-top: 0;
  }

  .vux-no-group-title {
    margin-top: 0;
  }

  .pass-title {

  }

  /*.mt10{*/
  /*margin-top: .1rem;*/
  /*}*/
  /*.container{*/
  /*min-height: 100%;*/
  /*padding: 0;*/
  /*!*display: flex;*!*/
  /*!*align-items: center;*!*/
  /*!*flex-direction: column;*!*/
  /*!*justify-content: center;*!*/
  /*}*/
  /*.header-wrap{*/
  /*border-bottom: 1px solid #dddddd;*/
  /*!*padding:.2rem .16rem;*!*/
  /*}*/
  /*.reason-title{*/
  /*padding:.2rem .16rem;*/
  /*background-color: #ffffff;*/
  /*}*/
  /*.time-panel{*/
  /*padding:0 .16rem;*/
  /*border-top: 1px solid #dddddd;*/
  /*border-bottom: 1px solid #dddddd;*/
  /*background-color: #ffffff;*/
  /*}*/
  /*.start-time,.end-time,.time-calc{*/
  /*border-bottom: 1px solid #dddddd;*/
  /*padding: .2rem 0;*/
  /*}*/
  /*.time-calc{*/
  /*border-bottom: none;*/
  /*}*/
  /*.pass-panel,.related-panel{*/
  /*border-top: 1px solid #dddddd;*/
  /*border-bottom: 1px solid #dddddd;*/
  /*}*/
  /*.pass-title{*/
  /*padding: 0.2rem .16rem;*/
  /*}*/
  /*.select-man{*/
  /*background-color: #ffffff;*/
  /*padding: .4rem .25rem;*/
  /*}*/
  /*.options{*/
  /*float: right;*/
  /*}*/
  /*.add-man,.remove-man{*/
  /*width: .6rem;*/
  /*height:.6rem;*/
  /*border:1px solid #dddddd;*/
  /*float: left;*/
  /*margin-right: .4rem;*/
  /*}*/
  /*.add-man i,.remove-man i{*/
  /*display: block;*/
  /*line-height: .6rem;*/
  /*text-align: center;*/
  /*}*/
</style>
