</div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@2.11.6/dist/umd/popper.min.js"></script>
    
    <script>
        // Menu Toggle Script
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });

        // Add hover effect to cards and table rows
        $(document).ready(function() {
            $('.card.hover-shadow').hover(
                function() {
                    $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
                }
            );

            $('tbody tr').hover(
                function() {
                    $(this).addClass('bg-light');
                },
                function() {
                    $(this).removeClass('bg-light');
                }
            );

            // Tooltips initialization
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Current page highlighting
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-item').forEach(item => {
                const href = item.getAttribute('href');
                if (href === currentPage) {
                    item.classList.add('active');
                }
            });
        });

        // Chart color theme options
        const chartColorOptions = {
            primary: '#5e72e4',
            secondary: '#f7fafc',
            success: '#2dce89',
            info: '#11cdef',
            warning: '#fb6340',
            danger: '#f5365c',
            dark: '#172b4d',
            light: '#f6f9fc'
        };
    </script>

    <!-- Footer -->
    <footer class="footer bg-white py-3 mt-auto border-top" style="display: none;">
        <div class="container-fluid">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-6">
                    <div class="text-muted small">
                        &copy; <?php echo date('Y'); ?> PlayOn Admin Dashboard. All rights reserved.
                    </div>
                </div>
                <div class="col-lg-6 text-end">
                    <div class="text-muted small">
                        Version 1.0.2 | Last updated: April 10, 2025
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>