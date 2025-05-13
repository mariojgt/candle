function dashboard() {
    return {
        siteId: null,
        period: "30days",
        charts: {
            visitors: null,
            devices: null
        },

        init() {
            // Set site ID from config
            this.siteId = window.dashboardConfig?.siteId || null;

            // Load data first, then charts will be created when data arrives
            if (this.siteId) {
                this.loadDashboardData();
            }
        },

        loadDashboardData() {
            const routes = window.dashboardConfig?.routes || {};
            const ajaxConfig = window.ajaxSetup?.() || {};

            // Fetch pageviews
            if (routes.pageviews) {
                fetch(`${routes.pageviews}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            // Update pageviews metric
                            const pageviewsElement = document.getElementById('metric-pageviews-value');
                            if (pageviewsElement) {
                                pageviewsElement.textContent = data.data.total.toLocaleString();
                            }

                            // Update chart
                            this.updateVisitorsChart(data.data.pageviews || [], 'pageviews');
                        }
                    })
                    .catch(error => console.error('Error fetching pageviews:', error));
            }

            // Fetch unique visitors
            if (routes.uniqueVisitors) {
                fetch(`${routes.uniqueVisitors}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            // Update visitors metric
                            const visitorsElement = document.getElementById('metric-visitors-value');
                            if (visitorsElement) {
                                visitorsElement.textContent = data.data.total.toLocaleString();
                            }

                            // Update chart
                            this.updateVisitorsChart(data.data.visitors || [], 'visitors');
                        }
                    })
                    .catch(error => console.error('Error fetching visitors:', error));
            }

            // Fetch sessions (for bounce rate and duration)
            if (routes.sessions) {
                fetch(`${routes.sessions}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            // Update bounce rate metric
                            const bounceElement = document.getElementById('metric-bounce-value');
                            if (bounceElement) {
                                bounceElement.textContent = data.data.bounce_rate + '%';
                            }

                            // Update duration metric
                            const durationElement = document.getElementById('metric-duration-value');
                            if (durationElement) {
                                durationElement.textContent = data.data.average_duration_formatted;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching sessions:', error));
            }

            // Fetch device information
            if (routes.devices) {
                fetch(`${routes.devices}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            this.updateDevicesChart(data.data);
                        }
                    })
                    .catch(error => console.error('Error fetching devices:', error));
            }

            // Fetch top pages
            if (routes.topPages) {
                fetch(`${routes.topPages}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data && data.data.length > 0) {
                            this.renderTopPagesTable(data.data);
                        } else {
                            const topPagesTable = document.getElementById('top-pages-table');
                            if (topPagesTable) {
                                topPagesTable.innerHTML = `
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No data available
                                        </td>
                                    </tr>
                                `;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching top pages:', error));
            }

            // Fetch referrers
            if (routes.referrers) {
                fetch(`${routes.referrers}?site_id=${this.siteId}&period=${this.period}`, ajaxConfig)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data && data.data.length > 0) {
                            this.renderReferrersTable(data.data);
                        } else {
                            const referrersTable = document.getElementById('referrers-table');
                            if (referrersTable) {
                                referrersTable.innerHTML = `
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No data available
                                        </td>
                                    </tr>
                                `;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching referrers:', error));
            }
        },

        updateVisitorsChart(data, type) {
            try {
                const visitorsCanvas = document.getElementById('visitors-chart');
                if (!visitorsCanvas) {
                    console.log('Visitors chart element not found');
                    return;
                }

                if (!data || data.length === 0) {
                    console.log('No visitor data available to display');
                    return;
                }

                // Extract dates and counts
                const labels = data.map(item => item.date ? this.formatDate(item.date) : '');
                const values = data.map(item => item.count || 0);

                // If chart doesn't exist yet, create it
                if (!this.charts.visitors) {
                    const visitorsCtx = visitorsCanvas.getContext('2d');

                    // Create initial datasets structure
                    const datasets = [
                        {
                            label: 'Pageviews',
                            data: [],
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Unique Visitors',
                            data: [],
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true
                        }
                    ];

                    // Set initial data based on type
                    if (type === 'pageviews') {
                        datasets[0].data = values;
                    } else if (type === 'visitors') {
                        datasets[1].data = values;
                    }

                    // Create chart
                    this.charts.visitors = new Chart(visitorsCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                    console.log('Visitors chart created with', type, 'data');
                } else {
                    // Update existing chart
                    if (type === 'pageviews') {
                        this.charts.visitors.data.labels = labels;
                        this.charts.visitors.data.datasets[0].data = values;
                    } else if (type === 'visitors') {
                        this.charts.visitors.data.datasets[1].data = values;
                    }

                    // Update chart
                    this.charts.visitors.update();
                    console.log('Visitors chart updated with', type, 'data');
                }
            } catch (error) {
                console.error('Error updating visitors chart:', error);
            }
        },

        updateDevicesChart(data) {
            try {
                const devicesCanvas = document.getElementById('devices-chart');
                if (!devicesCanvas) {
                    console.log('Devices chart element not found');
                    return;
                }

                if (!data.devices || data.devices.length === 0) {
                    console.log('No device data available to display');
                    return;
                }

                // Extract device data
                const desktop = data.devices.find(item => item.device_type === 'desktop')?.count || 0;
                const mobile = data.devices.find(item => item.device_type === 'mobile')?.count || 0;
                const tablet = data.devices.find(item => item.device_type === 'tablet')?.count || 0;

                // Create or update chart
                if (!this.charts.devices) {
                    const devicesCtx = devicesCanvas.getContext('2d');
                    this.charts.devices = new Chart(devicesCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Desktop', 'Mobile', 'Tablet'],
                            datasets: [{
                                data: [desktop, mobile, tablet],
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(251, 191, 36, 0.8)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                    console.log('Devices chart created');
                } else {
                    // Update chart data
                    this.charts.devices.data.datasets[0].data = [desktop, mobile, tablet];
                    this.charts.devices.update();
                    console.log('Devices chart updated');
                }
            } catch (error) {
                console.error('Error updating devices chart:', error);
            }
        },

        renderTopPagesTable(pages) {
            const tableBody = document.getElementById('top-pages-table');
            if (!tableBody) {
                console.log('Top pages table element not found');
                return;
            }

            let html = '';

            pages.forEach((page, index) => {
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-medium ${index < 3 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'}">
                                    ${index + 1}
                                </span>
                                <div class="ml-3 truncate max-w-xs">${this.formatUrl(page.url)}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${page.pageviews}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${page.visitors}</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;
        },

        renderReferrersTable(referrers) {
            const tableBody = document.getElementById('referrers-table');
            if (!tableBody) {
                console.log('Referrers table element not found');
                return;
            }

            let html = '';

            referrers.forEach((referrer, index) => {
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-medium ${index < 3 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'}">
                                    ${index + 1}
                                </span>
                                <div class="ml-3">${this.formatReferrer(referrer.referrer)}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${referrer.count}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${(referrer.conversion_rate || 0) + '%'}</td>
                    </tr>
                `;
            });

            tableBody.innerHTML = html;
        },

        formatDate(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
            } catch (error) {
                return dateString;
            }
        },

        formatUrl(url) {
            if (!url) return 'Unknown';

            try {
                // Handle homepage case
                if (url === '/') return '/';

                // Extract path from URL if it's a full URL
                let path = url;
                if (url.startsWith('http')) {
                    const urlObj = new URL(url);
                    path = urlObj.pathname;
                }

                // Truncate if too long
                if (path.length > 50) {
                    path = path.substring(0, 47) + '...';
                }

                return path;
            } catch (e) {
                // Fallback for invalid URLs
                return url.substring(0, 50);
            }
        },

        formatReferrer(referrer) {
            if (!referrer) return 'Direct / None';

            try {
                // Convert to hostname if it's a full URL
                if (referrer.startsWith('http')) {
                    const urlObj = new URL(referrer);
                    return urlObj.hostname;
                }
                return referrer;
            } catch (e) {
                return referrer;
            }
        }
    };
}
