$(function(){

            $("#next").click(function(){
                Calculate();
            });

            function Calculate() {
                var from = $("#from").val();
                    fromtime = $("#fromTime").val();
                    to = $("#to").val();
                    totime = $("#toTime").val();

                
                if(from != "" && fromtime != "" && to != "" && totime != ""){
                    var fromdate = (from+" "+fromtime).trim();
                        todate = (to+" "+totime).trim();
                        dateString = fromdate,
                        dateTimeParts = dateString.split(' '),
                        timeParts = dateTimeParts[1].split(':'),
                        dateParts = dateTimeParts[0].split('/'),

                    from = new Date(dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1]);

                    var dateString = todate,
                        dateTimeParts = dateString.split(' '),
                        timeParts = dateTimeParts[1].split(':'),
                        dateParts = dateTimeParts[0].split('/'),

                    to = new Date(dateParts[2], parseInt(dateParts[1], 10) - 1, dateParts[0], timeParts[0], timeParts[1]);
                    //alert(to.getTime()-from.getTime());
                    if((to.getTime()-from.getTime()) < 0){
                        $("#next").hide();
                        $("#requireDate").show();
                        $("#requireDate").html("<p><font color=\"red\">ช่วงเวลาไม่ถูกต้อง</font></p>");
                    }else{
                    	var isShowErrorName = $('#error-name').text();
                    	if(isShowErrorName != ""){
                    		$('#next').hide();
                    	}else{
                    		$('#next').show();
                    	}
                        $("#requireDate").hide();
                    }
                }
            }

            $("#from,#to,#fromTime,#toTime").change(function(){
                Calculate();
            });

            $("#projectname,#description,#teamname").change(function(){
                var value=($(this).val()).trim();
                $(this).val(value);
            });

            $("#want").click(function(){
                $("#teamname").prop('disabled', false);
            });

            $("#nowant").click(function(){
                $("#teamname").prop('disabled', true);
                $("#teamrequire").hide();
                $("#duplicateTeamname").hide();
            });

            $("#teamname").click(function(){
                $("#teamrequire").hide();
            });

            $("#submit").click(function(){
                var isDisabled = $("#teamname").is(':disabled');
                var	teamname = $("input[name=newteamname]").val();
                if(teamname != "" && teamname != undefined){
                	teamname = teamname.trim();
                }
                
                if(!isDisabled){
                	if(teamname != ""){
                		submit();
                	}else{
                		$("#teamrequire").html("<font color=\"red\">กรุณากรอกชื่อทีม</font>");
                        $("#teamrequire").show();
                	}
                }else{
                	submit();
                }
            });
            
            $("li.user-menu").click(function(){
            	var strClass = $(this).attr('class');
            	if(strClass.includes("user-menu open")){
            		$(this).removeClass('dropdown user user-menu open').addClass('dropdown user user-menu');
            	}else{
            		$(this).removeClass('dropdown user user-menu').addClass('dropdown user user-menu open');
            	}
            });
        });