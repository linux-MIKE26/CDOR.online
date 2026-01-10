<?php if (!empty($_SESSION['user']) && $_SESSION['user']['role']==='admin'): ?>
<a href="/admin/dashboard.php" class="admin-gear">⚙</a>
<?php endif; ?>
