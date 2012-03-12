<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{$sitetitle}</title>
<meta http-equiv="Content-Style-Type" content="text/css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<link rel="stylesheet" href="css/jquery-ui-1.7.2.custom.css" type="text/css" />
<script type="text/javascript" src="js/mdabasic.js"></script>
<script type="text/javascript">
{literal}
	var PRG = {
		simpleReservation:function(id){
			$.get('api.php', { method: 'simpleReservation', program_id: id } ,function(data){
				if(data.match(/^error/i)){
					alert(data);
				}else{
					$('#resid_' + id).addClass('prg_rec');
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
				}
				else {
					var id = parseInt(data);
					if( id ) {
						$('#resid_' + id).addClass('prg_rec');
					}
					$('#floatBox4Dialog').dialog('close');
				}
			});
		}
	}
	
	$(document).ready(function () {
		var DG = $('#floatBox4Dialog');
		DG.dialog({title:'録画予約',width:600});
		DG.dialog('close');
	});
</script>
<style type="text/css">
<!--
body {padding:4px;margin:0;font-size:10pt;}
a {text-decoration:none;}

table#reservation_table {
    width: 800px;
    border: 1px #BBB solid;
    border-collapse: collapse;
    border-spacing: 0;
}

table#reservation_table th {
    padding: 5px;
    border: #E3E3E3 solid;
    border-width: 0 0 1px 1px;
    background: #BBB;
    font-weight: bold;
    line-height: 120%;
    text-align: center;
}
table#reservation_table td {
    padding: 5px;
    border: 1px #BBB solid;
    border-width: 0 0 1px 1px;
    text-align: center;
}

table#reservation_table tr.ctg_news, #category_select a.ctg_news {background-color: #FFFFD8;}
table#reservation_table tr.ctg_etc, #category_select a.ctg_etc {background-color: #FFFFFF;}
table#reservation_table tr.ctg_information, #category_select a.ctg_information {background-color: #F2D8FF;}
table#reservation_table tr.ctg_sports, #category_select a.ctg_sports {background-color: #D8FFFF;}
table#reservation_table tr.ctg_cinema, #category_select a.ctg_cinema {background-color: #FFD8D8;}
table#reservation_table tr.ctg_music, #category_select a.ctg_music {background-color: #D8D8FF;}
table#reservation_table tr.ctg_drama, #category_select a.ctg_drama {background-color: #D8FFD8;}
table#reservation_table tr.ctg_anime, #category_select a.ctg_anime {background-color: #FFE4C8;}
table#reservation_table tr.ctg_variety, #category_select a.ctg_variety {background-color: #FFD2EB;}
table#reservation_table tr.ctg_10, #category_select a.ctg_10 {background-color: #E4F4F4;}
table#reservation_table tr.prg_rec  {background-color: #F55;color:#FEE}

#floatBox4Dialog .prg_title{font-size:120%;font-weight:bold;padding:0.4em 0;text-align:center;}
#floatBox4Dialog .prg_rec_cfg{background:#EEE;padding:1em 2em;margin:0.4em 0;}
#floatBox4Dialog .labelLeft {width:8em;float:left;text-align:right;}
#floatBox4Dialog .button {padding:0.4em 1em;}
-->
</style>
{/literal}
</head>
<body>
<h2>{$sitetitle}</h2>
<div>
<a href="<?php echo url('/') ?>">番組表に戻る</a>/<a href="<?php echo url('keyword/index') ?>">自動録画キーワード管理へ</a>
</div>

<div>
絞り込み：
<form method="post" action="search.php">
<input type="hidden" name="do_search" value="1" />
検索語句<input type="text" size="20" name="search" value="{$search}" />
正規表現使用<input type="checkbox" name="use_regexp" value="1" {if $use_regexp}checked{/if} />
種別<select name="type">
  {foreach from=$types item=type}
  <option value="{$type.value}" {$type.selected}>{$type.name}</option>
  {/foreach}
</select>
局<select name="station">
  {foreach from=$stations item=st}
    <option value="{$st.channel_disc}" {$st.selected}>{$st.name}</option>
  {/foreach}
  </select>
カテゴリ<select name="category_disc">
  {foreach from=$cats item=cat}
  <option value="{$cat.category_disc}" {$cat.selected}>{$cat.name}</option>
  {/foreach}
  </select>
曜日<select name='weekofday'>
  {foreach from=$weekofdays item=day}
  <option value="{$day.id}" {$day.selected}>{$day.name}</option>
  {/foreach}
</select>
<input type="submit" value="絞り込む" />
</form>
</div>

{if count($programs)}
<table id="reservation_table">
 <tr>
  <th>種別</th>
  <th>局名</th>
  <th>番組開始</th>
  <th>番組終了</th>
  <th>タイトル</th>
  <th>内容</th>
  <th>簡易録画</th>
  <th>詳細録画</th>
 </tr>

{foreach from=$programs item=program}
 <tr id="resid_{$program.id}" class="ctg_{$program.name_en}{if $program.rec > 0} prg_rec{/if}">
  <td>{$program.type}</td>
  <td>{$program.station_name}</td>
  <td>{$program.starttime}</td>
  <td>{$program.endtime}</td>
  <td>{$program.title|escape}</td>
  <td>{$program.description|escape}</td>
  <td><input type="button" value="録画" onClick="javascript:PRG.simpleReservation('{$program.program_disc}')" /></td>
  <td><input type="button" value="詳細" onClick="javascript:PRG.customform('{$program.program_disc}')" /></td>
 </tr>
{/foreach}
</table>
{else}
  該当する番組はありません
{/if}
<div>{$programs|@count}件ヒット</div>
{if count($programs) >= 300}<div>表示最大300件まで</div>{/if}
{if $do_keyword}
{if (count($programs) < 300)}
<div>
<form method="post" action="keywordTable.php">
  <b>語句:</b>{$search|escape}
  <b>正規表現:</b>{if $use_regexp}使う{else}使わない{/if}
  <b>種別:</b>{if $k_type == "*"}すべて{else}{$k_type}{/if}
  <b>局:</b>{if $k_station == 0}すべて{else}{$k_station_name}{/if}
  <b>カテゴリ:</b>{if $k_category == 0}すべて{else}{$k_category_name}{/if}
  <b>曜日:</b>{if $weekofday == 7}なし{else}{$k_weekofday}{/if}曜
  <b>件数:</b>{$programs|@count}
  <input type="hidden" name="add_keyword" value="{$do_keyword}" />
  <input type="hidden" name="k_use_regexp" value="{$use_regexp}" />
  <input type="hidden" name="k_search" value="{$search}" />
  <input type="hidden" name="k_type" value="{$k_type}" />
  <input type="hidden" name="k_category" value="{$k_category}" />
  <input type="hidden" name="k_station" value="{$k_station}" />
  <input type="hidden" name="k_weekofday" value={$weekofday} />
  <b>録画モード:</b><select name="autorec_mode" >
  {foreach from=$autorec_modes item=mode name=recmode }
     <option value="{$smarty.foreach.recmode.index}" {$mode.selected} >{$mode.name}</option>
  {/foreach}
   </select>
  <br><input type="submit" value="この絞り込みを自動録画キーワードに登録" />
  </form>
</div>
{/if}
{/if}

<div id="floatBox4Dialog">jQuery UI Dialog</div>

</body>
</html>
