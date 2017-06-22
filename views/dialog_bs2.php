<?php extract(array('id' => 'modalDialog'), EXTR_SKIP);?>
<!-- Modal -->
<style>
    .progress-bar.animate {
        width: 100%;
    }
    .modal .modal-dialog-form .modal-body {
        padding: 4px 0 4px 0;
    }
    .modal .modal-dialog-form .modal-body .form-actions {
        padding: 19px 20px 20px;
        margin-top: 0px;
        text-align: right;
        background-color: transparent;
    }
    .modal .form-wrapper form {
        /*padding: 20px 0 0 0;*/
        margin: 0;
    }
    .modal-body .alert {
        display: none;
    }
</style>
<div class="modal hide fade" id="<?php echo $id; ?>">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title"></h3>
    </div>
    <div class="form-wrapper">
        
    </div>
</div>

