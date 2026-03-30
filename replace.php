<?php
$directory = '/opt/lampp/htdocs/hotel';

$files = [
    'public/login.php',
    'public/register.php',
    'index.php',
    'includes/student_sidebar.php',
    'includes/admin_sidebar.php',
    'includes/header.php',
    'admin/dashboard.php',
    'admin/manage_rooms.php',
    'admin/allocations.php',
    'admin/manage_students.php',
    'student/payment.php',
    'student/my_room.php',
    'student/apply.php',
    'student/complaints.php',
    'student/dashboard.php'
];

foreach ($files as $file) {
    $path = $directory . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $content = str_replace('blue-', 'green-', $content);
        $content = str_replace("primary: '#0d6efd'", "primary: '#16a34a'", $content);
        $content = str_replace("from-[#0d6efd]", "from-green-600", $content);
        $content = str_replace("to-[#0a58ca]", "to-green-700", $content);

        file_put_contents($path, $content);
        echo "Updated $file\n";
    } else {
        echo "File not found: $file\n";
    }
}
?>
