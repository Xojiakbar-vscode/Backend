            </div> <!-- container-fluid end -->
        </div> <!-- main-content end -->
    </div> <!-- wrapper end -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarToggle').click(function() {
                $('.sidebar').toggleClass('active');
                $('.main-content').toggleClass('active');
            });

            // Tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Delete confirmation
            $('.delete-btn').click(function(e) {
                if(!confirm('Haqiqatan ham o\'chirmoqchimisiz?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>