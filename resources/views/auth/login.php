<h2 class="auth-title">Welcome Back</h2>
<p class="auth-subtitle">Sign in to continue to Dream Blanks POS.</p>

<?php foreach (($flash ?? []) as $item): ?>
    <div class="alert <?= $item['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= e($item['message']) ?>
    </div>
<?php endforeach; ?>

<form method="post" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>
    <div class="field">
        <label for="identity">Email or Username</label>
        <input id="identity" class="input" type="text" name="identity" required>
    </div>

    <div class="field">
        <label for="password">Password</label>
        <input id="password" class="input" type="password" name="password" required>
    </div>

    <div class="field" style="display:flex;justify-content:space-between;align-items:center;">
        <label style="margin:0;"><input type="checkbox" name="remember_me" value="1"> Remember me</label>
        <a href="#" style="color:#4b5563;font-size:13px;">Forgot password?</a>
    </div>

    <button class="btn btn-primary w-full" type="submit">Login</button>
</form>
