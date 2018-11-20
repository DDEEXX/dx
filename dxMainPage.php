<?php
//require_once("class/class.php");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<?php header('Content-type: text/html; charset=windows-1251')?>
<title>DX HOME</title>
<script src="js2/jquery.js"></script>
<script src="js2/jquery.timers.js"></script>
<script src="js2/jquery-ui.js"></script>

<link  rel="stylesheet" type="text/css"  href="css2/temes/dx/jquery-ui.css" >

<link rel="stylesheet" type="text/css" href="css2/reset.css">
<!-- <link rel="stylesheet" type="text/css" href="/css2/text.css">  -->
<link rel="stylesheet" type="text/css" href="css2/960_12_col.css">
<link rel="stylesheet" type="text/css" href="css2/style_mmenu.css">
<!-- <link rel="stylesheet" type="text/css" href="/css2/style_960_b.css">  -->
<link rel="stylesheet" type="text/css" href="css2/style.css">
<link rel="stylesheet" type="text/css" href="css2/icon.css">

<script>
$(function() {
	
	$.ajaxSetup({ cache: false });
	
    $("#mmenu").menu();

    $("button.upDown").button({
        icons: {
          primary: "ui-icon-upDown"
        },
        text: false
    })    
    
    $("button").button()
      .click(function( event ) {
        event.preventDefault();
    });    
    
    //������������ ��������� ���� ����
    $('#mmenu li:first').addClass('ui-state-selected'); // ��� �������� �������� ����� ���� ����� ������ ������, �� ���� ��������� ��������
    $('#mmenu a').each(function () { // �������� �� ������ ��� ��������
      var location = window.location.href; // ���������� � ������� ��������
      var link = this.href; // ���������� � url ������
      if(location == link) {
        $('#mmenu li:first').removeClass('ui-state-selected'); // ������� ������� ����� � ��������� ��������
        $(this).parent().addClass('ui-state-selected'); // ��������� �����
      }    
    });    

    $('#mmenu').removeClass('ui-widget-content'); // ��� �������� �������� ����� ���� ����� ������ ������, �� ���� ��������� ��������
    
});
</script>

</head>

<body>
<div class="container_12">
  <div class="grid_1">
	<ul id="mmenu" class="ui-corner-all">
		<li class="ui-corner-all"><a href="dxMainPage.php?p=home"><span class="ui-icon ui-icon-mmhome" title="��������">1</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=temp"><span class="ui-icon ui-icon-mmtemp" title="�����������">2</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=light"><span class="ui-icon ui-icon-mmbulb" title="���������">3</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=power"><span class="ui-icon ui-icon-mmpower">4</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=n5"><span class="ui-icon ui-icon-mmheater">5</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=cam"><span class="ui-icon ui-icon-mmip_camera">6</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=n7"><span class="ui-icon ui-icon-mmkey">7</span></a></li>
		<li class="ui-corner-all"><a href="dxMainPage.php?p=n8"><span class="ui-icon ui-icon-mmpref">8</span></a></li>
		</ul>
  </div>
  
<?php
include_once 'dxPages.php';
?>
  
</div>
</body>
</html>
