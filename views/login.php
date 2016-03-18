<?php require_once('header.php'); ?>
<div class="container">
        <?=form_open("admin/authenticate", array('class' => 'form-horizontal'));?>
        <?=form_hidden('referer', $referer); ?>
        <div class="form-group">
            <?=form_label('Username', 'username', array('class' => 'col-sm-2 control-label')); ?>
            <div class="col-sm-4">
                <?=form_input(array('name'=>'username', 'id'=>'username', 'class' => 'form-control', 'placeholder' => 'Username')); ?>
            </div>
        </div>
    
        <div class="form-group">
            <?=form_label('Password', 'passwd', array('class' => 'col-sm-2 control-label')); ?>
            <div class="col-sm-4">
                <?=form_password(array('name'=>'passwd', 'id'=>'passwd', 'class' => 'form-control', 'placeholder' => 'Password')); ?>
            </div>
        </div>
    
        <div class="form-group">
            <div class="col-sm-6 text-right">
                <?=form_submit('submit', 'Submit', 'class="btn"'); ?>
            </div>
        </div>
        <?=form_close();?>
</div>
<?php require_once('footer.php'); ?>