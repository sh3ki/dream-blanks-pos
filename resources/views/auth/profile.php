<?php
$initials = strtoupper(substr($user['first_name'] ?? '', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Profile Settings</h2>
        <p class="page-subtitle">Update personal details and preferences.</p>
    </div>
</div>

<div class="card profile-card">
    <form method="post" action="<?= e(url('/profile')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="profile-grid">
            <div class="profile-avatar-panel" data-avatar-scope>
                <div class="avatar avatar-lg" data-avatar-preview><?= e($initials !== '' ? $initials : 'DB') ?></div>
                <div class="field">
                    <label for="profile-avatar">Profile Photo</label>
                    <input class="input" id="profile-avatar" type="file" name="avatar" accept="image/*" data-avatar-input>
                    <div class="help-text">PNG or JPG up to 2MB.</div>
                </div>
            </div>

            <div>
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
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
