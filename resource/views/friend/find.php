<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <style type="text/css">
    body {
      text-align: center
    }

    .layui-find-list li img {
      position: absolute;
      left: 15px;
      top: 8px;
      width: 36px;
      height: 36px;
      border-radius: 100%;
    }

    .layui-find-list li {
      position: relative;
      height: 90px;;
      padding: 5px 15px 5px 60px;
      font-size: 0;
      cursor: pointer;
    }

    .layui-find-list li * {
      display: inline-block;
      vertical-align: top;
      font-size: 14px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .layui-find-list li span {
      margin-top: 4px;
      max-width: 155px;
    }

    .layui-find-list li p {
      display: block;
      line-height: 18px;
      font-size: 12px;
      color: #999;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .back {
      cursor: pointer;
    }

    .lay_page {
      background: #fff;
      margin: 0 auto;
    }
  </style>
</head>
<?= $this->include('chat/header', ['title' => '查找好友']) ?>
<body>
<div class="layui-form">
  <div class="layui-container" style="padding:0">
    <div class="layui-row layui-col-space3">
      <div class="layui-col-xs7" style="margin-top: 15px;">
        <input type="text" name="keyword" lay-verify="required" autocomplete="off" placeholder="请输入用户编号/昵称/邮箱"
               class="layui-input">
      </div>
      <div class="layui-col-xs1" style="margin-top: 15px;">
        <button class="layui-btn layui-icon" lay-submit lay-filter="search">&#xe615;</button>
      </div>
    </div>
    <div id="LAY_view"></div>
    <textarea title="消息模版" id="LAY_tpl" style="display:none;">
			<fieldset class="layui-elem-field layui-field-title">
			  <legend>{{ d.legend}}</legend>
			</fieldset>
			<div class="layui-row ">
					{{#  layui.each(d.data, function(index, item){ }}
					<div class="layui-col-xs3 layui-find-list">
						<li layim-event="add" data-index="0" data-uid="{{ item.userId }}"
                data-name="{{item.username}}">
							<img src="{{item.avatar}}">
							<span>{{item.username}}({{item.email}})</span>
							<p>{{item.sign}}  {{#  if(item.sign == ''){ }}我很懒，懒得写签名{{#  } }} </p>
							<button class="layui-btn layui-btn-mini add"><i
                  class="layui-icon">&#xe654;</i>加好友</button>
						</li>
					</div>
					{{#  }); }}
			</div>
        </textarea>
    <div class="lay_page" id="LAY_page"></div>

  </div>

</div>
<script type="module">
  import {friend_get_recommended, friend_search, friend_apply} from '/chat/js/api.js';
  import {getRequest, postRequest} from '/chat/js/request.js';

  layui.use(['layim', 'laypage', 'form', 'flow'], function () {
    var layim = layui.layim
      , layer = layui.layer
      , laytpl = layui.laytpl
      , form = layui.form
      , $ = layui.jquery
      , laypage = layui.laypage;

    $(function () {
      getRecommend();
    });


    function getRecommend() {
      getRequest(friend_get_recommended, {}, function (data) {
        var html = laytpl(LAY_tpl.value).render({
          data: data,
          legend: '推荐好友',
          type: 'friend'
        });
        $('#LAY_view').html(html);
      });
    }

    form.on('submit(search)', function (data) {
      $("#LAY_page").css("display", "block");
      var keyword = data.field.keyword;

      postRequest(friend_search, {keyword: keyword, page: 1, size: 20}, function (data) {
        laypage.render({
          elem: 'LAY_page'
          , count: data.count
          , limit: data.perPage
          , prev: '<i class="layui-icon">&#58970;</i>'
          , next: '<i class="layui-icon">&#xe65b;</i>'
          , layout: ['prev', 'next', 'count']
          , curr: 1
          , jump: function (obj, first) {
            if (first) return false;
            let page = obj.curr;
            postRequest(friend_search, {keyword: keyword, page: page, size: 20}, function (data) {
              var html = laytpl(LAY_tpl.value).render({
                data: data.list,
                legend: '<a class="back"><i class="layui-icon">&#xe65c;</i>返回</a> 查找结果',
              });
              $('#LAY_view').html(html);
            });
          }
        });

      })
    });

    $('body').on('click', '.add', function () {//添加好友
      var li = $(this).parents('li');
      var username = li.attr('data-name');
      var receiver_id = li.attr('data-uid');
      var avatar = li.find("img").attr('src');
      parent.layui.layim.add({
        type: 'friend'
        , username: username
        , avatar: avatar
        , submit: function (group, remark, index) {
          postRequest(friend_apply, {
              receiver_id: receiver_id,
              group_id: group,
              application_type: 'friend',
              application_reason: remark
            }, function (data) {
              parent.layer.close(index);
            },
            function (data, msg) {
              parent.layer.close(index);
            }
          );
        }
      });
    });
    $('body').on('click', '.back', function () {
      getRecommend();
      $("#LAY_page").css("display", "none");
    });

    $("body").keydown(function (event) {
      if (event.keyCode == 13) {
        $(".find").click();
      }
    });
  })
  ;
</script>
</body>
</html>
