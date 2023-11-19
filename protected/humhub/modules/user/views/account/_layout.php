<?php

use humhub\widgets\FooterMenu;

?>

<div class="container">
    
    <?php if (isset($accountStatus) && $accountStatus === 'register'): ?>
        
           
                <?php echo $content; ?>
            
            
        
    <?php else: ?>
        <div class="row">
            <div class="col-md-3">
              <?php
              echo \humhub\modules\user\widgets\AccountMenu::widget(); ?>
            </div>
             <div class="col-md-9">
                <div class="panel panel-default">
                    <?php echo $content; ?>
                </div>
            
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
            </div>
        </div>
        
    <?php endif; ?>
        
    
</div>
