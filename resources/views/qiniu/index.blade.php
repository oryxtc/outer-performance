
<body>
用户图像：<input id="userface" type="file" ><br>
<input id="token" name="token" type="hidden" value="{{$token}}">
<input type="button" id="btnClick" value="上传">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="application/javascript">
    $("#btnClick").click(function () {
        var formData = new FormData();
        formData.append("token", $("#token").val());
        formData.append("file", $("#userface")[0].files[0]);
        $.ajax({
            url: 'https://up-z2.qiniup.com',
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            success: function (msg) {
            }
        });
    });
</script>
</body>
</html>