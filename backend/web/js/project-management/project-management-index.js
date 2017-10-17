function showModalDetailProject(data){
	var project = data.project;
	var userId = data.userId;
		$('.modal-title').text(project.projectName);
		$('#modalProjectId').val(project._id);
		$('#modal-description').text(project.description);
		$('#modal-start-date').text(project.startDate);
		$('#modal-end-date').text(project.endDate);
		$('#modal-project-type').text(project.category);
		$('#modal-create-date').text(project.createDate);
		$('#modal-create-by').text(project.createBy);
		$('#modal-department-name').text(project.departmentId);	
		$('#myModal').modal('show');
}
					
$('#pManage').DataTable( {
    responsive: true,
	searching: false,
	paging: false,
    lengthChange: false,
	ordering: false,
	info: false,
	language: {"emptyTable": "ไม่พบข้อมูล"},
} );