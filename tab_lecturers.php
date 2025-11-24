<div id="lecturers" class="tab-section">
    <div class="charts-grid">
        
        <!-- TOP 5 PERFORMING MODULES -->
        <div class="card">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">🏆 Top 5 Highest Scoring Modules</h2>
            <div id="topModChart" class="chart-container" style="height:250px;"></div>
        </div>

        <!-- BOTTOM 5 MODULES (HARDEST) -->
        <div class="card">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">⚠️ Top 5 Toughest Modules (Low Avg)</h2>
            <div id="botModChart" class="chart-container" style="height:250px;"></div>
        </div>

        <!-- FULL MODULE LIST TABLE -->
        <div class="card" style="grid-column: 1/-1;">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">📋 Lecturer / Module Performance Matrix</h2>
            <div class="table-wrapper">
                <table style="width:100%; text-align:center;">
                    <thead>
                        <tr style="background:var(--bg-input);">
                            <th style="text-align:left;">Module Code</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Distinction Rate (>80%)</th>
                            <th>Student Count</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lecturerList as $mod): 
                            $status = "Stable"; $col = "var(--text-main)";
                            if($mod['avg'] < 55 || $mod['pass'] < 70) { $status = "⚠️ Needs Review"; $col = "#f87171"; }
                            if($mod['avg'] > 75) { $status = "🌟 High Performance"; $col = "#34d399"; }
                        ?>
                        <tr>
                            <td style="text-align:left; font-weight:bold; color:var(--accent);"><?php echo $mod['code']; ?></td>
                            <td><strong><?php echo $mod['avg']; ?></strong></td>
                            <td>
                                <div style="background:var(--bg-body); border-radius:4px; overflow:hidden; height:6px; width:50px; display:inline-block; vertical-align:middle; margin-right:5px;">
                                    <div style="width:<?php echo $mod['pass']; ?>%; height:100%; background:<?php echo ($mod['pass']<70)?'#f87171':'#10b981';?>;"></div>
                                </div>
                                <?php echo $mod['pass']; ?>%
                            </td>
                            <td><?php echo $mod['dist']; ?>%</td>
                            <td><?php echo $mod['students']; ?></td>
                            <td style="font-weight:bold; color:<?php echo $col; ?>;"><?php echo $status; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const lecData = <?php echo json_encode($lecturerList); ?>;
    
    // Prep Top 5 / Bottom 5
    const top5 = lecData.slice(0, 5);
    const bot5 = lecData.slice(-5).reverse(); // Reverse so lowest is at bottom of bar

    // 1. TOP 5 CHART
    Highcharts.chart('topModChart', {
        chart: { type: 'bar', backgroundColor: 'transparent' }, title: { text: '' },
        xAxis: { categories: top5.map(x => x.code) }, yAxis: { title: { text: 'Avg Score' }, max: 100 },
        legend: { enabled: false },
        series: [{ name: 'Score', data: top5.map(x => x.avg), color: '#34d399' }]
    });

    // 2. BOTTOM 5 CHART
    Highcharts.chart('botModChart', {
        chart: { type: 'bar', backgroundColor: 'transparent' }, title: { text: '' },
        xAxis: { categories: bot5.map(x => x.code) }, yAxis: { title: { text: 'Avg Score' }, max: 100 },
        legend: { enabled: false },
        series: [{ name: 'Score', data: bot5.map(x => x.avg), color: '#f87171' }]
    });
});
</script>