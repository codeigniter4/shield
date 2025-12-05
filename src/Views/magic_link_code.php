<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.verifyMagicCode') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?= lang('Auth.verifyMagicCode') ?></h5>
                <form action="<?= route_to('verify-magic-link') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="magicCode" class="form-label"><?= lang('Auth.magicCodeText', [strtok(config('Auth')->magicLoginMode, '-')]) ?></label>
                        <input type="text" class="form-control" name="magicCode" id="magicCode" maxlength="<?= strtok(config('Auth')->magicLoginMode, '-'); ?>" pattern="[a-zA-Z0-9]{<?= strtok(config('Auth')->magicLoginMode, '-'); ?>}" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= lang('Auth.login') ?></button>
                </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>