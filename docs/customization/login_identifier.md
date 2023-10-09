# Customizing Login Identifier

If your application has a need to use something other than `email` or `username`, you may specify any valid column within the `users` table that you may have added. This allows you to easily use phone numbers, employee or school IDs, etc as the user identifier. You must implement the following steps to set this up:

This only works with the Session authenticator.

1. Create a [migration](http://codeigniter.com/user_guide/dbmgmt/migration.html) that adds a new column to the `users` table.
2. Edit `app/Config/Auth.php` so that the new column you just created is within the `$validFields` array.

    ```php
    public array $validFields = [
        'employee_id'
    ];
    ```

    If you have multiple login forms on your site that use different credentials, you must have all of the valid identifying fields in the array.

    ```php
    public array $validFields = [
        'email',
        'employee_id'
    ];
    ```
    !!! warning

        It is very important for security that if you add a new column for identifier, you must write a new **Validation Rules** and then set it using the [Customizing Validation Rules](./validation_rules.md) description.

3. Edit the login form to change the name of the default `email` input to the new field name.

    ```php
    <!-- Email -->
    <div class="mb-2">
        <input type="text" class="form-control" name="employee_id" autocomplete="new-employee-id" placeholder="12345" value="<?= old('employee_id') ?>" required />
    </div>
    ```
