<?php
require __DIR__ . '/../app/bootstrap.php'; start_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
verify_csrf(); $_SESSION = []; session_destroy(); redirect('/admin/login.php');
