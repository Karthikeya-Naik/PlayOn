<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PlayOn</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <style>
    /* Custom Styles for Admin Header */
    body {
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f0f3f8;
        color: #525f7f;
    }

    #sidebar-wrapper {
        min-height: 100vh;
        width: 280px;
        margin-left: -280px;
        transition: margin 0.25s ease-out;
        background: linear-gradient(135deg, #172b4d 0%, #1a1f3c 100%);
        box-shadow: 0 0 35px 0 rgba(49, 57, 66, 0.5);
        z-index: 1040;
    }

    #sidebar-wrapper .sidebar-heading {
        height: 70px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    #wrapper.toggled #sidebar-wrapper {
        margin-left: 0;
    }

    #page-content-wrapper {
        min-width: 100vw;
        transition: all 0.25s ease-out;
    }

    .list-group-item.sidebar-item {
        background-color: transparent;
        color: rgba(255, 255, 255, 0.7);
        border: none;
        border-radius: 7px;
        margin: 5px 15px;
        padding: 12px 20px;
        transition: all 0.3s ease;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .list-group-item.sidebar-item:hover, 
    .list-group-item.sidebar-item.active {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .list-group-item.sidebar-item i {
        width: 24px;
        font-size: 1.1rem;
        text-align: center;
        margin-right: 8px;
        opacity: 0.8;
    }

    .list-group-item.sidebar-item:hover i,
    .list-group-item.sidebar-item.active i {
        opacity: 1;
    }

    .navbar {
        background-color: #fff !important;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, .15);
        height: 70px;
        padding: 0.75rem 1.5rem;
    }

    .navbar .form-control {
        border-radius: 30px;
        padding-left: 15px;
        background-color: #f6f9fc;
        border: none;
        box-shadow: 0 1px 3px rgba(50, 50, 93, 0.15), 0 1px 0 rgba(0, 0, 0, 0.02);
        transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .navbar .form-control:focus {
        width: 300px;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .navbar .btn-primary {
        border-radius: 30px;
        padding: 0.5rem 1rem;
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
        box-shadow: 0 2px 5px rgba(94, 114, 228, 0.3);
    }

    .dropdown-menu {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 50px 100px rgba(50, 50, 93, .1), 0 15px 35px rgba(50, 50, 93, .15), 0 5px 15px rgba(0, 0, 0, .1);
        padding: 1rem 0;
        min-width: 12rem;
    }

    .dropdown-item {
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background-color: #f6f9fc;
        color: #5e72e4;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 5px;
        opacity: 0.7;
    }

    .sidebar-footer {
        padding: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .progress {
        height: 6px;
        background-color: rgba(255,255,255,0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    /* Notification Animation */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            transform: scale(1);
        }
    }

    .notification-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #f5365c;
        color: white;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
        box-shadow: 0 3px 6px rgba(245, 54, 92, 0.3);
    }

    /* Media Queries */
    @media (min-width: 992px) {
        #sidebar-wrapper {
            margin-left: 0;
        }
        
        #page-content-wrapper {
            min-width: 0;
            width: 100%;
        }
        
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -280px;
        }
    }

    .menu-toggle-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f6f9fc;
        color: #5e72e4;
        box-shadow: 0 2px 5px rgba(50, 50, 93, 0.1);
        transition: all 0.3s;
    }

    .menu-toggle-icon:hover {
        background: #5e72e4;
        color: white;
        transform: rotate(90deg);
    }

    /* Brand gradient text */
    .brand-text {
        font-weight: 800;
        font-size: 1.5rem;
        background: linear-gradient(45deg, #5e72e4, #825ee4);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading d-flex align-items-center px-4 py-3">
                <i class="fas fa-volleyball-ball me-2 text-primary" style="font-size: 1.5rem;"></i>
                <span class="text-white fw-bold">PlayOn Admin</span>
            </div>
            <div class="list-group list-group-flush mt-3">
                <a href="dashboard.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="venues.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-map-marker-alt"></i> Venues
                </a>
                <a href="bookings.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
                <a href="users.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="reviews.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-star"></i> Reviews
                </a>
                <a href="reports.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="settings.php" class="list-group-item list-group-item-action py-3 sidebar-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
            <div class="sidebar-footer mt-auto">
                <div class="small text-white-50 mb-3">System Health</div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-white-50 small">Server Load</span>
                    <div class="progress" style="width: 70%">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 25%"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white-50 small">Memory Usage</span>
                    <div class="progress" style="width: 70%">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid px-lg-4">
                    <button class="btn menu-toggle-icon shadow-sm" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex d-lg-none align-items-center ms-3">
                        <span class="brand-text">PlayOn</span>
                    </div>

                    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-ellipsis-v text-primary"></i>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <form class="d-none d-lg-flex ms-auto me-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input class="form-control" type="search" placeholder="Search for..." aria-label="Search">
                            </div>
                        </form>

                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item dropdown me-3 position-relative">
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                                    <i class="fas fa-bell fa-lg text-muted"></i>
                                    <span class="notification-badge">3</span>
                                </a>
                            </li>
                            <li class="nav-item me-3">
                                <a class="nav-link d-flex align-items-center" href="../index.php" target="_blank">
                                    <i class="fas fa-external-link-alt me-1 text-muted"></i> 
                                    <span class="d-none d-md-inline">Visit Site</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="avatar-circle me-2">
                                        <?php echo substr(htmlspecialchars($_SESSION['admin_name']), 0, 1); ?>
                                    </div>
                                    <span class="d-none d-lg-inline fw-semibold">
                                        <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                                    <li>
                                        <div class="dropdown-item-text px-4 py-3 border-bottom">
                                            <div class="small text-muted">Signed in as</div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                                        </div>
                                    </li>
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                                    <li><a class="dropdown-item" href="activity_log.php"><i class="fas fa-list"></i> Activity Log</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Notifications Modal -->
            <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold" id="notificationsModalLabel">
                                <i class="fas fa-bell text-primary me-2"></i> Notifications
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action p-3 border-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-circle bg-success text-white">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <h6 class="mb-1 fw-bold">New booking request</h6>
                                                <span class="badge bg-primary rounded-pill">New</span>
                                            </div>
                                            <p class="mb-1">A new booking has been received from John Doe.</p>
                                            <small class="text-muted">3 mins ago</small>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action p-3 border-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-circle bg-info text-white">
                                                <i class="fas fa-server"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 fw-bold">Server update completed</h6>
                                            </div>
                                            <p class="mb-1">System maintenance completed successfully.</p>
                                            <small class="text-muted">1 hour ago</small>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action p-3 border-0">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-circle bg-warning text-white">
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 fw-bold">New review posted</h6>
                                            </div>
                                            <p class="mb-1">A new 5-star review has been posted for Green Park venue.</p>
                                            <small class="text-muted">3 hours ago</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center bg-light">
                            <a href="notifications.php" class="btn btn-sm btn-primary rounded-pill px-4">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>