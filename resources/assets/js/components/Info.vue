<template>
    <div id="app">
        <div class="container container-detail" style="height: 100%">
            <div class="info-panel">
                <p class="info_title">{{userData.title}}</p>
                <p class="mt10"><span
                        style="color: #999;margin-right: 12px;">{{userData.username}}</span>申请时间:{{userData.created_at}}</p>
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
                <div v-if="approver.length > 0">
                    <template v-for="item in approver">
                        <div id="select-passman" class="clearfix">
                            <div class="options_man"><span>{{item}}</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="pass-panel">
                <div class="pass-title" style="border-top: 1px solid #dddddd">
                    <span>相关人({{relevant.length}})</span>
                </div>
                <div v-if="relevant.length > 0">
                    <template v-for="item in relevant">
                        <div id="select-relateman" class="clearfix">
                            <div class="options_man"><span>{{item}}</span>
                            </div>
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
                            <div class="options_man"><span>{{item.name}}</span></div>
                            <span class="answer_info">{{item.status}}</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {XButton, Flexbox, FlexboxItem, Cell, Group, AjaxPlugin} from 'vux'
    export default {
        name: 'hello',
        data () {
            return {
                userData: {},
                approver: [],
                relevant: [],
                retrial: [],
            }
        },
        mounted: function () {
            this.getAttendanceInfo()
        },
        components: {XButton, Flexbox, FlexboxItem, Cell, Group, AjaxPlugin},
        computed: {},
        methods: {
            getAttendanceInfo (){
                let dataStr = {"id": 1};
                AjaxPlugin.$http.post('/wechat/getAttendanceInfo', dataStr)
                    .then((response) => {
                        this.userData = response.data.data.info;
                        this.approver = response.data.data.approver;
                        this.relevant = response.data.data.relevant;
                        this.retrial = response.data.data.retrial;
                    })
            }
        },

    }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="less">
    @import '../../../../node_modules/vux/src/styles/reset.less';
</style>

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

    .mt10 {
        margin-top: 10px;
    }

    ul li {
        list-style-type: none;
    }

    ul li .left_word {
        color: #818181;
        margin-right: 27px;
    }

    .info-panel {
        width: 100%;
        min-height: 300px;
        background-color: #ffffff;
        border-bottom: 1px solid #dddddd;
        padding: 10px 20px;

    }

    .info-panel .info_title {
        font-size: 21px;
    }

    .info-panel .reason {
        width: 80%;
    }

    .options_man {
        width: .5rem;
        height: .5rem;
        border: 1px solid #dddddd;
        float: left;
        margin-left: .1rem;
        position: relative;
    }
    .options_man span {
        text-align: center;
        display: block;
        line-height: 52px
    }

    .answer_info {
        display: inline-block;
        position: relative;
        top: 30px;
        left: 20px;
    }

    .pass-title {
        border-bottom: 1px solid #dddddd;
        padding: 6px 30px;
        color: #999;
    }

    #select-answer, #select-passman, #select-relateman {
        background-color: #ffffff;
        border-bottom: 1px solid #dddddd;
        padding: 20px;
    }

    .answer_time {
        float: right;
        color: #999;
    }

    .clearfix:before {
    }

    .clearfix:after {
        content: " ";
        display: table;
        clear: both;
    }
</style>