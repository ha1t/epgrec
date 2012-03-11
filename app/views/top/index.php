<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $sitetitle ?></title>
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" href="<?php echo url('/') ?>css/bootstrap.css" /> 
<link rel="stylesheet" href="<?php echo url('/') ?>css/jquery-ui-1.7.2.custom.css" />
<script type="text/javascript" src="<?php echo url('/') ?>js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="<?php echo url('/') ?>js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="<?php echo url('/') ?>js/mdabasic.js"></script>
<script type="text/javascript">

function tvtimes_scroll() {
    var t2max = $('#tvtimes2').position().left;
    var ftmin = $('#float_titles').position().top;
    scroll_tvtimes2();
    $(window).scroll(function () {
        $('#tvtimes').css('left', parseInt($(document).scrollLeft())); 
        var newTop = parseInt($(document).scrollTop());
        if (newTop < ftmin) {
            newTop = ftmin;
        }
        $('#float_titles').css('top', newTop);
        scroll_tvtimes2();
        $('#float_follows').css('left', parseInt($(document).scrollLeft()));
    });
    $(window).resize(function () {
            scroll_tvtimes2();
    });
    function scroll_tvtimes2(){
        var inwidth = parseInt($('body').innerWidth());
        var newLeft = inwidth - parseInt($('#tvtimes2').width()) + parseInt($( document ).scrollLeft());
        if (newLeft > t2max) {
            newLeft = t2max
        }
        $('#tvtimes2').css('left', newLeft);
        $('#float_follows').width(inwidth);
    }
}

// hover時のプログラム表示
function prg_hover() {
    function aClick(){
        var TG = $(this).children('.prg_dummy');
        var startTime = new Date(TG.children('.prg_start').html());
        var duration = parseInt(TG.children('.prg_duration').html());
        var endTime = new Date(startTime.getTime() + duration * 1000);
        var prgID = TG.children('.prg_id').html();

        var str = '<div class="prg_title">';
        str += TG.children('.prg_title').html();
        str += '</div>' + 
            '<div class="prg_rec_cfg ui-corner-all"><div class="prg_channel"><span class=" labelLeft">チャンネル：</span><strong>' + TG.children('.prg_channel').html() + '</strong></div>' +
            '<div class="prg_startTime" style="clear: left"><span class=" labelLeft">日時：</span>' + MDA.Days.time4Disp(startTime) + ' ～ ' + MDA.Days.time4DispH(endTime) + '</div>' +
            '<div class="prg_duration" style="clear: left"><span class=" labelLeft">録画時間：</span><strong>' + parseInt(duration / 60) +'</strong>分' + ((duration % 60)>0?'<strong>' + parseInt(duration % 60) + '</strong>秒':'') + '</div>' +
            '</div>';
        if ($(this).hasClass('prg_rec')) {
            str += '<div style="margin:2em 0 1em 0;text-align:center;"><a href="javascript:PRG.cancel(' + prgID + ')" class="ui-state-default ui-corner-all ui-dialog-buttonpane button">予約キャンセル</a></div>';
        } else {
            str += '<div style="margin:2em 0 1em 0;text-align:center;"><a href="javascript:PRG.rec(\'' + prgID + '\')" class="ui-state-default ui-corner-all ui-dialog-buttonpane button">簡易予約</a>　<a href="javascript:PRG.customform(' + prgID + ')" class="ui-state-default ui-corner-all ui-dialog-buttonpane button">予約カスタマイズ</a></div>';
        }
        $('#floatBox4Dialog').html(str);
        $('#floatBox4Dialog').dialog('open', 'center');
    };

    $('.prg').hover(
        function() {
            $('#tv_chs .prg_hover').removeClass('prg_hover');
            if ($(this).hasClass('prg_none')) {
                return;
            }
            $(this).addClass('prg_hover');
            var TG = $(this).children('.prg_dummy');
            var startTime = new Date(TG.children('.prg_start').html());
            var duration = parseInt(TG.children('.prg_duration').html());
            var endTime = new Date(startTime.getTime() + duration * 1000);
            var disp_date = MDA.Days.time4Disp(startTime) + '～' + MDA.Days.time4DispH(endTime);
            $('#prg_info_title').html(TG.children('.prg_title').html());
            $('#prg_info_desc').html(TG.children('.prg_desc').html());
            $('#prg_info_channel').html(TG.children('.prg_channel').html());
            $('#prg_info_date').html(disp_date);
            $('#prg_info').show();
            $(this).click(aClick);
        },
        function() {
            $(this).removeClass('prg_hover');
            $('#prg_info').hide();
            $(this).unbind('click',aClick);
        }
    );
}

	var PRG = {
		chdialog:function(disc){
			$('#channelDialog').dialog('close');
			$.get('api.php', { method: 'channelInfo', channel_disc: disc },function(data) {
				if (data.match(/^error/i)){
					alert(data);
				} else {
					var str = data;
					str += '<div style="margin:2em 0 1em 0;text-align:center;"><a href="javascript:PRG.chupdate()" class="ui-state-default ui-corner-all ui-dialog-buttonpane button">更新</a></div>';
					$('#channelDialog').html(str);
					$('#channelDialog').dialog('open', 'center');
					
				}
			});
		},
		chupdate:function() {
			var v_sid = $('#id_sid').val();
			var v_channel_disc = $('#id_disc').val();
			$.post('channelSetSID.php', { channel_disc: v_channel_disc,
						      sid: v_sid }, function(data) {
				
				$('#channelDialog').dialog('close');
			});
		},
		rec:function(id){
			$.get('api.php', { method: 'simpleReservation', program_id: id } ,function(data){
				if(data.match(/^error/i)){
					alert(data);
					$('#floatBox4Dialog').dialog('close');
				}else{
					$('#prgID_' + id).addClass('prg_rec');
					$('#floatBox4Dialog').dialog('close');
				}
			});
		},
		cancel:function(id){
			$.get(INISet.prgCancelURL, { program_id: id } ,function(data){
				if(data.match(/^error/i)){
					alert(data);
					$('#floatBox4Dialog').dialog('close');
				}else{
					$('#prgID_' + id).removeClass('prg_rec');
					$('#floatBox4Dialog').dialog('close');
				}
			});
		},
		customform:function(id) {
			$('#floatBox4Dialog').dialog('close');
			$.get('api.php', { method: 'reservationForm', program_id: id }, function(data) {
				if(data.match(/^error/i)){
					alert(data);
				}
				else {
					var str = data;
					str += '<div style="margin:2em 0 1em 0;text-align:center;"><a href="javascript:PRG.customrec()" class="ui-state-default ui-corner-all ui-dialog-buttonpane button">予約する</a></div>';
					$('#floatBox4Dialog').html(str);
					$('#floatBox4Dialog').dialog('open', 'center');
				}
			});
		},
		customrec:function() {
			var id_syear = $('#id_syear').val();
			var id_smonth = $('#id_smonth').val();
			var id_sday = $('#id_sday').val();
			var id_shour = $('#id_shour').val();
			var id_smin = $('#id_smin').val();
			var id_eyear = $('#id_eyear').val();
			var id_emonth = $('#id_emonth').val();
			var id_eday = $('#id_eday').val();
			var id_ehour = $('#id_ehour').val();
			var id_emin = $('#id_emin').val();
			var id_channel_id = $('#id_channel_id').val();
			var id_record_mode = $('#id_record_mode').val();
			var id_title = $('#id_title').val();
			var id_description = $('#id_description').val();
			var id_category_id = $('#id_category_id ').val();
			var id_program_id = $('#id_program_id').val();
			var with_program_id = $('#id_program_id').attr('checked');
			
			if( ! with_program_id ) id_program_id = 0;
			
			$.post('customReservation.php', { syear: id_syear,
							  smonth: id_smonth,
							  sday: id_sday,
							  shour: id_shour,
							  smin: id_smin,
							  eyear: id_eyear,
							  emonth: id_emonth,
							  eday: id_eday,
							  ehour: id_ehour,
							  emin: id_emin,
							  channel_id: id_channel_id,
							  record_mode: id_record_mode,
							  title: id_title,
							  description: id_description,
							  category_id: id_category_id,
							  program_id: id_program_id }, function(data) {
				if(data.match(/^error/i)){
					$('#floatBox4Dialog').dialog('close');
					alert(data);
				} else {
					var id = parseInt(data);
					if (id) {
						$('#prgID_' + id).addClass('prg_rec');
					}
					$('#floatBox4Dialog').dialog('close');
				}
			});
		}
	}
	var CTG = {
		CN:'ctg',
		CV:'0.1',
		defaultCk:[],
		INI:function(){
			var Ck = this.CkGet()[1];
			if(Ck){ $.each(Ck.split(','), function(){CTG.select(this);})}
		},
		select:function(ctg){
			if($('#category_select .ctg-hide.ctg_'+ctg).length){
				$('#tv_chs .ctg_'+ctg).removeClass('ctg-hide');
				$('#category_select a.ctg_'+ctg).removeClass('ctg-hide');
			} else {
				$('#tv_chs .ctg_'+ctg).addClass('ctg-hide');
				$('#category_select a.ctg_'+ctg).addClass('ctg-hide');
			}
			this.oCk();
		},
		toggle:function (){$('#category_select ul').toggle();},
		oCk:function(){
			var T=$('#category_select ul li a.ctg-hide');
			var X=[];
			$.each(T.get(), function(){
				$(this).attr('class').match(/ctg_([^ ]+)/);
				var TMC=RegExp.$1;
				X.push(TMC);
			});
			this.CkSet([X.join(',')]);
		},
		CkGet:function (){
			var Ck = MDA.Cookie.get(this.CN);
			if(!Ck){return this.defaultCk};
			 Ck=Ck.replace(/^([^;]+;)/,'');
			return Ck.split('+');
		},
		CkSet:function(V){
			MDA.Cookie.set(this.CN,'ver='+this.CV+'+'+V.join('+'));
		}
	};
	var nowBar = {
		defaultID:'tableNowBas',
		startTime:null,
		endTime:null,
		INI:function(){
			if (INISet.tableStartTime && INISet.tableStartTime && INISet.dotMin) {
				$('#tvtable').append('<div id="' + this.defaultID + '" style="display:none">now</div>');
				this.startTime = new Date(INISet.tableStartTime);
				this.endTime = new Date(INISet.tableEndTime);
				$('#' + this.defaultID).width($('#float_titles').width());
				this.ch();
			}
		},
		ch:function(){
			var now = new Date();
			if(this.startTime){
				if((now >= this.startTime) && (this.endTime >= now)){
//					console.log((now - this.startTime) / 60000);
					$('#' + this.defaultID).css({top:(now - this.startTime) / 60000 * INISet.dotMin}).show()
				} else {
					$('#' + this.defaultID).hide()
				}
			}
		}
	}

	MDA.SCR = {
		CN:'scr',
		CV:'0.1',
		defaultCk:{md:'',x:0,y:0},
		jqSel:[{sel:'#jump-time a.jump',md:'x'},{sel:'#jump-day a.jump',md:'xy'},{sel:'#jump-day a.jump-today',md:'x'},{sel:'#jump-broadcast a.jump',md:'y'}],
		INI:function(){
//			this.defaultCk.y = $('#float_titles').position().top;
			$.each(this.jqSel, function(){
				var md = this.md;
				$(this.sel).click(function(){MDA.SCR.oCk(md)})
			});
			var Ck = this.CkGet();
//			console.log(Ck);
			var x = (Ck.md.indexOf('x')>-1)?Ck.x:this.defaultCk.x;
			var y = (Ck.md.indexOf('y')>-1)?Ck.y:this.defaultCk.y;
			if (Ck.md) {
				window.scrollBy(x, y);
			}
			this.CkClear();
		},
		channel:{
			save:function(){},
			load:function(){}
		},
		time: {
		},
		oCk:function(xy){
			this.CkSet(['md=' + ((!xy)?'xy':xy),
				'x=' + $(document ).scrollLeft(),
				'y=' + $(document ).scrollTop()]);
		},
		CkGet:function (){
			var Ck = MDA.Cookie.get(this.CN);
			if(!Ck){return this.defaultCk};
			Ck=Ck.replace(/^([^;]+;)/,'').split('+');
			var ret = {};
			$.each(Ck, function(){
				var str = this.split('=', 2);
				ret[str[0]] = str[1];
			})
			return ret;
		},
		CkSet:function(V){
			MDA.Cookie.set(this.CN,'ver='+this.CV+'+'+V.join('+'));
		},
		CkClear:function(){
			MDA.Cookie.del(this.CN);
		}
	};
	
	$(document).ready(function () {
		MDA.Cookie.CookieName = 'tvProgmas_';
		CTG.toggle();
		tvtimes_scroll();
		prg_hover();

		var DG = $('#floatBox4Dialog');
		DG.dialog({title:'録画予約',width:600});
		DG.dialog('close');

		var DG2 = $('#channelDialog');
		DG2.dialog({title:'チャンネル情報',width:600});
		DG2.dialog('close');

		nowBar.INI();
		CTG.INI();
		MDA.SCR.INI();	// 番組表の位置保存
	});
</script>
<style type="text/css">
<!--
body {
    padding-top: 40px;
}

a {
    text-decoration:none;
}

h2 {
    padding: 4px
}

#float_titles {
    position:absolute;
    background-image: url('<?php echo url('/') ?>imgs/trancBG50.png');
    z-index: 100;
    width: <?php echo (int)$chs_width + 80 ?>;
    height:120px;
}

#float_titles div.set.ctg_sel {background-color:#BBB;color:#3CF}
#float_titles div.set {float:left;background-color:#444;padding:4px;margin:4px;}
#float_titles span.title {float:left;color:#ACF;}
#float_titles ul {float:left;padding:0;margin:0;}
#float_titles ul li {float:left;list-style:none;margin:0 0 0 4px;}
#float_titles li a{padding:1px 4px;background-color:#555;color:#FFF;}
#float_titles li.selected a{background-color:#48B;}
#float_titles li a:hover{background-color:#28D;}

#tvtable {
    line-height:1.2em;
    width:100%;
    position:relative;
}

#tvtimes,#tvtimes2 {
    position:absolute;
    background-image: url('<?php echo url('/') ?>imgs/trancBG70.png');
}
#tvtimes {
    width: 40px;
    top: 0px;
    left: <?php echo (int)$chs_width + 40 ?>px;
}
#tv_chs {padding-left:40px;padding-right:40px;}
.tvtime {
	height:{$height_per_hour}px;
	color:#EEE;
	text-align:center;
	font-weight:bold;
	font-size:120%;
	background-image: url(<?php echo url('/') ?>imgs/dot2.gif);
	background-repeat: repeat-x;
	background-position: left bottom;
}
#tvtable div.tvtimetop {padding:8px 0px;}

#tvtable div.ch_set {
    width:<?php echo $ch_set_width ?>px;
    float:left;
    background-color:#BBB;
}

#tvtable div.prg {
    margin-right:2px;
}
#tvtable div.ch_title {
    padding:8px 0px;
    background-color:#333;
    color:#DDD;
    font-weight:bold;
    text-align:center;
    margin-right:2px;
}
#tvtable div.prg {
	overflow:hidden;
	color:#444;
	background-image: url(<?php echo url('/') ?>imgs/dot2.gif);
	background-image: url(<?php echo url('/') ?>imgs/prg_bg2.png);
	background-repeat: repeat-x;
	background-position: left bottom;
	-moz-border-radius: 0.6em 0.6em 0.3em 0.3em;
	-webkit-border-radius: 0.6em;
	-webkit-border-bottom-right-radius: 0.3em;
	-webkit-border-bottom-left-radius: 0.3em;
}
#tvtable div.prg_none {background-color:#eee;}
#tvtable div.prg_dummy {margin:3px 6px;}
#tvtable div.prg_title {color:#111;font-weight:bold;}
#tvtable div.prg_subtitle {font-size:80%;}
#tvtable div.prg_desc {font-size:80%;}

#tvtable div.prg_start,#tvtable div.prg_duration,#tvtable div.prg_channel ,#tvtable div.prg_id  {display: none;}

#tvtable div.ctg_news, #category_select a.ctg_news {background-color: #FFFFD8;}
#tvtable div.ctg_etc, #category_select a.ctg_etc {background-color: #FFFFFF;}
#tvtable div.ctg_information, #category_select a.ctg_information {background-color: #F2D8FF;}
#tvtable div.ctg_sports, #category_select a.ctg_sports {background-color: #D8FFFF;}
#tvtable div.ctg_cinema, #category_select a.ctg_cinema {background-color: #FFD8D8;}
#tvtable div.ctg_music, #category_select a.ctg_music {background-color: #D8D8FF;}
#tvtable div.ctg_drama, #category_select a.ctg_drama {background-color: #D8FFD8;}
#tvtable div.ctg_anime, #category_select a.ctg_anime {background-color: #FFE4C8;}
#tvtable div.ctg_variety, #category_select a.ctg_variety {background-color: #FFD2EB;}
#tvtable div.ctg_10, #category_select a.ctg_10 {background-color: #E4F4F4;}
#tvtable div.ctg-hide, #category_select a.ctg-hide {background-color: #F8F8F8;color:#888;}
#tvtable div.ctg-hide .prg_title, #category_select a.ctg-hide .prg_title{color:#666;}
#tvtable div.prg_rec  {background-color: #F55;color:#FEE}
#tvtable div.prg_rec .prg_title,#tvtable div.prg_hover .prg_title {color:white;}
#tvtable div.prg_hover  {background-color: #28D;color:#EFF}

#float_titles div.ch_title {
    width:<?php echo $ch_set_width ?>px;
    float:left;
    color:#FFF;
    font-weight:bold;
    text-align:center
}
#float_titles div.ch_title div {
    cursor: pointer;
    padding:8px 0px;
    margin:0 6px 0 4px;
    background-image: url('<?php echo url("/") ?>imgs/trancBG50.png');
}

#float_follows {position:absolute;}
#prg_info {
	display:none;
	position:absolute;
    top:0px;
    left:0px;
	width:100%;
	background-color:#246;
	color:#BDF;
	height:80px;
}

#tableNowBas {position:absolute;background:red;width:100%;top:190px;height:2px;overflow:hidden;}

#floatBox4Dialog .prg_title{font-size:120%;font-weight:bold;padding:0.4em 0;text-align:center;}
#floatBox4Dialog .prg_rec_cfg{background:#EEE;padding:1em 2em;margin:0.4em 0;}
#floatBox4Dialog .labelLeft {width:8em;float:left;text-align:right;}
#floatBox4Dialog .button {padding:0.4em 1em;}

#channelDialog .prg_title{font-size:120%;font-weight:bold;padding:0.4em 0;text-align:center;}
#channelDialog .prg_rec_cfg{background:#EEE;padding:1em 2em;margin:0.4em 0;}
#channelDialog .labelLeft {width:8em;float:left;text-align:right;}
#channelDialog .button {padding:0.4em 1em;}
-->
</style>
</head>
<body>
<div class="topbar">
  <div class="fill">
    <div class="container">
      <a class="brand" href="#"><?php echo $sitetitle ?></a>
      <ul class="nav">
        <li><a href="index.php">番組表</a></li>
        <li><a href="search.php">番組検索</a></li>
        <li><a href="<?php echo url('reserve/index') ?>">予約一覧</a></li>
      </ul>
    </div>
  </div>
</div>

<div id="float_titles">
  <div id="float_follows">
    <div class="set">
      <ul>
        <li><a href="envSetting.php">環境設定</a></li>
      </ul>
    </div>

<div class="set ctg_sel" id="category_select">
  <span class="title"><a href="javascript:CTG.toggle()">強調表示</a></span>
  <ul>
    <?php foreach ($categories as $category): ?> 
    <li><a href="javascript:CTG.select('<?php echo $category->name_en ?>');" class="ctg_<?php echo $category->name_en ?>"><?php echo $category->name_jp ?></a></li>
    <?php endforeach; ?>
  </ul>
</div>

<div id="time_selects">
  <div class="set" id="jump-broadcast">
    <span class="title">放送波選択</span>
    <ul>
      <?php foreach ($types as $type): ?> 
      <li <?php echo $type['selected'] ?>><a class="jump" href="<?php echo $type['link'] ?>"><?php echo $type['name'] ?></a></li>
      <?php endforeach; ?>
    </ul>
    <br style="clear:left;" />
  </div>

  <div class="set" id="jump-time">
    <span class="title">時間</span>
    <ul>
      <?php foreach ($toptimes as $top): ?> 
      <li><a class="jump" href="<?php echo $top['link']?>"><?php echo $top['hour'] ?>～</a></li>
      <?php endforeach; ?>
    </ul>
    <br style="clear:left;" />
  </div>

  <br style="clear:left;" />

  <div class="set">
    <ul>
      <li><a href="recordedTable.php">録画済一覧</a></li>
    </ul>
  </div>

  <div class="set" id="jump-day">
    <span class="title">日付</span>
    <ul>
      <?php foreach($days as $day): ?>
      <li <?php echo $day['selected'] ?>>
      <?php if ($day['d'] == '現在'): ?> 
        <a class="jump-today" href="<?php echo $day['link'] ?>"><?php echo $day['d'] . $day['ofweek'] ?></a>
      <?php else: ?> 
        <a class="jump" href="<?php echo $day['link'] ?>"><?php echo $day['d'] . $day['ofweek'] ?></a>
      <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <br style="clear:left;" />
  </div>

</div>
<br style="clear:left;" />

<!-- prg説明文表示  -->
<div id="prg_info">
  <h3 id="prg_info_title" style="color: white;"></h3>
  <span id="prg_info_channel" class="prg_sub"></span>
  <span id="prg_info_date" class="prg_sub"></span>
  <span id="prg_info_desc"></span>
</div>

<!-- ch一覧  -->
<div style="position:absolute;bottom:0;">

  <!-- left-margin -->
  <div style="width: 40px; float:left;">&nbsp;</div>

  <?php foreach ($programs as $program): ?> 
  <div class="ch_title">
    <div onClick="javascript:PRG.chdialog('<?php echo $program['channel_disc'] ?>')" ><?php echo $program['station_name'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<br style="clear:left;" />
</div>

<div id="float_titles_dummy" style="width:1410px;height:120px;">&nbsp;</div>

<div id="tvtable">
  <div id="tvtimes">
  <?php foreach ($tv_hours as $time): ?> 
  <div class="tvtime"><?php echo $time ?></div>
  <?php endforeach; ?>
  </div>

  <div id="tv_chs" style="width: <?php echo $chs_width ?>px" >
    <?php foreach ($programs as $program): ?> 
    <div class="ch_set" style="width: <?php echo $ch_set_width ?>px" >
      <div class="ch_programs">
      <?php foreach ($program['list'] as $item): ?> 
        <?php $class_rec = '' ?>
        <?php if ($item['rec'] > 0): ?> 
          <?php $class_rec = 'prg_rec' ?>
        <?php endif; ?>
        <?php if ($item['program_disc']): ?> 
        <div id="prgID_<?php echo $item['program_disc'] ?>" class="prg ctg_<?php echo $item['category_name'] ?> <?php echo $class_rec ?>" style="height:<?php echo $item['height'] ?>px;">
        <?php else: ?>
        <div class="prg prg_none ctg_<?php echo $item['category_name'] ?> <?php echo $class_rec ?>" style="height:<?php echo $item['height'] ?>px;">
        <?php endif; ?>
        <div class="prg_dummy">
          <div class="prg_title"><?php echo $item['title'] ?></div>
          <div class="prg_subtitle"><?php echo $item['starttime'] ?></div>
          <div class="prg_desc"><?php echo $item['description'] ?></div>
          <div class="prg_channel"><?php echo $item['channel'] ?></div>
          <div class="prg_start"><?php echo $item['prg_start'] ?></div>
          <div class="prg_duration"><?php echo $item['duration'] ?></div>
          <div class="prg_id"><?php echo $item['program_disc'] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
   </div>
   <?php endforeach; ?>
 </div>

 <div id="tvtimes2">
   <?php foreach($tv_hours as $time): ?>
     <div class="tvtime"><?php echo $time ?></div>
   <?php endforeach; ?>
 </div>
</div>

<div id="floatBox4Dialog">
jQuery UI Dialog
</div>
<div id="channelDialog">jQuery UI Dialog</div>

<script type="text/javascript">
var INISet = {
    prgRecordPlusURL : 'recordp.php',		// 詳細予約
    prgCancelURL : 'cancelReservation.php',		// 予約キャンセル
    dotMin : <?php echo $height_per_min ?>,
    tableStartTime : '<?php echo $now_time ?>',
    tableEndTime : '<?php echo $last_time ?>'
}
</script>
</body>
</html>
