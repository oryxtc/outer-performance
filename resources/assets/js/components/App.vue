<template>
  <div id="app">
    <info></info>
  </div>
</template>

<script>
    import info from './components/info'

    export default {
        name: 'app',
        components: {
            info
        }
    }
</script>
<style lang="less">
  @import '../../../../node_modules/vux/src//styles/reset.less';
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
    font-family: Arial,Helvetica,sans-serif;
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
</style>