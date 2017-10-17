var table = "";

var TableDatatablesResponsive = function () {

    var initTable1 = function () {
     
        
        

        table = $('#member').DataTable({
            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ ของ _TOTAL_ รายการ",
                "infoEmpty": "ไม่พบข้อมูล",
                "infoFiltered": "(กรอง 1 จากทั้งหมด _MAX_ รายการ)",
                "lengthMenu": "_MENU_ รายการ",
                "zeroRecords": "ไม่พบข้อมูล",
                "oPaginate": {
                    "sFirst": "หน้าแรก", // This is the link to the first page
                    "sPrevious": "ก่อนหน้า", // This is the link to the previous page
                    "sNext": "ถัดไป", // This is the link to the next page
                    "sLast": "หน้าสุดท้าย" // This is the link to the last page
                },
                "select": {
                	"rows": "%d รายการถูกเลือก"
                }
            },

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            // setup buttons extentension: http://datatables.net/extensions/buttons/
            buttons: [
               
            ],

            // setup responsive extension: http://datatables.net/extensions/responsive/
            responsive: true,

            "order": [
                [1, 'desc']
            ],
            
            "lengthMenu": [
                [5, 10, 15, 20, -1],
                [5, 10, 15, 20, "ทั้งหมด"] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,

            "searching": true,

            "pagingType": "full_numbers",
            
            

            "columnDefs": [ {
                "targets": [0],
                "orderable": false,
                "checkboxes": {
                	"selectRow": true
                }
            },
        
            {"className": "dt-center", "targets": [0]},
            {"className": "dt-left", "targets": []},
            {"className": "dt-head-center", "targets": [1,2]},
            ],
            
            "select": {
                "style": "multi"
             },
            // "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js). 
            // So when dropdowns used the scrollable div should be removed. 
            //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
        });
        
        $('#name').keyup(function(){
        	var test = this.value;
        	table.column(1).search( this.value ).draw();
        });

        $('#department').change(function(){
        	var test = this.value;
        	table.column(2).search( this.value ).draw();
        })

    }

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            initTable1();
        }

    };

}();

jQuery(document).ready(function() {
    TableDatatablesResponsive.init();
});