<?php use common\libs\Permission;
$baseUrl = \Yii::getAlias('@web'); 
$user = Yii::$app->user;
?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="ค้นหา"/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                		
                    ($user->can(Permission::SEARCH_ROLE) || $user->can(Permission::SEARCH_ASSIGN) || $user->can(Permission::SEARCH_AMOUNT)
        			|| $user->can(Permission::SEARCH_CATEGORY) || $user->can(Permission::SEARCH_TEAM_MANAGEMENT) || $user->can(Permission::SEARCH_PROJECT_MANAGEMENT))
                	?['label' => 'เมนูสำหรับผู้ดูแลระบบ', 'options' => ['class' => 'header']]:array(),
                		
                	($user->can(Permission::SEARCH_ROLE))?['label' => 'การจัดการบทบาท', 'icon' => 'graduation-cap', 'url' => ['/role']]:array(),
                	($user->can(Permission::SEARCH_ASSIGN))?['label' => 'กำหนดบทบาท', 'icon' => 'user-secret', 'url' => ['/assign']]:array(),
                	($user->can(Permission::SEARCH_AMOUNT) || $user->can(Permission::SEARCH_EVENT_HOLIDAY) || $user->can(Permission::SEARCH_EVENTTYPE))?
                	[
                		'label' => 'การกำหนดนโยบาย',
                		'icon' => 'fa fa-tasks',
                		'url' => '#',
                			'items' => [
                				($user->can(Permission::SEARCH_AMOUNT))?array('label' => 'กำหนดจำนวนโครงการที่สร้างได้', 'icon' => '', 'url' => ['/policy']):array(),
                				($user->can(Permission::SEARCH_EVENT_HOLIDAY))?array('label' => 'กำหนดวันหยุด', 'icon' => '', 'url' => ['/upload']):array(),
                				($user->can(Permission::SEARCH_EVENTTYPE))?array('label' => 'กำหนดประเภทกิจกรรม', 'icon' => '', 'url' => ['/eventtype']):array(),
                			],
                	]:array(),
                	($user->can(Permission::SEARCH_CATEGORY))?['label' => 'การจัดการประเภทโครงการ', 'icon' => 'bookmark', 'url' => ['/category']]:array(),
                	($user->can(Permission::SEARCH_TEAM_MANAGEMENT))?['label' => 'การจัดการทีม', 'icon' => 'user-plus', 'url' => ['/team-management']]:array(),
                	($user->can(Permission::SEARCH_PROJECT_MANAGEMENT))?['label' => 'การจัดการโครงการ', 'icon' => 'table', 'url' => ['/project-management']]:array(),
                	
                	($user->can(Permission::SEARCH_TEAM) || $user->can(Permission::SEARCH_PROJECT) || $user->can(Permission::SEARCH_TASK)
        			|| $user->can(Permission::SEARCH_APPROVE_TASK) || $user->can(Permission::SEARCH_DEPARTMENT_CALENDAR) || $user->can(Permission::SEARCH_PROJECT_CALENDAR) || $user->can(Permission::SEARCH_INDIVIDUAL_CALENDAR))
                	?['label' => 'เมนูสำหรับผู้ใช้งานระบบ', 'options' => ['class' => 'header']]:array(),
                		
                	($user->can(Permission::SEARCH_TEAM))?['label' => 'ทีม', 'icon' => 'users', 'url' => ['/team']]:array(),
                	($user->can(Permission::SEARCH_DEPARTMENT_CALENDAR) || $user->can(Permission::SEARCH_PROJECT_CALENDAR) || $user->can(Permission::SEARCH_INDIVIDUAL_CALENDAR))?
                	[
	                	'label' => 'การจัดการปฏิทิน',
	                	'icon' => 'calendar',
	                	'url' => '#',
	                	'items' => [
	                		($user->can(Permission::SEARCH_DEPARTMENT_CALENDAR))?array('label' => 'ปฏิทินแผนก', 'icon' => '', 'url' => ['/event/devision']):array(),
	                		($user->can(Permission::SEARCH_PROJECT_CALENDAR))?array('label' => 'ปฏิทินโครงการ', 'icon' => '', 'url' => ['/event/project']):array(),
	                		($user->can(Permission::SEARCH_INDIVIDUAL_CALENDAR))?array('label' => 'ปฏิทินส่วนบุคคล', 'icon' => '', 'url' => ['/event']):array(),
	                	],
                	]:array(),
                	
                	($user->can(Permission::SEARCH_PROJECT))?['label' => 'โครงการ', 'icon' => 'file', 'url' => ['/project']]:array(),
                	($user->can(Permission::SEARCH_TASK))?['label' => 'งาน', 'icon' => 'tasks', 'url' => ['/task/privatetask']]:array(),
                	($user->can(Permission::SEARCH_APPROVE_TASK))?['label' => 'อนุมัติปิดงาน', 'icon' => 'thumb-tack', 'url' => ['/approve']]:array(),
                ],
            ]
        ) ?>
    </section>

</aside>
