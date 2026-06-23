        <!-- ===== Footer ===== -->
        <footer class="footer">
            <div class="user-details">
                <div class="avatar-sm"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
                <div class="info">
                    <div class="name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                    <div class="role"><?= ucfirst($_SESSION['role']) ?></div>
                    <div class="email"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($_SESSION['email'] ?? 'admin@ministry.gov') ?></div>
                </div>
            </div>
            <div>
                <i class="fas fa-calendar-alt me-1"></i> <?= date('Y') ?> &copy; TMS Portal
            </div>
        </footer>
    </main>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Common JS utilities -->
<script>
    async function fetchJSON(url, options = {}) {
        const res = await fetch(url, { headers: { 'Content-Type': 'application/json' }, ...options });
        if (!res.ok) throw new Error('Network error');
        return await res.json();
    }
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container') || (() => {
            const c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'position-fixed bottom-0 end-0 p-3';
            c.style.zIndex = 1050;
            document.body.appendChild(c);
            return c;
        })();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
    function confirmDelete(url, message = 'Are you sure?') {
        if (confirm(message)) {
            fetch(url, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { showToast('Deleted successfully.'); location.reload(); }
                    else showToast('Error: ' + data.message, 'danger');
                })
                .catch(err => showToast('Error: ' + err.message, 'danger'));
        }
    }
</script>
</body>
</html>