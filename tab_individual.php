<style>
    /* --- MESSAGES --- */
    .sub-msg { font-size: 10px; text-align: center; margin-top: 8px; padding: 4px; border-radius: 4px; font-weight: 700; min-height: 32px; display: flex; align-items: center; justify-content: center; line-height: 1.2; }
    .msg-good { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .msg-bad  { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    /* --- HEADERS --- */
    .sub-score-display { text-align: center; font-size: 11px; color: #64748b; margin-bottom: 5px; font-family: 'Inter', sans-serif; }
    .val-student { color: #3b82f6; font-weight: 800; }
    .val-avg { color: #334155; font-weight: 800; }

    /* --- RANK & CLASS --- */
    .rank-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; margin-bottom: 15px; }
    .rank-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center; transition: 0.3s; }
    .rank-box.gold { background: linear-gradient(135deg, #fffbeb 0%, #fcd34d 100%); border: 1px solid #d97706; box-shadow: 0 4px 6px rgba(251, 191, 36, 0.2); }
    .rank-label { font-size: 10px; color: #64748b; font-weight: 800; text-transform: uppercase; display: block; margin-bottom: 4px; }
    .rank-val { font-size: 24px; font-weight: 900; color: #0f172a; line-height: 1; }
    .rank-total { font-size: 11px; color: #94a3b8; font-weight: 500; }

    .class-badge {
        background: #334155; color: white; 
        padding: 6px 12px; border-radius: 20px; 
        font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
        display: inline-block; margin-top: 5px;
    }
</style>

<div id="indiv" class="tab-section">
    <div class="card">
        <h2 style="color:#64748b; text-transform:uppercase;">🔎 Find Student</h2>
        <select id="studentSelect" class="student-select">
            <option value="">-- Select a Student --</option>
        </select>
    </div>
    
    <div id="studentProfile" style="display:none;">
        <div class="profile-grid">
            <!-- PROFILE CARD -->
            <div class="card">
                <div class="profile-stat"><label>Name</label><span id="pName"></span></div>
                <div class="profile-stat"><label>Programme</label><span id="pProg"></span></div>
                <div class="profile-stat"><label>Year</label><span id="pYear"></span></div>
                <hr style="border-top:1px solid #f1f5f9; margin: 15px 0;">
                <div class="profile-stat">
                    <label>Final Score</label>
                    <span id="pAvg" style="font-size:36px; color:#3b82f6;"></span>
                    <br>
                    <span id="pClass" class="class-badge">--</span> <!-- Classification Badge -->
                </div>

                <div id="rankContainer" class="rank-grid">
                    <div id="boxRankProg" class="rank-box">
                        <span class="rank-label">Programme Rank</span>
                        <div id="rankProgDisplay">--</div>
                    </div>
                    <div id="boxRankYear" class="rank-box">
                        <span class="rank-label">Year Cohort Rank</span>
                        <div id="rankYearDisplay">--</div>
                    </div>
                </div>
                <div id="gaugeChart" style="height:160px;"></div>
            </div>

            <!-- RADAR CHART -->
            <div class="card">
                <h2 style="color:#64748b; text-transform:uppercase;">🎯 Overall Shape</h2>
                <div id="studentRadar" style="height:350px;"></div>
            </div>
        </div>

        <!-- BELL CURVE (DISTRIBUTION) -->
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">🔔 Cohort Distribution (Where do I sit?)</h2>
            <div id="bellCurveChart" style="height:280px;"></div>
        </div>

        <!-- VARIANCE CHART -->
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">📊 Variance Analysis</h2>
            <div id="deviationChart" style="height:250px;"></div>
        </div>

        <!-- SUBJECT BREAKDOWN -->
        <div class="card">
            <h2 style="color:#64748b; text-transform:uppercase;">🔬 Subject Breakdown</h2>
            <div class="sub-subject-grid">
                <?php for($i=1; $i<=5; $i++): ?>
                <div class="sub-card">
                    <div id="subHeader<?php echo $i; ?>" class="sub-score-display"></div>
                    <div id="subC<?php echo $i; ?>" style="height:130px;"></div>
                    <div id="subMsg<?php echo $i; ?>" class="sub-msg"></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Data Setup
    const iData = {
        classAvg: <?php echo json_encode($subAvg); ?>,
        students: <?php echo json_encode($allRows, JSON_HEX_APOS); ?>
    };

    // --- PRE-CALCULATE BELL CURVE DATA (ALL STUDENTS) ---
    // We create 20 buckets (0-5, 5-10... 95-100)
    const distributionData = new Array(21).fill(0); // 0, 5, 10... 100 (21 steps)
    const xCategories = [];
    for(let i=0; i<=100; i+=5) xCategories.push(i);

    iData.students.forEach(s => {
        const score = parseFloat(s[11]);
        const bucket = Math.round(score / 5); // Find nearest bucket
        if(bucket >= 0 && bucket <= 20) distributionData[bucket]++;
    });

    // Populate Dropdown
    const sel = document.getElementById('studentSelect');
    if (iData.students && iData.students.length > 0) {
        iData.students.forEach((s, i) => {
            const opt = document.createElement('option');
            opt.value = i; 
            opt.text = s[0] + " " + s[1] + " (" + s[5] + ")"; 
            sel.appendChild(opt);
        });
    }

    // Change Event
    sel.addEventListener('change', function() {
        const idx = this.value;
        const profile = document.getElementById('studentProfile');
        if(idx === "") { profile.style.display = 'none'; return; }
        profile.style.display = 'block';

        const s = iData.students[idx];
        const firstName = s[0];
        const myProg = s[4];
        const myYear = s[5];
        const myScore = parseFloat(s[11]);

        // 1. UPDATE TEXT & CLASSIFICATION
        document.getElementById('pName').innerText = s[0] + " " + s[1];
        document.getElementById('pProg').innerText = myProg;
        document.getElementById('pYear').innerText = myYear;
        document.getElementById('pAvg').innerText = s[11] + "%";

        let className = "Fail";
        let classColor = "#ef4444";
        if(myScore >= 80) { className = "First Class Honours"; classColor="#d97706"; } // Gold
        else if(myScore >= 70) { className = "Second Class Upper"; classColor="#3b82f6"; } // Blue
        else if(myScore >= 60) { className = "Second Class Lower"; classColor="#10b981"; } // Green
        else if(myScore >= 50) { className = "Third Class"; classColor="#64748b"; } // Gray
        
        const badge = document.getElementById('pClass');
        badge.innerText = className;
        badge.style.backgroundColor = classColor;

        // 2. RANK LOGIC
        const rankContainer = document.getElementById('rankContainer');
        if (myScore < 50) {
            rankContainer.style.display = 'none';
        } else {
            rankContainer.style.display = 'grid';
            const progPeers = iData.students.filter(st => st[4] === myProg);
            progPeers.sort((a,b) => parseFloat(b[11]) - parseFloat(a[11]));
            const progRank = progPeers.findIndex(st => st === s) + 1;
            document.getElementById('rankProgDisplay').innerHTML = `<span class="rank-val">#${progRank}</span> <span class="rank-total">/ ${progPeers.length}</span>`;
            
            const yearPeers = iData.students.filter(st => st[5] === myYear);
            yearPeers.sort((a,b) => parseFloat(b[11]) - parseFloat(a[11]));
            const yearRank = yearPeers.findIndex(st => st === s) + 1;
            document.getElementById('rankYearDisplay').innerHTML = `<span class="rank-val">#${yearRank}</span> <span class="rank-total">/ ${yearPeers.length}</span>`;
            
            const boxProg = document.getElementById('boxRankProg');
            const boxYear = document.getElementById('boxRankYear');
            if(progRank<=3) boxProg.classList.add('gold'); else boxProg.classList.remove('gold');
            if(yearRank<=3) boxYear.classList.add('gold'); else boxYear.classList.remove('gold');
        }

        const scores = [ parseFloat(s[6]), parseFloat(s[7]), parseFloat(s[8]), parseFloat(s[9]), parseFloat(s[10]) ];

        // 3. BELL CURVE CHART (NEW)
        try {
            Highcharts.chart('bellCurveChart', {
                chart: { type: 'spline' }, title: { text: '' },
                xAxis: { categories: xCategories, title: { text: 'Score Range' } },
                yAxis: { title: { text: 'Number of Students' }, visible: false },
                legend: { enabled: false },
                tooltip: { enabled: false },
                plotOptions: { spline: { marker: { enabled: false }, enableMouseTracking: false } },
                series: [{
                    name: 'Distribution',
                    data: distributionData,
                    color: '#e2e8f0',
                    lineWidth: 2,
                    type: 'areaspline',
                    fillOpacity: 0.5
                }],
                // The Vertical Line representing the student
                xAxis: {
                    categories: xCategories,
                    plotLines: [{
                        color: '#3b82f6', // Blue Line
                        width: 3,
                        value: Math.round(myScore / 5), // Position on X Axis
                        zIndex: 5,
                        label: {
                            text: 'YOU',
                            rotation: 0,
                            y: -10,
                            style: { color: '#3b82f6', fontWeight: 'bold' }
                        }
                    }]
                }
            });
        } catch(e) {}

        // 4. RADAR
        try {
            Highcharts.chart('studentRadar', {
                chart: { polar: true, type: 'line' }, title: { text: '' },
                xAxis: { categories: ['Sub 1','Sub 2','Sub 3','Sub 4','Sub 5'], tickmarkPlacement: 'on', lineWidth: 0 },
                yAxis: { gridLineInterpolation: 'polygon', min: 0, max: 100 },
                series: [
                    { name: 'Student', data: scores, pointPlacement: 'on', color: '#3b82f6', type: 'area', fillOpacity: 0.2 },
                    { name: 'Class Avg', data: iData.classAvg, pointPlacement: 'on', color: '#334155', dashStyle: 'ShortDot' }
                ]
            });
        } catch(e){}

        // 5. GAUGE
        try {
            Highcharts.chart('gaugeChart', {
                chart: { type: 'solidgauge' }, title: null,
                pane: { center: ['50%', '85%'], size: '140%', startAngle: -90, endAngle: 90, background: { innerRadius: '60%', outerRadius: '100%', shape: 'arc' } },
                yAxis: { min: 0, max: 100, stops: [[0.1, '#ef4444'], [0.5, '#eab308'], [0.8, '#10b981']], lineWidth: 0, tickWidth: 0 },
                series: [{ data: [myScore], dataLabels: { format: '<div style="text-align:center"><span style="font-size:25px">{y}</span></div>' } }]
            });
        } catch(e){}

        // 6. VARIANCE
        try {
            const deviationData = scores.map((score, i) => score - iData.classAvg[i]);
            Highcharts.chart('deviationChart', {
                chart: { type: 'bar' }, title: { text: '' },
                xAxis: { categories: ['Sub 1','Sub 2','Sub 3','Sub 4','Sub 5'] },
                yAxis: { title: { text: 'Deviation' }, plotLines: [{ value: 0, width: 2, color: '#333' }] },
                legend: { enabled: false }, plotOptions: { series: { colorByPoint: false } },
                series: [{ name: 'Variance', data: deviationData, zones: [{ value: 0, color: '#f97316' }, { color: '#8b5cf6' }] }]
            });
        } catch(e){}

        // 7. MINI CHARTS
        try {
            for(let i=0; i<5; i++) {
                const studentScore = scores[i];
                const classAvg = iData.classAvg[i];
                document.getElementById('subHeader'+(i+1)).innerHTML = `${firstName}: <span class="val-student">${studentScore}</span> <span style="color:#ccc">|</span> Avg: <span class="val-avg">${classAvg}</span>`;
                const msgBox = document.getElementById('subMsg'+(i+1));
                if(studentScore >= classAvg) { msgBox.innerText = "Exceeds Avg"; msgBox.className = "sub-msg msg-good"; } 
                else { msgBox.innerText = "Below Avg"; msgBox.className = "sub-msg msg-bad"; }

                Highcharts.chart('subC'+(i+1), {
                    chart: { type: 'column', backgroundColor: 'transparent', margin:[2,2,5,2] },
                    title: { text: 'Sub '+(i+1), style:{fontSize:'10px'} },
                    xAxis: { categories: [''], visible: false }, yAxis: { min: 0, max: 100, visible: false },
                    legend: { enabled: false }, plotOptions: { column: { grouping: false, shadow: false, borderWidth: 0 } },
                    series: [
                        { name: 'Avg', color: '#e2e8f0', data: [classAvg], pointPadding: 0 },
                        { name: 'Student', color: studentScore >= classAvg ? '#10b981' : '#ef4444', data: [studentScore], pointPadding: 0.2 }
                    ]
                });
            }
        } catch(e){}
    });
});
</script>