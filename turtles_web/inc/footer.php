<?php $root = $root ?? ''; ?>
    <footer id="footer">
        <img src="<?= $root ?>images/footer.gif" alt="">
        <p>Copyright © The Turtles All Rights Reserved.</p>
    </footer>

    <script src="<?= $root ?>js/nav.js"></script>
<?php if (!empty($page_scripts)): ?>
<?php foreach ($page_scripts as $script): ?>
    <script src="<?= htmlspecialchars($root . $script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
