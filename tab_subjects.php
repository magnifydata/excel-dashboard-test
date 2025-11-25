<!-- 
    File: tab_subjects.php
    Purpose: Frontend for Subject Performance.
    Changes: Removed Overall Pass/Fail 3D Pie Chart KPI and its container.
-->

<div class="tab-section active">
    
    <!-- FILTER BAR -->
    <div class="global-filter-bar">
        <h2 style="margin-bottom:15px; border-bottom:1px solid var(--border); padding-bottom:10px;">
            Subject Analysis
        </h2>
        
        <div id="chartError" style="display:none; color: #ef4444; background: #fee2e2; padding: 10px; margin-bottom: 10px;"></div>

        <div class="filter-form">
            <div class="f-group">
                <label>Select Subject</label>
                <select id="subjectFilter" class="f-select" onchange="updateCharts()">
                    <option value="All">All Subjects (Overview)</option>
                </select>
            </div>
            <div class="f-group">
                <label>Select Cohort</label>
                <select id="cohortFilter" class="f-select" onchange="updateSemesters()">
                    <option value="All">All Cohorts</option>
                </select>
            </div>
            <div class="f-group">
                <label>Select Semester</label>
                <select id="semesterFilter" class="f-select" onchange="updateCharts()">
                    <option value="All">All Semesters</option>
                </select>
            </div>
            <!-- Status Filter kept if you want to reuse it later, visual only for now -->
            <div class="f-group">
                <label>Student Status</label>
                <select id="statusFilter" class="f-select" onchange="updateCharts()">
                    <option value="All">All Statuses</option>
                </select>
            </div>
            <button class="f-btn" onclick="resetFilters()" style="background: var(--text-muted); margin-left: auto;">Reset</button>
        </div>
    </div>

    <!-- ROW 1: Average Marks -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-12">
            <div class="card">
                <div id="mainChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- ROW 2: High Failure Rates -->
    <div class="row" id="rowFailRates">
        <div class="col-12">
            <div class="card" style="border-left: 5px solid #ef4444;">
                <div id="failChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- ROW 3: Lowest Scoring Subjects -->
    <div class="row" id="rowLowestScores">
        <div class="col-12">
            <div class="card" style="border-left: 5px solid #f59e0b;">
                <div id="lowestChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <!-- ROW: Top "Ace" Subjects -->
    <div class="row" id="rowAceSubjects">
        <div class="col-12">
            <div class="card" style="border-left: 5px solid #10b981;">
                <div id="aceChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- ROW 4: Subject Scoring Matrix -->
    <div class="row" id="rowScatter">
        <div class="col-12">
            <div class="card">
                <div id="scatterChart" style="height: 500px;"></div>
                <p style="text-align:center; font-size:12px; color:var(--text-muted); margin-top:10px;">
                    Bottom Right = <b>High Risk</b> (Low Marks + High Failure)
                </p>
            </div>
        </div>
    </div>
    
    <!-- ROW: Subject Performance Trend -->
    <div class="row" id="rowTrend">
        <div class="col-12">
            <div class="card">
                <!-- Centralized Heading -->
                <h3 style="padding:10px 0 0 20px; margin-bottom: 5px; text-align: center;">Subject Performance Trend (Across Semesters)</h3>
                <!-- Centralized Description -->
                <p style="padding:0 20px 10px 20px; font-size:13px; color:var(--text-muted); line-height:1.4; text-align: center;">
                    This line chart shows the average mark for each subject over sequential semesters within the selected cohort(s).
                    Look for lines that show a consistent upward or downward slope to identify long-term changes in subject difficulty or curriculum effectiveness.
                </p>
                <!-- Checkbox for Deselect All -->
                <div style="text-align:right; padding-right: 20px; margin-bottom: 10px;">
                    <input type="checkbox" id="toggleAllTrends" onchange="toggleTrendSeries(this.checked)">
                    <label for="toggleAllTrends" style="font-size:13px; color:var(--text-main); font-weight: 500; cursor: pointer;">Deselect All Lines</label>
                </div>
                <div id="trendChart" style="height: 400px;"></div>
            </div>
        </div>
    </div>

    <!-- REMOVED: rowKPIs container -->
    
    <!-- ROW: Grade Distribution Heatmap -->
    <div class="row" id="rowHeatmap">
        <div class="col-12">
            <div class="card">
                <!-- Centralized Heading -->
                <h3 style="padding:10px 0 0 20px; margin-bottom: 5px; text-align: center;">Grade Distribution Heatmap</h3>
                <!-- Centered Description -->
                <p style="padding:0 20px 15px 20px; font-size:13px; color:var(--text-muted); line-height:1.4; text-align: center;">
                    This heatmap shows the volume of students (color intensity) who received a specific Grade (X-axis) in each Subject (Y-axis).
                    Look for dark clusters on the right (high failure) or on the left (high excellence) to quickly spot grading anomalies or performance bottlenecks.
                </p>
                <div id="heatmapChart" style="height: 600px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 3. JAVASCRIPT LOGIC -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initFilters();
});

// Global variable to store the Trend Chart object
let trendHighchart = null;

function initFilters() {
    fetch('logic_subjects.php?action=get_filters')
        .then(r => r.json())
        .then(data => {
            const sub = document.getElementById('subjectFilter');
            const coh = document.getElementById('cohortFilter');
            const stat = document.getElementById('statusFilter');

            if(data.subjects) data.subjects.forEach(s => sub.add(new Option(s, s)));
            if(data.cohorts)  data.cohorts.forEach(c => coh.add(new Option(c, c)));
            if(data.statuses) data.statuses.forEach(s => stat.add(new Option(s, s)));
            
            updateSemesters();
        })
        .catch(err => console.error(err));
}

function updateSemesters() {
    const cohort = document.getElementById('cohortFilter').value;
    const semSelect = document.getElementById('semesterFilter');
    semSelect.length = 1; 

    fetch(`logic_subjects.php?action=get_semesters&cohort=${encodeURIComponent(cohort)}`)
        .then(r => r.json())
        .then(semesters => {
            semesters.forEach(s => semSelect.add(new Option("Semester " + s, s)));
            updateCharts();
        });
}

function updateCharts() {
    const sub = document.getElementById('subjectFilter').value;
    const coh = document.getElementById('cohortFilter').value;
    const sem = document.getElementById('semesterFilter').value;
    const stat = document.getElementById('statusFilter').value;

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#f1f5f9' : '#334155';
    const gridColor = isDark ? '#334155' : '#e2e8f0';

    document.getElementById('mainChart').style.opacity = '0.5';

    // Reset the toggle checkbox state on every chart update
    document.getElementById('toggleAllTrends').checked = false;

    fetch(`logic_subjects.php?action=get_data&subject=${sub}&cohort=${coh}&semester=${sem}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('mainChart').style.opacity = '1';
            
            if(data.error) {
                console.error(data.error);
                return;
            }

            const isOverview = data.main.type === 'overview';

            renderMainChart(data.main, isDark, textColor, gridColor);

            if (isOverview) {
                // Removed 'rowKPIs' from show list
                ['rowFailRates', 'rowLowestScores', 'rowScatter', 'rowAceSubjects', 'rowHeatmap', 'rowTrend'].forEach(id => document.getElementById(id).style.display = 'block');
                
                renderStackedChart('failChart', data.failChart, 'Highest Failure Rates (Top 15)', textColor, gridColor);
                renderStackedChart('lowestChart', data.lowestChart, 'Lowest Average Scores (Bottom 15)', textColor, gridColor);
                renderAceChart('aceChart', data.aceChart, 'Top "Ace" Subjects (Highest \'A\' Grade %)', textColor, gridColor);
                renderScatterChart('scatterChart', data.scatterChart, textColor, gridColor);
                trendHighchart = renderTrendChart(data.trendChart, textColor, gridColor);
                
                // renderKpiPieChart is removed
                
                renderHeatmapChart(data.heatmapChart, textColor, gridColor, isDark);
            } else {
                // Removed 'rowKPIs' from hide list
                ['rowFailRates', 'rowLowestScores', 'rowScatter', 'rowAceSubjects', 'rowHeatmap', 'rowTrend'].forEach(id => document.getElementById(id).style.display = 'none');
            }
        })
        .catch(err => console.error(err));
}

// --- NEW FUNCTIONALITY: Toggle all series visibility ---
function toggleTrendSeries(isChecked) {
    if (!trendHighchart) return;
    
    const isVisible = !isChecked; 

    trendHighchart.series.forEach(series => {
        series.setVisible(isVisible, false);
    });
    trendHighchart.redraw(); 
}
// --------------------------------------------------------


function renderMainChart(data, isDark, textColor, gridColor) {
    if (data.type === 'overview') {
        return Highcharts.chart('mainChart', {
            chart: { type: 'column', backgroundColor: 'transparent' },
            title: { text: data.title, style: { color: textColor } },
            xAxis: { categories: data.categories, labels: { style: { color: textColor } } },
            yAxis: { title: { text: 'Avg Marks', style: { color: textColor } }, max: 100, gridLineColor: gridColor, labels: { style: { color: textColor } } },
            legend: { enabled: false }, credits: { enabled: false },
            series: [{ name: 'Avg Marks', data: data.data, color: '#3b82f6' }]
        });
    } else {
        return Highcharts.chart('mainChart', {
            chart: { type: 'pie', backgroundColor: 'transparent' },
            title: { text: data.title, style: { color: textColor } },
            plotOptions: { pie: { dataLabels: { enabled: true, format: '<b>{point.name}</b>: {point.y}', style: { color: textColor, textOutline:'none' } }, borderColor: isDark?'#1e293b':'#fff' } },
            credits: { enabled: false },
            series: [{ name: 'Count', colorByPoint: true, data: data.data }]
        });
    }
}

function renderStackedChart(containerId, data, title, textColor, gridColor) {
    if(!data) return;
    return Highcharts.chart(containerId, {
        chart: { type: 'bar', backgroundColor: 'transparent' },
        title: { text: title, style: { color: textColor } },
        xAxis: { categories: data.categories, labels: { style: { color: textColor } } },
        yAxis: { min: 0, title: { text: 'Student Count', style: { color: textColor } }, gridLineColor: gridColor, labels: { style: { color: textColor } }, stacked: true },
        legend: { itemStyle: { color: textColor } },
        plotOptions: { series: { stacking: 'normal' } },
        credits: { enabled: false },
        series: [
            { name: 'Failed', data: data.failed, color: '#ef4444' }, 
            { name: 'Passed', data: data.passed, color: '#10b981' }
        ]
    });
}

function renderAceChart(containerId, data, title, textColor, gridColor) {
    if(!data || !data.data || data.data.length === 0) return;
    return Highcharts.chart(containerId, {
        chart: { type: 'bar', backgroundColor: 'transparent' },
        title: { text: title, style: { color: textColor } },
        xAxis: { categories: data.categories, labels: { style: { color: textColor } } },
        yAxis: { 
            title: { text: 'Percentage of \'A\' Grades', style: { color: textColor } }, 
            max: 100, 
            gridLineColor: gridColor, 
            labels: { style: { color: textColor } }
        },
        tooltip: {
            pointFormat: 'Percentage of \'A\'s: <b>{point.y}%</b><br/>Students: <b>{point.options.count}</b>'
        },
        legend: { enabled: false },
        credits: { enabled: false },
        series: [{ 
            name: 'Ace Rate', 
            data: data.data.map((value, index) => ({
                y: value,
                count: data.counts[index]
            })), 
            color: '#10b981' 
        }]
    });
}

function renderScatterChart(containerId, data, textColor, gridColor) {
    if(!data || !data.data) return;
    return Highcharts.chart(containerId, {
        chart: { type: 'bubble', zoomType: 'xy', backgroundColor: 'transparent' },
        title: { text: 'Subject Scoring Matrix', style: { color: textColor } },
        xAxis: { 
            title: { text: 'Avg Score (Performance)', style: { color: textColor } }, 
            gridLineColor: gridColor, labels: { style: { color: textColor } },
            plotLines: [{ color: 'red', dashStyle: 'dot', width: 2, value: 50, label: { text: 'Pass Mark', style: { color: 'red' } } }]
        },
        yAxis: { 
            title: { text: 'Failure Rate (%)', style: { color: textColor } }, 
            gridLineColor: gridColor, labels: { style: { color: textColor } },
            max: 100
        },
        tooltip: {
            useHTML: true,
            headerFormat: '<table>',
            pointFormat: '<tr><th colspan="2"><h3>{point.name}</h3></th></tr>' +
                '<tr><th>Avg Score:</th><td>{point.x}</td></tr>' +
                '<tr><th>Fail Rate:</th><td>{point.y}%</td></tr>' +
                '<tr><th>Students:</th><td>{point.z}</td></tr>',
            footerFormat: '</table>',
            followPointer: true
        },
        plotOptions: { bubble: { minSize: 10, maxSize: 50 } },
        credits: { enabled: false },
        series: [{
            name: 'Subjects',
            data: data.data,
            color: 'rgba(245, 158, 11, 0.7)'
        }]
    });
}

// renderKpiPieChart function is REMOVED


// Render Subject Performance Trend Chart
function renderTrendChart(data, textColor, gridColor) {
    if(!data || !data.series || data.series.length === 0) return;
    
    const semesters = data.semesters.map(s => parseInt(s.substring(1))); 
    const finalSeries = data.series.map(seriesItem => {
        const marksMap = new Map();
        seriesItem.data.forEach(point => marksMap.set(point.x, point.y));
        
        return {
            name: seriesItem.name,
            data: semesters.map(s => marksMap.get(s) ?? null) 
        };
    });


    return Highcharts.chart('trendChart', { 
        chart: { type: 'line', backgroundColor: 'transparent' },
        title: { text: 'Subject Average Mark Trend by Semester', style: { color: textColor } },
        xAxis: { 
            categories: data.semesters, 
            title: { text: 'Semester', style: { color: textColor } },
            labels: { style: { color: textColor } },
            gridLineColor: gridColor
        },
        yAxis: { 
            title: { text: 'Average Mark', style: { color: textColor } }, 
            max: 100,
            gridLineColor: gridColor, 
            labels: { style: { color: textColor } }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' marks'
        },
        legend: { itemStyle: { color: textColor } },
        credits: { enabled: false },
        plotOptions: {
            series: {
                marker: { enabled: true },
                events: {
                    legendItemClick: function () {
                        document.getElementById('toggleAllTrends').checked = false;
                        return true;
                    }
                }
            }
        },
        series: finalSeries
    });
}


function renderHeatmapChart(data, textColor, gridColor, isDark) {
    const container = document.getElementById('heatmapChart');
    
    if(!data || !data.data || data.subjects.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding:50px; color:${textColor};">No grade distribution data found for the current filters. (Check if your database has data for the selected filters)</div>`;
        container.style.height = '150px';
        return;
    }
    
    container.innerHTML = ''; 

    const subjectCount = data.subjects.length;
    const minHeight = 400; 
    const rowHeight = 25;  
    const dynamicHeight = Math.max(minHeight, subjectCount * rowHeight + 150); 

    container.style.height = dynamicHeight + 'px';

    return Highcharts.chart('heatmapChart', {
        chart: { type: 'heatmap', marginTop: 40, marginBottom: 80, backgroundColor: 'transparent' },
        title: { text: 'Subject Grade Distribution', style: { color: textColor } },
        xAxis: {
            categories: data.grades,
            title: { text: 'Grade Achieved', style: { color: textColor } },
            labels: { style: { color: textColor } },
            gridLineWidth: 0,
            lineWidth: 0
        },
        yAxis: {
            categories: data.subjects,
            title: { text: 'Subject Code', style: { color: textColor } },
            labels: { style: { color: textColor } },
            reversed: true, 
            gridLineWidth: 0,
            lineWidth: 0
        },
        colorAxis: {
            min: 0,
            max: data.max_count,
            minColor: isDark?'#1e293b':'#ffffff', 
            maxColor: '#ef4444', 
            labels: { style: { color: textColor } }
        },
        legend: {
            align: 'right',
            layout: 'vertical',
            margin: 0,
            verticalAlign: 'middle',
            y: 25,
            symbolHeight: 280,
            itemStyle: { color: textColor }
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.xAxis.categories[this.point.x] + '</b> grade in <br><b>' +
                    this.series.yAxis.categories[this.point.y] + '</b>: <br><b>' + this.point.value + '</b> students';
            }
        },
        credits: { enabled: false },
        series: [{
            name: 'Students',
            borderWidth: 1,
            borderColor: isDark?'#334155':'#fff',
            data: data.data,
            dataLabels: { enabled: true, color: isDark?'#fff':'#334155', style: { textOutline: 'none' } }
        }]
    });
}

function resetFilters() {
    document.getElementById('subjectFilter').value = "All";
    document.getElementById('cohortFilter').value = "All";
    document.getElementById('statusFilter').value = "All";
    updateSemesters();
}
</script>