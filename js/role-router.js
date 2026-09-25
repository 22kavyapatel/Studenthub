(function () {
    const role = localStorage.getItem('userRole');
    const file = window.location.pathname.split('/').pop();
    const map = {
        assignments: { student:'student-assignments.html', teacher:'teacher-assignments.html', admin:'admin-assignments.html' },
        attendance: { student:'student-attendance.html', teacher:'teacher-attendance.html', admin:'admin-attendance.html' },
        events: { student:'student-events.html', teacher:'teacher-events.html', admin:'admin-events.html' },
        notices: { student:'student-notices.html', teacher:'teacher-notices.html', admin:'admin-notices.html' },
        profile: { student:'student-profile.html' }
    };
    const type = file.replace('.html','');
    if (!map[type]) return;
    if (!role || !map[type][role]) { window.location.replace('login.html'); return; }
    window.location.replace(map[type][role]);
})();
