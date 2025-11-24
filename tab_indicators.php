<style>.chart-container { width: 100%; height: 300px; }</style>

<div id="charts" class="tab-section <?php echo ($activeTab == 'charts') ? 'active' : ''; ?>">
    <div class="charts-grid">
        <!-- Trend -->
        <div class="card" style="grid-column: 1/-1;">
            <h2>📈 Performance Trend</h2>
            <div id="trendChart" class="chart-container"></div>
        </div>
        <!-- Grades -->
        <div class="card">
            <h2>📊 Grade Distribution</h2>
            <div id="distChart" class="chart-container"></div>
        </div>
        <!-- Leaderboard -->
        <div class="card">
            <h2>🏆 Top 5 Students</h2>
            <table style="width:100%; font-size:13px;">
                <?php if(!empty($top5)): foreach($top5 as $i=>$s): ?>
                <tr>
                    <td style="color:#94a3b8; font-weight:bold;">#<?php echo $i+1; ?></td>
                    <td><strong><?php echo $s['name']; ?></strong></td>
                    <td style="text-align:right; color:#3b82f6; font-weight:bold;"><?php echo round($s['score'],1); ?>%</td>
                </tr>
                <?php endforeach; else: echo "<tr><td>No data.</td></tr>"; endif; ?>
            </table>
        </div>
        <!-- Averages -->
        <div class="card">
            <h2>🏢 Programme Averages</h2>
            <div id="colChart" class="chart-container"></div>
        </div>
        <!-- Gender -->
        <div class="card">
            <h2>👥 Gender Split</h2>
            <div id="pieChart" class="chart-container"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const data = <?php echo json_encode($chartPayload); ?>;
    if(!data) return;

    Highcharts.setOptions({ colors: ['#3b82f6', '#ec4899', '#10b981', '#f59e0b'] });

    Highcharts.chart('trendChart', {
        chart: { type: 'spline', backgroundColor: 'transparent' }, title: { text: '' },
        xAxis: { categories: data.trendY }, yAxis: { title: { text: 'Score' } },
        series: data.trendSeries
    });

    Highcharts.chart('distChart', {
        chart: { type: 'column', backgroundColor: 'transparent' }, title: { text: '' },
        xAxis: { categories: ['A','B','C','D','F'] }, legend: { enabled: false },
        series: [{ name: 'Count', data: data.grades, colorByPoint: true }]
    });

    Highcharts.chart('colChart', {
        chart: { type: 'bar', backgroundColor: 'transparent' }, title: { text: '' },
        xAxis: { categories: data.progLabels }, legend: { enabled: false },
        series: [{ name: 'Avg', data: data.progValues }]
    });

    Highcharts.chart('pieChart', {
        chart: { type: 'pie', backgroundColor: 'transparent' }, title: { text: '' },
        series: [{ name: 'Count', data: [['Male', data.male], ['Female', data.female]] }]
    });
});
</script>