        </div><!-- /.content -->
    </main>

    <script>
        // Sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }

        // User dropdown
        const userBtn = document.getElementById('userBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (userBtn) {
            userBtn.addEventListener('click', () => {
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!userBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }

        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabGroup = btn.closest('.card') || document;
                tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                tabGroup.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                const target = document.getElementById(btn.dataset.tab);
                if (target) target.classList.add('active');
            });
        });

        // Delete confirmation
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                if (!confirm(el.dataset.confirm || 'Apakah Anda yakin?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
