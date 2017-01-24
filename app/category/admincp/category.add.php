<?php /**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
defined('iPHP') OR exit('What are you doing?');
admincp::head();
?>
<script type="text/javascript">
$(function(){
	iCMS.select('pid',"<?php echo $rs['pid']?$rs['pid']:0 ; ?>");
	iCMS.select('mode',"<?php echo $rs['mode'] ; ?>");

	iCMS.select('isucshow',"<?php echo $rs['isucshow'] ; ?>");
	iCMS.select('issend',"<?php echo $rs['issend'] ; ?>");
	iCMS.select('isexamine',"<?php echo $rs['isexamine'] ; ?>");

	$(document).on("click",".delprop",function(){
   		$(this).parent().parent().remove();
	});
	$(".addprop").click(function(){
    var href = $(this).attr("href");
    var tb   = $(href),tbody=$("tbody",tb);
    var ntr  = $(".aclone",tb).clone(true).removeClass("hide aclone");
		$('input',ntr).removeAttr("disabled");
		ntr.appendTo(tbody);
		return false;
	});
  $("#mode").change(function(event) {
    if(this.value=="0"){
      $("#mode-box").hide();
    }else{
      $("#mode-box").show();
    }
  });
});
</script>
<style>
.category_rule>li>a{width: auto;}
.category_rule:before {
    position: absolute;
    top: -7px;
    left: 9px;
    display: inline-block;
    border-right: 7px solid transparent;
    border-bottom: 7px solid #ccc;
    border-left: 7px solid transparent;
    border-bottom-color: rgba(0,0,0,0.2);
    content: ''
}

.category_rule:after {
    position: absolute;
    top: -6px;
    left: 10px;
    display: inline-block;
    border-right: 6px solid transparent;
    border-bottom: 6px solid #fff;
    border-left: 6px solid transparent;
    content: ''
}
</style>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5 class="brs"><?php echo empty($this->cid)?'添加':'修改' ; ?><?php echo $this->category_name;?></h5>
      <ul class="nav nav-tabs" id="category-add-tab">
        <li class="active"><a href="#category-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#category-add-url" data-toggle="tab"><i class="fa fa-link"></i> URL规则设置</a></li>
        <li><a href="#category-add-tpl" data-toggle="tab"><i class="fa fa-columns"></i> 模版设置</a></li>
        <li><a href="#category-add-user" data-toggle="tab"><i class="fa fa-user"></i> 用户设置</a></li>
        <li><a href="#category-add-prop" data-toggle="tab"><i class="fa fa-wrench"></i> <?php echo $this->category_name;?>附加属性</a></li>
        <li><a href="#category-add-body" data-toggle="tab"><i class="fa fa-wrench"></i> HTML</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo $this->category_furi; ?>&do=save" method="post" class="form-inline" id="iCMS-category" target="iPHP_FRAME">
        <input name="cid" type="hidden" value="<?php echo $rs['cid']  ; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid']  ; ?>" />
        <div id="category-add" class="tab-content">
          <div id="category-add-base" class="tab-pane active">
            <div class="input-prepend input-append"> <span class="add-on">上级<?php echo $this->category_name;?></span>
              <?php if(admincp::CP($rootid) || empty($rootid)) {   ?>
              <select name="rootid" class="span6 chosen-select">
                <option value="0">======顶级<?php echo $this->category_name;?>=====</option>
                <?php echo $this->select('a',$rootid,0,1,true);?>
              </select>
              <span class="add-on" title="继承"><input type="checkbox" name="extends" /> 继承</span>
              <?php }else { ?>
              <input name="rootid" id="rootid" type="hidden" value="<?php echo $rootid ; ?>" />
              <input readonly="true" value="<?php echo $this->get($rootid)->name ; ?>" type="text" class="txt" />
              <?php } ?>
            </div>
            <span class="help-inline">本<?php echo $this->category_name;?>的上级<?php echo $this->category_name;?>或<?php echo $this->category_name;?></span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><?php echo $this->category_name;?>属性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" data-placeholder="请选择<?php echo $this->category_name;?>属性..." multiple="multiple">
                <option value="0">普通<?php echo $this->category_name;?>[pid='0']</option>
                <?php echo propAdmincp::get("pid") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><?php echo $this->category_name;?>名称</span>
              <?php if(empty($this->cid)){?>
              <textarea name="name" id="name" class="span6" style="height: 150px;width:600;"><?php echo $rs['name'] ; ?></textarea>
              <?php }else{?>
              <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
              <?php }?>
            </div>
            <?php if(empty($this->cid)){?>
            <span class="help-inline"><span class="label label-important">可批量添加<?php echo $this->category_name;?>,每行一个</span></span>
            <?php }?>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><?php echo $this->category_name;?>别名</span>
              <input type="text" name="subname" class="span6" id="subname" value="<?php echo $rs['subname'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图2</span>
              <input type="text" name="mpic" class="span6" id="mpic" value="<?php echo $rs['mpic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("mpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图3</span>
              <input type="text" name="spic" class="span6" id="spic" value="<?php echo $rs['spic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("spic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">SEO 标题</span>
              <input type="text" name="title" class="span6" id="title" value="<?php echo $rs['title'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">关 键 字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on"><?php echo $this->category_name;?>简介</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;width:600;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">外部链接</span>
              <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
            </div>
            <span class="help-inline"><span class="label label-important">外部链接设置后所有项目无效,此<?php echo $this->category_name;?>仅为一个链接.不设置请留空</span></span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><?php echo $this->category_name;?>排序</span>
              <input id="sortnum" class="span1" value="<?php echo $rs['sortnum'] ; ?>" name="sortnum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><?php echo $this->category_name;?>状态</span>
              <div class="switch" data-on-label="显示" data-off-label="隐藏">
                <input type="checkbox" data-type="switch" name="status" id="status" <?php echo $rs['status']?'checked':''; ?>/>
              </div>
            </div>
          </div>
          <div id="category-add-url" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">访问模式</span>
              <select name="mode" id="mode" class="chosen-select span3">
                <option value="0">动态</option>
                <option value="1">静态</option>
                <option value="2">伪静态</option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">绑定域名</span>
              <input type="text" name="domain" class="span3" id="domain" value="<?php echo $rs['domain'] ; ?>"/>
            </div>
            <span class="help-inline">例 http://test.idreamsoft.com</span>
            <div class="clearfloat mb10"></div>
            <div id="mode-box" class="<?php if(!$rs['mode']){ echo ' hide';}?>">
              <div class="input-prepend"> <span class="add-on">静态目录</span>
                <input type="text" name="dir" class="span3" id="dir" value="<?php echo $rs['dir'] ; ?>"/>
              </div>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">静态后缀</span>
                <input type="text" name="htmlext" class="span3" id="htmlext" value="<?php echo $rs['htmlext'] ; ?>"/>
              </div>
              <div class="clearfloat mb10"></div>
              <?php include admincp::view('category.rule',$this->_view_tpl_dir);?>
            </div>
          </div>
          <div id="category-add-tpl" class="tab-pane hide">
              <?php include admincp::view('category.template',$this->_view_tpl_dir);?>
          </div>
          <div id="category-add-user" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">用户中心</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="isucshow" id="isucshow" <?php echo $rs['isucshow']?'checked':''; ?>/>
              </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">支持投稿</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="issend" id="issend" <?php echo $rs['issend']?'checked':''; ?>/>
              </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">审核投稿</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="isexamine" id="isexamine" <?php echo $rs['isexamine']?'checked':''; ?>/>
              </div>
            </div>
          </div>
          <div id="category-add-prop" class="tab-pane hide">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>名称<span>(只能由英文字母、数字或_-组成(不支持中文))<span></th>
                  <th>值</th>
                </tr>
              </thead>
              <tbody>
                <?php if($rs['metadata'])foreach((array)$rs['metadata'] AS $mKey=>$mvalue){?>
                <tr>
                  <td><input name="metadata[key][]" type="text" value="<?php echo $mKey;?>" class="span3" /></td>
                  <td><input name="metadata[value][]" type="text" value="<?php echo $mvalue;?>" class="span6" />
                    <a class="btn delprop"><i class="fa fa-trash-o"></i> 删除</a></td>
                </tr>
                <?php }?>
              </tbody>
              <tfoot>
                <tr class="hide aclone">
                  <td><input name="metadata[key][]" type="text" disabled="disabled" class="span3" value=""/></td>
                  <td><input name="metadata[value][]" type="text" disabled="disabled" class="span6" value="" />
                    <a class="btn delprop"><i class="fa fa-trash-o"></i> 删除</a></td>
                </tr>
                <tr>
                  <td colspan="2"><a href="#category-add-prop" class="btn addprop"/>增加附加属性</a></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div id="category-add-body" class="tab-pane hide">
            <script type="text/javascript" charset="utf-8" src="./app/admincp/ui/iCMS.ueditor.js"></script>
            <script type="text/javascript" charset="utf-8" src="./app/admincp/ui/ueditor/ueditor.all.min.js"></script>
            <a class="btn btn-inverse" href="javascript:iCMS.editor.cleanup();"><i class="fa fa-magic"></i> 自动排版</a>
            <a class="btn btn-success" href="javascript:createEditor();"><i class="fa fa-times"></i> 使用编辑器</a>
            <a class="btn btn-inverse" href="javascript:deleteEditor();"><i class="fa fa-times"></i> 关闭编辑器</a>
            <div class="clearfix mt10"></div>
            <input name="hasbody" id="hasbody" type="hidden" value="<?php echo $rs['hasbody'] ; ?>" />
            <textarea type="text/plain" id="iCMS-editor-1" name="body"><?php echo $rs['body'] ; ?></textarea>
            <div class="clearfix mt10"></div>
            <div class="alert alert-block">
              <h4>注意事项</h4>
              大文本段,支持HTML,至于干嘛用,我也不知道...你爱怎么用就怎么用!!
              <br />
              注:编辑器会把div转换成p
            </div>
            <script type="text/javascript">
            <?php if($rs['hasbody']=="1"){ ?>
            iCMS.editor.create();
            <?php } ?>
            function deleteEditor() {
                ed = iCMS.editor.get(1);
                ed.destroy();
                $("#hasbody").val('2');
            }
            function createEditor() {
                iCMS.editor.create();
                $("#hasbody").val('0');
            }
            </script>
            <?php if($rs['hasbody']=="2"){ ?>
            <style>
              #iCMS-editor-1 {height: 600px;width: 98%;}
            </style>
            <?php } ?>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
