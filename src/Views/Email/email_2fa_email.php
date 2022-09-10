<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= lang('Auth.email2FASubject') ?></title>
</head>

<body>
    <p><?= lang('Auth.email2FAMailBody') ?></p>
    <div style="text-align: center"><h1><?= $code ?></h1></div>
    <b><?= lang('Auth.emaiInfo') ?></b>
    <p><?= lang('Auth.emailIpAddress') ?> <?= $ipAddress ?></p>
    <p><?= lang('Auth.emailDevice') ?> <?= $userAgent ?></p>
    <p><?= lang('Auth.emailDate') ?> <?= $date ?></p>
</body>

</html>
