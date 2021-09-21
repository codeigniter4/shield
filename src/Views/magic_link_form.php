<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?= lang('Auth.useMagicLink') ?></h5>

            <form action="<?= route_to('magic-link') ?>" method="post">
                <?= csrf_field() ?>

                <!-- Email -->
                <div class="mb-2">
                    <input type="email" class="form-control" name="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>"
                           value="<?= old('email', auth()->user()->email ?? null) ?>" required />
                </div>

                <div class="d-grid col-8 mx-auto m-3">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.useMagicLink') ?></button>
                </div>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
