<p>Your authentication token is: <b><?= $code ?></b></p>
<?php if(setting('Auth.allowEmail2FALoginWithLink')):
    $link = site_url(route_to('auth-action-verify')) . '?' . setting('Auth.allowEmail2FALoginFieldName') . '=' . $code;
    ?>
<p>You could click to login: <a href="<?=  $link ?>"><?= $link ?></a></p>
<?php endif; ?>
