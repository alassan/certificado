</div> <!-- Fecha .wrapper -->

<footer class="text-center py-3 bg-white border-top w-100">
    <div class="container">
        <small class="text-muted">
            &copy; <?= date('Y') ?> Sistema de Certificados • Desenvolvido por <strong>TeresinaDev</strong>
        </small>
    </div>
</footer>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para menu mobile -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById("menuToggle");
    const sidebar = document.querySelector(".sidebar");
    const body = document.body;

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("active");
            body.classList.toggle("menu-open");
        });
    }
});
</script>

<!-- Estilos adicionais para mobile -->
<style>
@media (max-width: 768px) {
    body.menu-open {
        overflow: hidden;
    }
    .sidebar.active {
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
    }
}
</style>
</body>
</html>