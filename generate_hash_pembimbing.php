<?php
// Jalankan sekali: php generate_hash_pembimbing.php
// Lalu salin output ke update_db_pembimbing.sql (ganti hash di INSERT)
echo password_hash('pembimbing123', PASSWORD_DEFAULT);
