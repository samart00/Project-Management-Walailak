function getAllCheck(){
	var permissionData = [];
	var row = "";
	$("table[id=addChildRole] tr").each(function(index) {
		if (index !== 0) {
			row = $(this);
			var firstRow = row.find("td:first");
			var isCheck = firstRow.children().is(':checked');
			var id = firstRow.children().data('id');
			if(isCheck){
				var temp = {
					itemId: id
				};
				permissionData.push(temp);
			}
		}
	});
	return permissionData;
}

$("#addChildRole tbody").delegate("tr", "click", function() {
	$("#delete").prop("disabled", false);
	var checkBox = $("td:first", this).children();
	$(this).toggleClass('odd info');
	var className = $(this).attr('class');
	if(className == 'odd info'){
		checkBox.prop('checked', true);
	}else{
		checkBox.prop('checked', false);
	}
});
			
$('input[name=checkAll]').change(function(){
	$("#delete").prop("disabled", false);
	if($(this).prop('checked')){
		$.each($('.checkbox-col'), function(index, obj){
			var id = "table[id=addChildRole] tr:eq("+(index+1)+")"
			$(id).addClass('odd info');
			$(obj).prop('checked', true);
		});
	}else{
		$.each($('.checkbox-col'), function(index, obj){
			var id = "table[id=addChildRole] tr:eq("+(index+1)+")"
			$(id).removeClass('odd info');
			$(obj).prop('checked', false);
		});
	}
});