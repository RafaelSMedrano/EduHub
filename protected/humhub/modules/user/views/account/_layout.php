<?php
use yii\helpers\Html;
use humhub\widgets\FooterMenu;
use humhub\modules\user\models\Profile;

// Obtém o usuário logado
$user = Yii::$app->user->identity;

// Verifica se o usuário e o perfil existem
if ($user !== null && $user->profile !== null) {
    // Obtém o valor do atributo desejado do perfil
    $isRegistering = $user->profile->isRegistering;
}
?>

<?php if($isRegistering == false): ?> 
    <div class="container">    
        <div class="row">
            <div class="col-md-3">
                <?php echo \humhub\modules\user\widgets\AccountMenu::widget(); ?>
            </div>
            <div class="col-md-9">
                <div class="panel panel-default">
                        <?php echo $content; ?>
                </div>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
            </div>                
           
        </div>
    </div>

<?php else: ?>

        <div class="panel panel-default" style="background-color: rgba(255, 0, 0, 0); box-shadow: none;">
            <?php echo $content; ?>
        </div>
            
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
            
<?php endif; ?>


?>







<?php
/*
use humhub\widgets\FooterMenu;
use humhub\modules\user\models\Profile;







<div class="container" style="box-shadow: none;">
    <div class="row" style="box-shadow: none;"">
    <?php if (isset($accountStatus)): ?>
        <div class="col-md-3">
            <?php echo $accountStatus ?>
            <?php
            echo \humhub\modules\user\widgets\AccountMenu::widget(); ?>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
        </div>
        <div class="panel panel-default">
                <?php echo $content; ?>
        </div>
            
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
    <?php else: ?>
        oi?
        <div class="panel panel-default" style="background-color: rgba(255, 0, 0, 0); box-shadow: none;">
                <?php echo $content; ?>
        </div>
            
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
    <?php endif; ?> 
        
            
        
    </div>
</div>
*/