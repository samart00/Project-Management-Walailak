<?php
use yii\web\View;
use yii\helpers\Html;
use common\models\User;

$baseUrl = \Yii::getAlias('@web');

/* @var $this \yii\web\View */
/* @var $content string */
$user = Yii::$app->user->identity;
$userName = "";
if(!empty($user)){
	$userName = $user->nameTh." ".$user->sernameTh;
}

?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?= Html::img(User::getPhotoUserViewer($user->_id),['class'=>'user-image']); ?>
                        <span class="hidden-xs"><?= $userName ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <?= Html::img(User::getPhotoUserViewer($user->_id),['class'=>'img-circle']); ?>
                            <p>
                                <?= $userName ?>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?php echo $baseUrl."/profile"; ?>" class="btn btn-default btn-flat">ประวัติส่วนตัว</a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'ออกจากระบบ',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
