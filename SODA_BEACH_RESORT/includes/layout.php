<?php
function eye_svg() {
    return '<button type="button" class="pw-toggle" aria-label="Show or hide password"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><line class="slash" x1="3" y1="21" x2="21" y2="3"/></svg></button>';
}

function pw_field($name, $placeholder) {
    return '<div class="pw-wrap"><input type="password" name="' . e($name) . '" placeholder="' . e($placeholder) . '" required>' . eye_svg() . '</div>';
}

function page_header($title, $bodyClass = '') {
    $u = current_user(); $b = BASE_URL;
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' · Soda Beach Resort</title>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . $b . '/assets/css/style.css"></head>';
    echo '<body class="' . e($bodyClass) . '"><div class="shade">';
    if ($bodyClass === 'auth') return;
    echo '<header class="topbar"><a class="brand" href="' . $b . '/index.php">Soda Beach Resort</a><nav>';
    if ($u && $u['role'] === 'staff') {
        echo "<a href=\"$b/admin/dashboard.php\">Dashboard</a><a href=\"$b/admin/validate.php\">Validate</a><a href=\"$b/admin/reports.php\">Reports</a><a href=\"$b/admin/manage.php\">Manage</a>";
    } elseif ($u) {
        echo "<a href=\"$b/book.php\">Book</a><a href=\"$b/my-bookings.php\">My Bookings</a>";
    }
    echo $u ? "<a href=\"$b/logout.php\">Logout (" . e($u['name']) . ")</a>" : "<a href=\"$b/login.php\">Login</a><a href=\"$b/register.php\">Register</a>";
    echo '</nav></header>';
}

function page_footer($extraScripts = '') {
    echo '</div><script src="' . BASE_URL . '/assets/js/app.js"></script>' . $extraScripts . '</body></html>';
}
