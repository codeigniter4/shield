# Customizing Views

Shield provides the default view files, but they are sample files.
Customization is recommended.

If your application uses a different method to convert view files to HTML than
CodeIgniter's built-in `view()` helper, see
[Integrating Custom View Libraries](./integrating_custom_view_libs.md).

## Change $views

Change values in `$views` in the **app/Config/Auth.php** file.

For example, if you customize the login page, change the value for `'login'`:

```php
public array $views = [
    'login'                       => '\App\Views\Shield\login', // changed this line.
    'register'                    => '\CodeIgniter\Shield\Views\register',
    'layout'                      => '\CodeIgniter\Shield\Views\layout',
    'action_email_2fa'            => '\CodeIgniter\Shield\Views\email_2fa_show',
    'action_email_2fa_verify'     => '\CodeIgniter\Shield\Views\email_2fa_verify',
    'action_email_2fa_email'      => '\CodeIgniter\Shield\Views\Email\email_2fa_email',
    'action_email_activate_show'  => '\CodeIgniter\Shield\Views\email_activate_show',
    'action_email_activate_email' => '\CodeIgniter\Shield\Views\Email\email_activate_email',
    'magic-link-login'            => '\CodeIgniter\Shield\Views\magic_link_form',
    'magic-link-message'          => '\CodeIgniter\Shield\Views\magic_link_message',
    'magic-link-email'            => '\CodeIgniter\Shield\Views\Email\magic_link_email',
];
```

## Copy View File

Copy the file you want to customize in **vendor/codeigniter4/shield/src/Views/**
to the **app/Views/Shield/** folder.

## Customize Content

Customize the content of the view file in **app/Views/Shield/** as you like.
