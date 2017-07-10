<template>
    <div id="app">
        <div class="container container-detail" style="height: 100%">
            <!--<form action="">-->
            <search
                    @result-click="resultClick"
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
                        <cell :title="titlename"></cell>
                        <x-textarea :max="200" name="detail" :show-counter="false" v-model="reason">

                        </x-textarea>
                    </group>
                </div>
            </div>
            <div class="time-panel mt10">
                <group>
                    <datetime title="开始时间" v-model="minuteListValue" format="YYYY-MM-DD HH:mm"
                              :minute-list="['00', '15', '30', '45']"></datetime>
                    <datetime title="截止时间" v-model="minuteListValue2" format="YYYY-MM-DD HH:mm"
                              :minute-list="['00', '15', '30', '45']"></datetime>
                    <popup-picker title="申请时长" :data="list_time" v-model="list_time_default"></popup-picker>
                </group>
            </div>
            <div class="pass-panel">
                <div class="pass-title">
                    <!--<x-switch title="审批人" v-model="passMan"></x-switch>-->
                    <cell title="审批人" ></cell>
                </div>
                <group>
                    <cell>
                        <div id="select-passman">
                            <div class="options_man" v-if="isShowManOne" @click="confirm_del_one">
                                <span>{{oneValue}}</span><img class="del" src="../../images/cancel-circle.png"
                                                              alt="" v-if="isCancel"></div>
                            <div class="options_man" v-if="isShowManTwo" @click="confirm_del_two">
                                <span>{{otherValue}}</span><img class="del" src="../../images/cancel-circle.png"
                                                                alt="" v-if="isCancel"></div>
                            <div class="options_man" @click="add_pass_man"><img class="man"
                                                                                src="../../images/user-plus.png"
                                                                                alt=""></div>
                            <div class="options_man" @click="del_pass_man"><img class="man"
                                                                                src="../../images/user-minus.png"
                                                                                alt=""></div>
                        </div>
                    </cell>
                </group>
            </div>
            <div class="pass-panel">
                <div class="pass-title">
                    <!--<x-switch title="相关人" v-model="busiMan"></x-switch>-->
                    <cell title="相关人" ></cell>
                </div>
                <group>
                    <cell>
                        <div class="options_man" v-if="isShowRelateManOne" @click="confirm_del_relate_one"><span>{{oneRelate}}</span><img
                                class="del" src="../../images/cancel-circle.png" alt="" v-if="isCancelRelate"></div>
                        <div class="options_man" v-if="isShowRelateManTwo" @click="confirm_del_relate_two"><span>{{twoRelate}}</span><img
                                class="del" src="../../images/cancel-circle.png" alt="" v-if="isCancelRelate"></div>
                        <div class="options_man" @click="add_relate_man"><img class="man"
                                                                              src="../../images/user-plus.png"
                                                                              alt=""></div>
                        <div class="options_man" @click="del_relate_man"><img class="man"
                                                                              src="../../images/user-minus.png"
                                                                              alt=""></div>
                    </cell>
                </group>
            </div>
            <flexbox class="mt10">
                <flexbox-item>
                    <!--<x-button type="primary" @click.native="submitFormDraft">保存为草稿</x-button>-->
                    <x-button type="warn"   @click.native="closeSubmit">取消并返回</x-button>
                </flexbox-item>
                <flexbox-item>
                    <x-button type="primary" @click.native="submitForm">立即提交</x-button>
                </flexbox-item>
            </flexbox>
            <!--<flexbox class="mt10">-->
                    <!--<x-button type="primary" @click.native="closeSubmit">取消并返回</x-button>-->
            <!--</flexbox>-->
            <!--</form>-->
        </div>
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
        Masker,
        AjaxPlugin
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
                type_list: [['事假', '病假', '加班', '年假', '婚假', '丧假', '产假', '产检假', '陪产假']],
                type_list_default: ['事假'],
                results: [],
                passVal: '',
                relateVal: '',
                isShow: false,
                isShowRelate: false,
                isShowManOne: false,
                isShowManTwo: false,
                isShowRelateManOne: false,
                isShowRelateManTwo: false,
                isCancel: false,
                isCancelRelate: false,
                otherValue: '',
                oneValue: '',
                oneRelate: '',
                twoRelate: '',
                created_at: this.format(new Date(), 'yyyy-MM-dd'),
                oneJobNumber:'',
                otherJobNumber:'',
                oneRelateJobnumber:'',
                twoRelateJobnumber:''
            }
        },
        props: [
            'title'
        ],
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
            Masker,
            AjaxPlugin
        },
        computed: {
            reason: function () {
                var minuteListValue = this.minuteListValue;
                var minuteListValue2 = this.minuteListValue2;
                var type = this.type_list_default;
                return '我因【】于【' + minuteListValue + '至' + minuteListValue2 + '】请假,类型为' + type + '请审批!';
            },
            titlename: function () {
                return this.title + '_' + this.type_list_default + '_' + this.created_at;
            }
        },
        methods: {
            closeSubmit(){
                window.history.back(-1);
            },
            submitFormDraft(){
                let formData = {
                    type: this.type_list_default[0],
                    title: this.titlename,
                    reson: this.reason,
                    start_at: this.minuteListValue,
                    end_at: this.minuteListValue2,
                    continued_at: this.list_time_default,
                    approver: [this.oneJobNumber, this.otherJobNumber],
                    relevant: [this.oneRelateJobnumber, this.twoRelateJobnumber],
                    created_at: this.created_at,
                    status: 0,
                }
                AjaxPlugin.$http.post('/wechat/applyAttendance', formData)
                        .then((response) => {
                            if (response.data.status == true) {
                                window.history.back(-1);
                            }else {
                                //
                            }
                        })

            },
            submitForm(){
                let formData = {
                    type: this.type_list_default[0],
                    title: this.titlename,
                    reson: this.reason,
                    start_at: this.minuteListValue,
                    end_at: this.minuteListValue2,
                    continued_at: this.list_time_default,
                    approver: [this.oneJobNumber, this.otherJobNumber],
                    relevant: [this.oneRelateJobnumber, this.twoRelateJobnumber],
                    created_at: this.created_at,
                    status: 1,
                }
                AjaxPlugin.$http.post('/wechat/applyAttendance', formData)
                        .then((response) => {
                            if (response.data.status == true) {
                                window.history.back(-1);
                            }
                        })

            },
            confirm_del_one(){
                if (this.isCancel == true) {
                    this.isCancel = false;
                    this.oneJobNumber='';
                    this.isShowManOne = false;
                }
            },
            confirm_del_two(){
                if (this.isCancel == true) {
                    this.isCancel = false;
                    this.otherJobNumber='';
                    this.isShowManTwo = false;
                }
            },
            confirm_del_relate_one(){
                if (this.isCancelRelate == true) {
                    this.isCancelRelate = false;
                    this.oneRelateJobnumber='';
                    this.isShowRelateManOne = false;
                }
            },
            confirm_del_relate_two(){
                if (this.isCancelRelate == true) {
                    this.isCancelRelate = false;
                    this.twoRelateJobnumber='';
                    this.isShowRelateManTwo = false;
                }
            },
            add_pass_man(){
                AjaxPlugin.$http.post('/wechat/getUsersList')
                    .then((response) => {
                        var data = response.data.data;
                        this.getResult(data);
                    });
                this.isShow = true;
            },
            add_relate_man(){
                AjaxPlugin.$http.post('/wechat/getUsersList')
                        .then((response) => {
                            var data = response.data.data;
                            this.getResult(data);
                        });
                this.isShowRelate = true;
            },
            del_pass_man(){
                this.isCancel = true;
            },
            del_relate_man(){
                this.isCancelRelate = true;
            },
            resultClick (item) {
                this.isShow = false;
                this.isCancel = false;
                if (this.oneValue == '' || this.isShowManOne == false) {
                    this.oneValue = item.title;
                    this.oneJobNumber = item.job_number;
                    this.isShowManOne = true;
                } else {
                    this.otherValue = item.title;
                    this.otherJobNumber = item.job_number;
                    this.isShowManTwo = true;
                }
            },
            resultClick2(item){
                this.isShowRelate = false;
                this.isCancelRelate = false;
                if (this.oneRelate == '' || this.isShowRelateManOne == false) {
                    this.oneRelate = item.title;
                    this.oneRelateJobnumber =  item.job_number;
                    this.isShowRelateManOne = true;
                } else {
                    this.twoRelate = item.title;
                    this.twoRelateJobnumber =  item.job_number;
                    this.isShowRelateManTwo = true;
                }
            },
            getResult (data) {
                let rs = []
                console.log(data)
                for (var value in data) {
                    rs.push({
                        title: data[value],
                        job_number: value
                    })
                }
                this.results = rs
            },
            format (date, fmt) { //author: meizz
                var o = {
                    "M+": date.getMonth() + 1, //月份
                    "d+": date.getDate(), //日
                    "h+": date.getHours(), //小时
                    "m+": date.getMinutes(), //分
                    "s+": date.getSeconds(), //秒
                    "q+": Math.floor((date.getMonth() + 3) / 3), //季度
                    "S": date.getMilliseconds() //毫秒
                };
                if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
                for (var k in o)
                    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
                return fmt;
            },
            onCancel () {
                this.isShow = false;
            },
            onCancelRelate(){
                this.isShowRelate = false;
            }
        }
    }
</script>

<style lang="less">
    @import '../../../../node_modules/vux/src/styles/reset.less';
</style>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style type="text/css">
    #app {
        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    html {
        font-size: 100px;
        height: 100%;
    }

    body {
        font-size: .14em;
        line-height: 1.5;
        font-family: Arial, Helvetica, sans-serif;
        height: 100%;
        margin: 0;
        background-color: #f0f0f0;
    }

    .clearfix:after {
        content: " ";
        display: table;
    }

    .clearfix:after {
        clear: both;
    }

    .options_man {
        width: .5rem;
        height: .5rem;
        border: 1px solid #dddddd;
        float: left;
        margin-left: .1rem;
        position: relative;
    }

    .options_man .man {
        position: relative;
        top: .08rem;
        left: -.06rem;;
    }

    .options_man .del {
        position: absolute;
        top: 0;
        right: 0;
        width: 18px;
    }

    .options_man span {
        text-align: center;
        display: block;
        line-height: 52px
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
</style>
