document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('Sidebar');
    const toggleBtn = document.getElementById('SidebarToggle');
    const backdrop = document.getElementById('SidebarBackdrop');

    if (!sidebar || !toggleBtn || !backdrop) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add('sidebar--open');
        backdrop.classList.add('sidebar-backdrop--visible');
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar--open');
        backdrop.classList.remove('sidebar-backdrop--visible');
    }

    toggleBtn.addEventListener('click', () => {
        const isOpen = sidebar.classList.contains('sidebar--open');
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    backdrop.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('sidebar--open');
            backdrop.classList.remove('sidebar-backdrop--visible');
        }
    });
});
