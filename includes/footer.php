            </div>
        </main>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal" id="logoutModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Confirm Logout</h3>
                <button class="modal-close" onclick="hideLogoutModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideLogoutModal()">No</button>
                <a href="<?php echo APP_URL; ?>/modules/auth/logout.php" class="btn btn-danger">Yes</a>
            </div>
        </div>
    </div>

    <script src="<?php echo APP_URL; ?>/public/js/main.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo APP_URL . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
