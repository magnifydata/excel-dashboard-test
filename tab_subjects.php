<div id="subjects" class="tab-section">
    <div class="charts-grid">
        
        <!-- TOP ROW: COMPARISONS -->
        <div class="card">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">📊 Difficulty Ranking (Avg Score)</h2>
            <div id="diffChart" class="chart-container"></div>
        </div>
        <div class="card">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">✅ Pass Rate % (Watchlist)</h2>
            <div id="passChart" class="chart-container"></div>
        </div>

        <!-- BOTTOM ROW: DETAILED HEATMAP TABLE -->
        <div class="card" style="grid-column: 1/-1;">
            <h2 style="color:var(--text-muted); text-transform:uppercase;">🔬 Subject Performance Matrix</h2>
            <div class="table-wrapper">
                <table style="width:100%; text-align:center;">
                    <thead>
                        <tr style="background:var(--bg-input);">
                            <th style="text-align:left; padding:15px;">Subject</th>
                            <th>Average Score</th>
                            <th>Pass Rate</th>
                            <th>Highest Score</th>
                            <th>Lowest Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i=0; $i<5; $i++): 
                            $sAvg = $subAvg[$i];
                            $sPass = $subPassRate[$i];
                            $sHigh = $subjectDetails[$i]['highest'];
                            $sLow = $subjectDetails[$i]['lowest'];
                            
                            // Status Logic
                            $status = "Stable"; $color="var(--text-main)";
                            if($sAvg < 60 || $sPass < 70) { $status = "⚠️ At Risk"; $color="#f87171"; }
                            if($sAvg > 75 && $sPass > 90) { $status = "🌟 Strong"; $color="#34d399"; }
                        ?>
                        <tr>
                            <td style="text-align:left; font-weight:bold; color:var(--accent);">Subject <?php echo $i+1; ?></td>
                            <td><?php echo $sAvg; ?></td>
                            <td>
                                <div style="background:var(--bg-body); border-radius:4px; overflow:hidden; height:6px; width:60px; display:inline-block; vertical-align:middle; margin-right:5px;">
                                    <div style="width:<?php echo $sPass; ?>%; height:100%; background:<?php echo ($sPass<70)?'#f87171':'#34d399';?>;"></div>
                                </div>
                                <?php echo $sPass; ?>%
                            </td>
                            <td style="color:#34d399;"><?php echo $sHigh; ?></td>
                            <td style="color:#f87171;"><?php echo $sLow; ?></td>
                            <td style="font-weight:bold; color:<?php echo $color; ?>;"><?php echo $status; ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sData = {
        avgs: <?php echo json_encode($subAvg); ?>,
        pass: <?php echo json_encode($subPassRate); ?>,
        cats: ['Sub 1', 'Sub 2', 'Sub 3', 'Sub 4', 'Sub 5']
    };

    // 1. DIFFICULTY CHART (Sorted Bar)
    // Combine data to sort it
    let combined = sData.cats.map((name, i) => ({ name, y: sData.avgs[i] }));
    combined.sort((a, b) => a.y - b.y); // Sort Low to High (Hardest first)

    Highcharts.chart('diffChart', {
        chart: { type: 'bar' }, title: { text: '' },
        xAxis: { categories: combined.map(x => x.name), title: { text: null } },
        yAxis: { min: 0, max: 100, title: { text: 'Average Score' } },
        legend: { enabled: false },
        plotOptions: { series: { colorByPoint: true } },
        colors: ['#f87171', '#fb923c', '#fbbf24', '#34d399', '#3b82f6'], // Red to Blue
        series: [{ name: 'Avg Score', data: combined.map(x => x.y) }]
    });

    // 2. PASS RATE CHART
    Highcharts.chart('passChart', {
        chart: { type: 'column' }, title: { text: '' },
        xAxis: { categories: sData.cats },
        yAxis: { min: 0, max: 100, title: { text: 'Pass Rate %' }, plotLines: [{ value: 70, color: 'red', width: 2, dashStyle: 'shortdash', label: { text: 'Warning Threshold' } }] },
        legend: { enabled: false },
        series: [{ name: 'Pass Rate', data: sData.pass, color: '#10b981' }]
    });
});
</script>