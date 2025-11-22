<style>
    .leader-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .leader-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
    .leader-rank { font-weight: bold; color: #cbd5e1; width: 25px; }
    .leader-score { font-weight: bold; color: #3b82f6; text-align: right; }
</style>

<div id="charts" class="tab-section <?php echo ($activeTab == 'charts') ? 'active' : ''; ?>">
    <div class="charts-grid">
        
        <!-- ROW 1: TREND -->
        <div class="card" style="grid-column: 1/-1;">
            <h2 style="color:#64748b; text-transform:uppercase;">📈 Trend History <?php echo $trendTitle; ?></h2>
            <div style="font-size:11px; color:#94a3b8; margin-bottom:10px;">*Performance timeline from 2020 to 2025</div>
            <div id="trendChart" class="chart-container" style="height:300px;"></div>
        </div>

        <!-- ROW 2: SNAPSHOT METRICS (Uses Dynamic Title) -->
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">📊 Grade Dist. <?php echo $dynamicTitle; ?></h2>
            <div id="distChart" class="chart-container"></div>
        </div>

        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">🏆 Top 5 <?php echo $dynamicTitle; ?></h2>
            <table class="leader-table">
                <?php foreach($top5 as $i => $s): ?>
                <tr>
                    <td class="leader-rank">#<?php echo $i+1; ?></td>
                    <td><strong><?php echo $s[0]." ".$s[1]; ?></strong><br><span style="color:#94a3b8"><?php echo $s[4]; ?></span></td>
                    <td class="leader-score"><?php echo $s[11]; ?>%</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($top5)) echo "<tr><td colspan='3'>No data found for this selection.</td></tr>"; ?>
            </table>
        </div>

        <!-- ROW 3: AVERAGES (Uses Dynamic Title) -->
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">🏢 Programme Avg <?php echo $dynamicTitle; ?></h2>
            <div id="columnChart3D" class="chart-container"></div>
        </div>
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">👥 Gender Split <?php echo $dynamicTitle; ?></h2>
            <div id="pieChart3D" class="chart-container"></div>
        </div>
        
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const cData = <?php echo json_encode($chartPayload); ?>;

    Highcharts.setOptions({ colors: ['#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#ef4444'] });

    // 1. TREND
    Highcharts.chart('trendChart', {
        chart: { type: 'areaspline' }, title: { text: '' }, xAxis: { categories: cData.trendY },
        yAxis: { title: { text: 'Score' } }, plotOptions: { areaspline: { fillOpacity: 0.2 } },
        series: [{ name: 'Business', data: cData.tBus }, { name: 'Computing', data: cData.tComp }]
    });

    // 2. GRADES
    Highcharts.chart('distChart', {
        chart: { type: 'column' }, title: { text: '' },
        xAxis: { categories: ['A','B','C','D','F'] }, yAxis: { title: { text: 'Students' } },
        legend: { enabled: false },
        plotOptions: { column: { colorByPoint: true } },
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
        series: [{ name: 'Students', data: cData.grades }]
    });

    // 3. 3D COL
    Highcharts.chart('columnChart3D', {
        chart: { type: 'column', options3d: { enabled: true, alpha: 10, beta: 25, depth: 70 } }, title: { text: '' },
        xAxis: { categories: ['Business', 'Computing'] }, plotOptions: { column: { depth: 50, colorByPoint: true } },
        series: [{ data: [cData.avgB, cData.avgC], showInLegend: false }]
    });

    // 4. PIE
    Highcharts.chart('pieChart3D', {
        chart: { type: 'pie', options3d: { enabled: true, alpha: 45 } }, title: { text: '' },
        plotOptions: { pie: { innerSize: 100, depth: 45 } },
        series: [{ name: 'Students', data: [['Male', cData.male], ['Female', cData.female]] }]
    });
});
</script>