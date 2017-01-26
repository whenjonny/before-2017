$(function(){ 
    $(".follow").click(function() {
        var uid = $(this).attr("data");

        $.post("/api/follow", {
            uid: uid
        }, function(data){
            if(data.ret == 1){
                alert("关注成功");
                location.reload();
            }
            else {
                alert("操作失败,稍后重试");
            }
        });
    });

    $(".unfollow").click(function() {
        var uid = $(this).attr("data");

        $.post("/api/unfollow", {
            uid: uid
        }, function(data){
            if(data.ret == 1){
                alert("取消关注成功");
                location.reload();
            }
            else {
                alert("操作失败,稍后重试");
            }
        });
    });
});
