<?php
// navigation.php - Reusable admin sidebar with hidden scrollbar (scrollable but no visible scrollbar)
// Include this file in all admin pages (dashboard.php, manage_*.php)
?>
<div class="sidebar" id="adminSidebar">
    <div class="logo-area">
        <div class="logo">⚡ARTECH</div>
        <button class="toggle-btn" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="menu">
        <div class="menu-title">CORE</div>
        <a href="dashboard.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
        <a href="manage_courses.php"><i class="fas fa-graduation-cap"></i><span>Courses</span></a>
        <a href="manage_sliders.php"><i class="fas fa-images"></i><span>Sliders</span></a>
        <a href="manage_services.php"><i class="fas fa-cogs"></i><span>Services</span></a>
        <a href="manage_portfolios.php"><i class="fas fa-briefcase"></i><span>Portfolio</span></a>
        <a href="manage_testimonials.php"><i class="fas fa-star"></i><span>Testimonials</span></a>
        <a href="manage_customers.php"><i class="fas fa-users"></i><span>Customers</span></a>
        <a href="manage_chairman.php"><i class="fas fa-microphone-alt"></i><span>Chairman Speech</span></a>
        <a href="manage_team.php"><i class="fas fa-users"></i><span>Team</span></a>
        <a href="manage_contact.php"><i class="fas fa-envelope"></i><span>Contact Messages</span></a>
        <div class="menu-title">SYSTEM</div>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</div>

<style>
    /* Sidebar styles – scrollable but scrollbar hidden */
    .sidebar {
        position: fixed;
        left: 20px;
        top: 20px;
        bottom: 20px;
        width: 280px;
        background: rgba(15,23,42,0.9);
        backdrop-filter: blur(20px);
        border-radius: 2rem;
        border: 1px solid var(--border, rgba(255,255,255,0.08));
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;
        box-shadow: var(--shadow, 0 20px 35px -10px rgba(0,0,0,0.4));
        overflow-y: auto;
        /* Hide scrollbar for Chrome, Safari, Edge */
        &::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for Firefox */
        scrollbar-width: none;
    }
    body.light .sidebar {
        background: rgba(255,255,255,0.9);
    }
    .sidebar.collapsed {
        width: 90px;
    }
    .logo-area {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border, rgba(255,255,255,0.08));
    }
    .logo {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary, #7c3aed), var(--secondary, #06b6d4));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        white-space: nowrap;
    }
    .toggle-btn {
        background: rgba(255,255,255,0.1);
        border: none;
        border-radius: 1rem;
        width: 40px;
        height: 40px;
        color: white;
        cursor: pointer;
        transition: 0.2s;
    }
    body.light .toggle-btn {
        background: rgba(0,0,0,0.05);
        color: #0f172a;
    }
    .toggle-btn:hover {
        background: var(--primary, #7c3aed);
        color: white;
    }
    .menu {
        padding: 1rem;
    }
    .menu-title {
        color: var(--muted, #94a3b8);
        font-size: 0.7rem;
        letter-spacing: 2px;
        margin: 1rem 1rem 0.5rem;
    }
    .menu a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 0.8rem 1rem;
        border-radius: 1.2rem;
        color: var(--muted, #94a3b8);
        text-decoration: none;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }
    .menu a i {
        width: 24px;
        font-size: 1.2rem;
        text-align: center;
    }
    .menu a:hover,
    .menu a.active {
        background: rgba(124,58,237,0.2);
        color: var(--primary-glow, #a855f7);
        transform: translateX(5px);
    }
    /* When sidebar is collapsed, hide text and menu titles */
    .sidebar.collapsed .logo,
    .sidebar.collapsed .menu span,
    .sidebar.collapsed .menu-title {
        display: none;
    }
    .sidebar.collapsed .menu a {
        justify-content: center;
        padding: 0.8rem;
    }
    /* Main content margin adjustment (to be used on parent container) */
    .main {
        margin-left: 310px;
        transition: margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .main.expand {
        margin-left: 120px;
    }
    @media (max-width: 768px) {
        .sidebar { left: 10px; }
        .main { margin-left: 100px; }
    }
</style>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const mainContent = document.querySelector('.main');
            
            if (!sidebar || !toggleBtn) return;
            
            function setSidebarCollapsed(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('collapsed');
                    if (mainContent) mainContent.classList.add('expand');
                } else {
                    sidebar.classList.remove('collapsed');
                    if (mainContent) mainContent.classList.remove('expand');
                }
                localStorage.setItem('adminSidebarCollapsed', collapsed ? 'true' : 'false');
            }
            
            const savedState = localStorage.getItem('adminSidebarCollapsed');
            if (savedState === 'true') {
                setSidebarCollapsed(true);
            } else {
                setSidebarCollapsed(false);
            }
            
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const isCollapsed = sidebar.classList.contains('collapsed');
                setSidebarCollapsed(!isCollapsed);
            });
            
            window.addEventListener('beforeunload', function() {
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('adminSidebarCollapsed', isCollapsed ? 'true' : 'false');
            });
        });
    })();
</script>