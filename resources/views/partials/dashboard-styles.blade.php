<style>
    .dashboard-page { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; }
    .dashboard-sidebar { }
    .dashboard-nav { display: flex; flex-direction: column; gap: 0.25rem; }
    .dashboard-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 8px; color: var(--text-secondary); font-weight: 500; transition: all 0.2s; text-decoration: none; }
    .dashboard-nav-link:hover { background: var(--bg-secondary); color: var(--text-main); }
    .dashboard-nav-link.active { background: var(--primary); color: white; }
    .nav-badge { background: #ef4444; color: white; font-size: 0.75rem; padding: 0.125rem 0.5rem; border-radius: 9999px; margin-left: auto; }
    .dashboard-nav-link.logout { width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 1rem; font-family: inherit; }
    .dashboard-nav-link.logout:hover { background: #fee2e2; color: #dc2626; }
    .dashboard-nav-form { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
    .dashboard-content { }
    .dashboard-title { font-size: 1.75rem; font-weight: 700; color: var(--text-main); margin-bottom: 2rem; }
    @media (max-width: 768px) {
        .dashboard-page { grid-template-columns: 1fr; }
        .dashboard-sidebar { order: 2; }
        .dashboard-nav { flex-direction: row; flex-wrap: wrap; }
        .dashboard-nav-form { margin: 0; padding: 0; border: none; }
    }
</style>
