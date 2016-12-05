<!DOCTYPE html>
<html>
  <head>
    <title>预约</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <base href="<?php echo base_url().'jquery-weui/';?>"/>

    <link rel="stylesheet" href="lib/weui.css">
    <link rel="stylesheet" href="css/jquery-weui.css">
    <link rel="stylesheet" href="demos/css/demos.css">
  </head>
  <body ontouchstart>
    <header class='demos-header'>
      <h1 class='demos-title'>约吗？</h1>
    </header>

    <div class='weui_cells_weui_cells_form'>
      <div class='weui_cell'>
        <div class='weui_cell_hd'><label for="time" class='weui_label'>时间</label></div>
        <div>
          <input class='weui_input' id='time' type="text" value="">
        </div>
      </div>
    </div>
    <div class='demos-content-padded'>
      <a href="javascript:;" id='confirm' class='weui_btn weui_btn_primary'>确定</a>
    </div>

    <script src="lib/jquery-2.1.4.js"></script>
    <script src="js/jquery-weui.js"></script>

    <script>
      $("#time").datetimePicker();
	  $(document).on("click", "#confirm", function() {
        $.confirm("您确定要把自己预约出去吗？不考虑你老公啦？", "警告！", function() {
          $.toast("预约已发送");
		}, function() {
          // cancel
		});
	  });
    </script>
  </body>
</html>
