/* CommunityOS front-end helpers: CSRF wiring, DataTables, Chart.js, AJAX forms */
window.CommunityOS = (function ($) {
    'use strict';

    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token.getAttribute('content') } });
    }

    const palette = ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316'];

    function initDataTables() {
        $('table.datatable').each(function () {
            if ($.fn.dataTable.isDataTable(this)) return;

            // Skip empty-state tables: a single full-width placeholder row
            // (<td colspan>) trips DataTables' "Incorrect column count" check.
            const $bodyRows = $(this).find('tbody > tr');
            if ($bodyRows.length === 0 || ($bodyRows.length === 1 && $bodyRows.find('td[colspan]').length)) {
                return;
            }

            $(this).DataTable({
                responsive: true,
                pageLength: 15,
                order: [],
                language: { search: '', searchPlaceholder: 'Search…' }
            });
        });
    }

    // Keep one Chart instance per canvas so re-rendering never stacks/duplicates.
    const chartRegistry = new WeakMap();

    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor = () => isDark() ? 'rgba(148,163,184,.15)' : '#eef2f7';
    const tickColor = () => isDark() ? '#94a3b8' : '#64748b';

    function renderCharts() {
        if (typeof Chart === 'undefined') return;

        document.querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
            const key = canvas.getAttribute('data-chart');
            const type = canvas.getAttribute('data-type') || 'line';
            const isCircular = (type === 'doughnut' || type === 'pie');

            fetch('/dashboard/chart/' + key, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function (payload) {
                    const labels = payload.labels || [];
                    const datasets = (payload.datasets || []).map(function (ds, i) {
                        const color = palette[i % palette.length];
                        if (isCircular) {
                            // one colour per slice; border matches the card bg
                            return Object.assign({
                                backgroundColor: labels.map((_, j) => palette[j % palette.length]),
                                borderColor: isDark() ? '#1e293b' : '#fff',
                                borderWidth: 2
                            }, ds);
                        }
                        return Object.assign({
                            backgroundColor: type === 'bar' ? color : color + '22',
                            borderColor: color,
                            borderWidth: 2,
                            borderRadius: type === 'bar' ? 6 : 0,
                            maxBarThickness: 48,
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            fill: type === 'line'
                        }, ds);
                    });

                    // Destroy any previous instance bound to this canvas.
                    const prev = chartRegistry.get(canvas) || (Chart.getChart && Chart.getChart(canvas));
                    if (prev) prev.destroy();

                    const chart = new Chart(canvas, {
                        type: type,
                        data: { labels: labels, datasets: datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: {
                                    display: isCircular || datasets.length > 1,
                                    position: isCircular ? 'bottom' : 'top',
                                    labels: { usePointStyle: true, boxWidth: 8, padding: 14, color: tickColor() }
                                },
                                tooltip: {
                                    callbacks: isCircular ? {
                                        label: function (ctx) {
                                            const data = ctx.dataset.data || [];
                                            const total = data.reduce((a, b) => a + (Number(b) || 0), 0) || 1;
                                            const val = Number(ctx.parsed) || 0;
                                            return ' ' + ctx.label + ': ' + val + ' (' + Math.round(val / total * 100) + '%)';
                                        }
                                    } : {}
                                }
                            },
                            cutout: type === 'doughnut' ? '62%' : undefined,
                            scales: isCircular ? {} : {
                                y: { beginAtZero: true, grid: { color: gridColor() }, ticks: { precision: 0, color: tickColor() } },
                                x: { grid: { display: false }, ticks: { color: tickColor() } }
                            }
                        }
                    });
                    chartRegistry.set(canvas, chart);
                    const box = canvas.closest('.chart-box');
                    if (box) box.classList.add('chart-ready');
                })
                .catch(() => {
                    const box = canvas.closest('.chart-box');
                    if (box) box.classList.add('chart-ready');
                });
        });
    }

    // Generic delete-confirmation for forms marked data-confirm.
    function bindConfirm() {
        $(document).on('submit', 'form[data-confirm]', function (e) {
            if (!window.confirm($(this).data('confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    }

    function bindSidebar() {
        $('#sidebarToggle').on('click', () => $('#appSidebar').toggleClass('show'));
    }

    // Light / dark theme toggle, persisted in localStorage.
    function applyThemeIcons(theme) {
        document.querySelectorAll('[data-theme-icon="dark"]').forEach(el => el.classList.toggle('d-none', theme !== 'dark'));
        document.querySelectorAll('[data-theme-icon="light"]').forEach(el => el.classList.toggle('d-none', theme === 'dark'));
    }
    function bindThemeToggle() {
        let theme = 'dark';
        try { theme = localStorage.getItem('co-theme') || 'dark'; } catch (e) {}
        document.documentElement.setAttribute('data-bs-theme', theme);
        applyThemeIcons(theme);

        $('#themeToggle').on('click', function () {
            const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('co-theme', next); } catch (e) {}
            applyThemeIcons(next);
            renderCharts(); // re-tint chart gridlines for the new theme
        });
    }

    $(function () {
        initDataTables();
        bindConfirm();
        bindSidebar();
        bindThemeToggle();
        renderCharts();   // auto-render any canvas[data-chart] on the page
    });

    return { initDataTables, renderCharts };
})(jQuery);
