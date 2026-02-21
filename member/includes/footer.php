    <footer class="m-footer">
        <div class="container">
            &copy; <?php echo date('Y'); ?> Cloud Member System
        </div>
    </footer>
    <script>
    // Toggle password visibility
    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('navDropdown');
        if (dd && !e.target.closest('.nav-user')) { dd.classList.remove('show'); }
    });

    // Alert dismiss
    document.querySelectorAll('.alert-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.alert-box').style.display = 'none';
        });
    });

    // Password strength meter
    var pwInput = document.getElementById('password');
    if (pwInput) {
        var bar = document.querySelector('.pw-bar-fill');
        var txt = document.querySelector('.pw-text');
        if (bar && txt) {
            pwInput.addEventListener('input', function() {
                var v = this.value, s = 0;
                if (v.length >= 8) s++;
                if (v.length >= 12) s++;
                if (/[A-Z]/.test(v)) s++;
                if (/[0-9]/.test(v)) s++;
                if (/[^A-Za-z0-9]/.test(v)) s++;
                var pct = [0, 20, 40, 60, 80, 100][s];
                var colors = ['var(--gray-300)', 'var(--danger)', '#f97316', 'var(--warning)', '#84cc16', 'var(--success)'];
                var labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
                bar.style.width = pct + '%';
                bar.style.background = colors[s];
                txt.textContent = v.length ? labels[s] : '';
                txt.style.color = colors[s];
            });
        }
    }
    </script>
</body>
</html>
