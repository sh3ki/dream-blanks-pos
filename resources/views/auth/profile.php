<div class="card" style="max-width:720px;">
    <h3 style="margin-top:0;">Profile Settings</h3>
    <form method="post" action="<?= e(url('/profile')) ?>">
        <?= csrf_field() ?>
        <div class="grid grid-2">
            <div class="field">
                <label for="first_name">First Name</label>
                <input class="input" id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="last_name">Last Name</label>
                <input class="input" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required>
            </div>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input class="input" id="email" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
        </div>

        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
