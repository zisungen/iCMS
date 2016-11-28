define("common", function(require) {
    var API = require("api"),utils = require("utils");;
    return {
        __post: function(param,uri,SUCCESS,FAIL) {
            var me = this;
            $.post(API.url(uri), param, function(ret) {
                utils.callback(ret, SUCCESS, FAIL,me,param);
            }, 'json');
        },
        vote: function(a, SUCCESS, FAIL) {
            var vars = iCMS.$v(a,'vote');
            var param = API.param(a);
            param = $.extend(param, {
                'type': vars[1],
                'action': 'vote'
            });
            this.__post(param,vars[0],SUCCESS,FAIL);
        },
        favorite: function(a,callback) {
            var me = this,USER = require("user"),UI = require("ui")
            var auth = USER.AUTH();
            if (!auth) {
              return USER.LOGIN();
            }

            var $this = $(a),
            box       = document.getElementById("iCMS-FAVORITE-DIALOG"),
            dialog    = UI.dialog({
                title: '添加到收藏夹',content:box,
                quickClose: false,width:"auto",height:"auto"
            });
            //console.log(dialog);
            $(".favorite_list_content",box).empty();
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.remove();
            });
            $('.create,.cancel_create', box).click(function(event) {
                event.preventDefault();
                if($(this).hasClass('create')){
                    dialog.title("创建新收藏夹");
                }else{
                    dialog.title("添加到收藏夹");
                }
                $(".favorite_create",box).toggle();
                $(".favorite_list",box).toggle();
            });

            $('[name="create"]', box).click(function(event){
                event.preventDefault();
                var data = {
                    'action':'create',
                    'title':$('[name="title"]',box).val(),
                    'description':$('[name="description"]',box).val(),
                    'mode':$('[name="mode"]:checked',box).val()
                }
                if(data.title==""){
                    $('[name="title"]',box).focus();
                    $('.favorite_create_error',box).text('请填写标题').show();
                    return false;
                }
                $.post(API.url('favorite'), data, function(c) {
                    if(c.code){
                        var item = __item({
                            'id':c.forward,
                            'title':data.title,
                            'count':0,'follow':0
                        });
                        $(".favorite_list_content",box).append(item);
                        $('[name="title"]',box).val('');
                        $('[name="description"]',box).val('');
                        $(".favorite_create",box).toggle();
                        $(".favorite_list",box).toggle();
                        dialog.reset();
                    }else{
                        $('.favorite_create_error',box).text(c.msg).show();
                    }
                }, 'json');
            });

            function __item(val){
                return '<a class="favo-list-item-link r5 " href="javascript:;" data-fid="'+val.id+'">'
                +'<span class="favo-list-item-title">'+val.title+'</span>'
                +'<span class="meta gray">'
                    +'<span class="num">'+val.count+'</span> 篇文章'
                    +'<span class="bull">•</span> '+val.follow+' 人关注'
                +'</span></a><div class="clearfix mt10"></div>';
            }

            $.get(API.url('favorite',"&do=list"),function(json) {
                var item ='';
                $.each(json, function(i,val){
                    item+=__item(val);
                });
                $(".favorite_list_content",box).html(item);
                dialog.reset();
            },'json');

            $(box).on("click", '.favo-list-item-link', function(event) {
                //console.log(this);
                var $this = $(this),
                data = API.param(a),
                num  = parseInt($('.num',$this).text());
                data.fid    = $this.attr('data-fid');
                if($this.hasClass('active')){
                    data.action = 'delete';
                }else{
                    data.action = 'add';
                }
                $.post(API.url('favorite'),data,function(c) {
                    if(c.code){
                        if($this.hasClass('active')){
                            $('.num',$this).text(num-1);
                            $this.removeClass('active');
                        }else{
                            $('.num',$this).text(num+1);
                            $this.addClass('active');
                        }
                    }else{
                        UI.alert(c.msg);
                    }
                },'json');
            });
        },
    };
});
