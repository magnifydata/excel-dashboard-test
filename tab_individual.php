<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    /* --- STUDENT IDENTITY CARD STYLE --- */
    .student-id-card { display: flex; gap: 20px; align-items: flex-start; }
    .id-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); flex-shrink: 0; }
    .id-details { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .id-header { grid-column: 1 / -1; border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 5px; }
    .id-name { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0; line-height: 1.2; }
    .id-sub { font-size: 12px; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; }
    .id-stat-label { font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; display: block; }
    .id-stat-value { font-size: 15px; color: var(--text-main); font-weight: 600; }
    
    /* --- PROGRESS BAR --- */
    .prog-container { width: 100%; background-color: var(--border); border-radius: 10px; height: 12px; margin-top: 5px; overflow: hidden; }
    .prog-fill { height: 100%; background: linear-gradient(90deg, #3b82f6, #2563eb); width: 0%; transition: width 1s ease-in-out; }
    .prog-text { font-size: 11px; color: var(--text-muted); font-weight: 700; margin-top: 3px; text-align: right; }

    /* --- CHART CONTAINERS --- */
    .gpa-chart-container { height: 250px; width: 100%; }
    .rank-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; margin-bottom: 15px; }
    .rank-box { background: var(--bg-input); border: 1px solid var(--border); border-radius: 12px; padding: 10px; text-align: center; transition: 0.3s; }
    .rank-box.gold { background: linear-gradient(135deg, #fffbeb 0%, #fbbf24 100%); border: 1px solid #d97706; box-shadow: 0 4px 10px rgba(251, 191, 36, 0.3); }
    .rank-val { font-size: 20px; font-weight: 900; color: var(--text-main); line-height: 1; }
    .rank-box.gold .rank-val { color: #78350f; }
    .rank-box.gold .rank-label { color: #92400e; }
    
    /* --- EXISTING STYLES --- */
    .sub-msg { font-size: 10px; text-align: center; margin-top: 8px; padding: 4px; border-radius: 4px; font-weight: 700; min-height: 32px; display: flex; align-items: center; justify-content: center; line-height: 1.2; }
    .msg-good { background-color: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #059669; }
    .msg-bad  { background-color: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #b91c1c; }
    .sub-score-display { text-align: center; font-size: 11px; color: var(--text-muted); margin-bottom: 5px; font-family: 'Inter', sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .val-student { color: var(--accent); font-weight: 800; }
    .val-avg { color: var(--text-muted); font-weight: 800; }
    .class-badge { background: #334155; color: white; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-left: 10px; vertical-align: middle; }
    
    .profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
    .sub-subject-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
    .sub-card { background: var(--bg-input); border: 1px solid var(--border); border-radius: 8px; padding: 10px; }
    .search-row { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
    .student-select, .status-select { width: 100%; padding: 12px; font-size: 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-input); color: var(--text-main); }
    
    /* STATS ROW */
    .stat-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .stat-box { background: var(--bg-input); border: 1px solid var(--border); border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
    .stat-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .stat-info h4 { margin: 0; font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }
    .stat-info div { margin: 2px 0 0 0; font-size: 14px; font-weight: 700; color: var(--text-main); }
    
    /* Dynamic Color Overrides */
    .stat-box.colored h4 { color: rgba(255,255,255,0.8) !important; }
    .stat-box.colored div { color: white !important; }
    .stat-box.colored .stat-icon { background: rgba(255,255,255,0.2) !important; color: white !important; }
</style>

<div id="indiv" class="tab-section">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
            <h2 style="margin:0;">🔎 Find Student</h2>
            <div style="display:flex; gap:10px;">
                <button onclick="downloadPDF()" style="background:var(--accent); color:white; border:none; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold;">⬇️ PDF</button>
                <button onclick="window.print()" style="background:var(--bg-input); color:var(--text-main); border:1px solid var(--border); padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold;">🖨️ Print</button>
            </div>
        </div>
        <div class="search-row">
            <div style="flex:1; max-width: 200px;">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted); text-transform:uppercase;">Status</label>
                <select id="statusFilter" class="status-select">
                    <option value="">All</option>
                    <?php foreach($uniqueStatus as $s): ?><option value="<?php echo $s; ?>"><?php echo $s; ?></option><?php endforeach; ?>
                </select>
            </div>
            <div style="flex:2;">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted); text-transform:uppercase;">Student Name</label>
                <select id="studentSelect" class="student-select"><option value="">-- Select --</option></select>
            </div>
        </div>
    </div>
    
    <div id="studentProfile" style="display:none;">
        
        <!-- ROW 1: PROFILE & RADAR -->
        <div class="profile-grid">
            <!-- LEFT COL: PROFILE CARD -->
            <div class="card">
                <div class="student-id-card">
                    <div id="avatar" class="id-avatar">--</div>
                    <div class="id-details">
                        <div class="id-header">
                            <h1 id="pName" class="id-name">--</h1>
                            <span id="pId" class="id-sub">ID: --</span>
                            <span id="pClass" class="class-badge">--</span>
                        </div>
                        <div><span class="id-stat-label">Programme</span><div id="pProg" class="id-stat-value">--</div></div>
                        <div><span class="id-stat-label">Intake Year</span><div id="pYear" class="id-stat-value">--</div></div>
                        <div><span class="id-stat-label">Status</span><div id="pStatus" class="id-stat-value">--</div></div>
                        <div><span class="id-stat-label">Avg Score</span><div id="pAvg" class="id-stat-value" style="color:var(--accent)">--</div></div>
                    </div>
                </div>

                <hr style="border-top:1px solid var(--border); margin: 20px 0;">

                <div id="rankContainer" class="rank-grid">
                    <div id="boxRankProg" class="rank-box"><span class="rank-label">Prog Rank</span><div id="rankProgDisplay" class="rank-val">--</div></div>
                    <div id="boxRankYear" class="rank-box"><span class="rank-label">Year Rank</span><div id="rankYearDisplay" class="rank-val">--</div></div>
                </div>
                
                <!-- Credit Progress -->
                <h4 style="margin:10px 0 5px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase;">Credit Progression</h4>
                <div id="creditChart" style="height:80px;"></div>
                <div class="prog-container"><div id="creditFill" class="prog-fill"></div></div>
                <div id="creditText" class="prog-text">0% Completed</div>
            </div>

            <!-- RIGHT COL: RADAR & STATS -->
            <div style="display:flex; flex-direction:column; gap:24px;">
                
                <!-- QUICK STATS ROW -->
                <div class="stat-row">
                    <div id="cardBest" class="stat-box">
                        <div class="stat-icon" style="background:rgba(16,185,129,0.2); color:#10b981;">🏆</div>
                        <div class="stat-info"><h4>Best Subject</h4><div id="statBest">--</div></div>
                    </div>
                    <div id="cardWorst" class="stat-box">
                        <div class="stat-icon" style="background:rgba(239,68,68,0.2); color:#ef4444;">⚠️</div>
                        <div class="stat-info"><h4>Worst Subject</h4><div id="statWorst">--</div></div>
                    </div>
                    <div id="cardMom" class="stat-box">
                        <div class="stat-icon" style="background:rgba(59,130,246,0.2); color:#3b82f6;">📈</div>
                        <div class="stat-info"><h4>GPA Momentum</h4><div id="statMom">--</div></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; flex:1;">
                    <div class="card" style="margin:0;">
                        <h2>🎯 Performance Shape</h2>
                        <div id="studentRadar" style="height:250px;"></div>
                    </div>
                    <div class="card" style="margin:0;">
                        <h2>📝 Grade Portfolio</h2>
                        <div id="gradePieChart" style="height:250px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: COHORT DISTRIBUTION -->
        <div class="card">
            <h2>🔔 Cohort Distribution (Where do I sit?)</h2>
            <div id="bellCurveChart" style="height:280px;"></div>
        </div>

        <!-- ROW 3: ACADEMIC TRAJECTORY -->
        <div class="card">
            <h2>📈 Academic Trajectory (GPA vs CGPA)</h2>
            <div id="gpaChart" class="gpa-chart-container"></div>
        </div>

        <!-- ROW 4: AI FORECAST -->
        <div id="forecastCard" class="card" style="display:none; border: 2px solid #8b5cf6; background: rgba(139, 92, 246, 0.15);">
            <h2 style="color:#a78bfa; font-weight:800;">🔮 AI Performance Forecast</h2>
            <div style="font-size:14px; color:var(--text-main); margin-bottom:15px; font-weight:500;">Predicting future performance based on current trajectory (Linear Regression Model).</div>
            <div id="forecastChart" style="height:250px; width:100%;"></div>
        </div>

        <!-- ROW 5: VARIANCE ANALYSIS -->
        <div class="card">
            <h2>📊 Variance Analysis (Above/Below Avg)</h2>
            <div id="deviationChart" style="height:250px;"></div>
        </div>

        <!-- ROW 6: SUBJECT BREAKDOWN -->
        <div class="card">
            <h2>🔬 Full Subject Breakdown</h2>
            <div id="dynamicChartsContainer" class="sub-subject-grid"></div>
        </div>
    </div>
</div>

<script>
// PDF Function with Padding and Timestamps
async function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const content = document.getElementById('studentProfile');
    const name = document.getElementById('pName').innerText;
    
    // 1. Prepare UI for Capture
    const originalBg = content.style.backgroundColor;
    const originalPadding = content.style.padding;
    content.style.backgroundColor = "#ffffff"; 
    content.style.padding = "20px"; 
    
    // 2. High-Res Capture
    const canvas = await html2canvas(content, { scale: 2, useCORS: true });
    
    // 3. Restore UI
    content.style.backgroundColor = originalBg;
    content.style.padding = originalPadding;

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');
    
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();
    const margin = 15; // 15mm Margin
    const printWidth = pdfWidth - (margin * 2);
    const imgProps = pdf.getImageProperties(imgData);
    const imgHeight = (imgProps.height * printWidth) / imgProps.width;
    
    // 4. Add Header (Timestamp)
    const dateStr = new Date().toLocaleString();
    pdf.setFontSize(10);
    pdf.setTextColor(100);
    pdf.text("Student Performance Report", margin, margin);
    pdf.setFontSize(8);
    pdf.text(`Generated: ${dateStr}`, pdfWidth - margin, margin, { align: "right" });

    // 5. Add Content (Below Header)
    pdf.addImage(imgData, 'PNG', margin, margin + 10, printWidth, imgHeight);

    // 6. Add Footer
    pdf.setFontSize(8);
    pdf.setTextColor(150);
    pdf.text("Generated by MagnifyData SIS", pdfWidth / 2, pdfHeight - 10, { align: "center" });

    pdf.save(`Report_${name}.pdf`);
}

document.addEventListener("DOMContentLoaded", function() {
    const iData = {
        globalAvgs: <?php echo json_encode($globalSubjectAvgs); ?>,
        globalSemAvgs: <?php echo json_encode($globalSemesterAvgs); ?>,
        students: <?php echo json_encode($allStudentsData, JSON_HEX_APOS); ?>
    };

    // Pre-calculate Bell Curve
    const distributionData = new Array(21).fill(0); 
    const xCategories = []; for(let i=0; i<=100; i+=5) xCategories.push(i);
    if(iData.students) { 
        iData.students.forEach(s => { 
            const b = Math.round(parseFloat(s[11])/5); 
            if(b>=0 && b<=20) distributionData[b]++; 
        }); 
    }

    const sel = document.getElementById('studentSelect');
    const statFilter = document.getElementById('statusFilter');

    function populateStudents(filterStatus) {
        sel.innerHTML = '<option value="">-- Select --</option>';
        if (iData.students) {
            iData.students.forEach((s, i) => {
                if (filterStatus === "" || s[12] === filterStatus) {
                    const opt = document.createElement('option');
                    opt.value = i; opt.text = s[0] + " (" + s[4] + " - " + s[5] + ")"; 
                    sel.appendChild(opt);
                }
            });
        }
    }
    populateStudents("");

    statFilter.addEventListener('change', function() {
        populateStudents(this.value);
        document.getElementById('studentProfile').style.display = 'none';
    });

    sel.addEventListener('change', function() {
        const idx = this.value;
        const profile = document.getElementById('studentProfile');
        if(idx === "") { profile.style.display = 'none'; return; }
        profile.style.display = 'block';

        const s = iData.students[idx];
        const name = s[0]; const id = s[1]; const myProg = s[4]; const myYear = s[5]; const myScore = parseFloat(s[11]);
        const myStatus = s[12];

        const mySubjects = JSON.parse(s[13]); 
        const subjectNames = mySubjects.map(sub => sub.code);
        const subjectScores = mySubjects.map(sub => sub.mark);
        const subjectAvgs = mySubjects.map(sub => { return iData.globalAvgs[sub.code] ? iData.globalAvgs[sub.code] : 0; });

        const gpaData = s[14] ? JSON.parse(s[14]) : [];
        const gpaSeries = gpaData.map(x => x.gpa);
        const cgpaSeries = gpaData.map(x => x.cgpa);
        const semLabels = gpaData.map(x => 'Sem ' + x.sem);
        const classGpaSeries = gpaData.map(x => iData.globalSemAvgs[x.sem] ? iData.globalSemAvgs[x.sem].gpa : null);
        const classCgpaSeries = gpaData.map(x => iData.globalSemAvgs[x.sem] ? iData.globalSemAvgs[x.sem].cgpa : null);
        const creditsEarned = gpaData.length > 0 ? gpaData[gpaData.length-1].credits : 0;
        const totalCredits = 120; 

        // HIGHLIGHTS & MOMENTUM
        let maxScore = -1, minScore = 101, bestSub = "-", worstSub = "-";
        let grades = { 'A':0, 'B':0, 'C':0, 'D':0, 'F':0 };
        mySubjects.forEach(sub => {
            if(sub.mark > maxScore) { maxScore = sub.mark; bestSub = sub.code; }
            if(sub.mark < minScore) { minScore = sub.mark; worstSub = sub.code; }
            if(sub.mark >= 80) grades['A']++; else if(sub.mark >= 70) grades['B']++; else if(sub.mark >= 60) grades['C']++; else if(sub.mark >= 50) grades['D']++; else grades['F']++;
        });
        document.getElementById('statBest').innerHTML = `${bestSub} <span style="color:#10b981">(${maxScore}%)</span>`;
        document.getElementById('statWorst').innerHTML = `${worstSub} <span style="color:#ef4444">(${minScore}%)</span>`;
        
        let momText = "Stable"; let momPct = 0;
        if(gpaData.length >= 2) {
            const last = gpaData[gpaData.length-1].gpa;
            const prev = gpaData[gpaData.length-2].gpa;
            if(prev > 0) momPct = ((last - prev) / prev) * 100;
        }
        const cardMom = document.getElementById('cardMom');
        cardMom.className = 'stat-box'; cardMom.style = ""; 
        const setStyle = (bg, border) => { cardMom.style.backgroundColor = bg; cardMom.style.borderColor = border; cardMom.classList.add('colored'); };
        if (momPct > 0) { momText = `⬆️ +${momPct.toFixed(1)}%`; setStyle('#10b981', '#059669'); } 
        else if (momPct < -1) { momText = `⬇️ ${momPct.toFixed(1)}%`; setStyle('#ef4444', '#b91c1c'); } 
        else if (momPct < 0) { momText = `↘️ ${momPct.toFixed(1)}%`; setStyle('#f97316', '#ea580c'); } 
        else { momText = "➡️ Stable"; setStyle('#3b82f6', '#2563eb'); }
        document.getElementById('statMom').innerHTML = momText;

        // UI UPDATE
        document.getElementById('pName').innerText = name;
        document.getElementById('pId').innerText = "ID: " + id;
        document.getElementById('avatar').innerText = name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
        document.getElementById('pProg').innerText = myProg;
        document.getElementById('pYear').innerText = myYear;
        document.getElementById('pStatus').innerText = myStatus;
        document.getElementById('pAvg').innerText = s[11] + "%";

        const pct = Math.min(100, Math.round((creditsEarned / totalCredits) * 100));
        document.getElementById('creditFill').style.width = pct + "%";
        document.getElementById('creditText').innerText = `${creditsEarned} / ${totalCredits} Credits (${pct}%)`;

        let className="Fail", classColor="#ef4444";
        if(myScore>=80){className="First Class";classColor="#fbbf24";}
        else if(myScore>=70){className="2nd Upper";classColor="#38bdf8";}
        else if(myScore>=60){className="2nd Lower";classColor="#34d399";}
        else if(myScore>=50){className="Third Class";classColor="#94a3b8";}
        const badge = document.getElementById('pClass');
        badge.innerText = className; badge.style.backgroundColor = classColor; badge.style.color = (myScore>=80)?'#422006':'white';

        // RANKS
        const rankContainer = document.getElementById('rankContainer');
        if (myScore < 10) { rankContainer.style.display = 'none'; } 
        else {
            rankContainer.style.display = 'grid';
            const progPeers = iData.students.filter(st => st[4] === myProg); progPeers.sort((a,b)=>parseFloat(b[11])-parseFloat(a[11]));
            const progRank = progPeers.findIndex(st => st === s) + 1;
            document.getElementById('rankProgDisplay').innerText = `#${progRank} / ${progPeers.length}`;
            
            const yearPeers = iData.students.filter(st => st[5] === myYear); yearPeers.sort((a,b)=>parseFloat(b[11])-parseFloat(a[11]));
            const yearRank = yearPeers.findIndex(st => st === s) + 1;
            document.getElementById('rankYearDisplay').innerText = `#${yearRank} / ${yearPeers.length}`;
            
            const b1=document.getElementById('boxRankProg'); const b2=document.getElementById('boxRankYear');
            if(progRank<=3) b1.classList.add('gold'); else b1.classList.remove('gold');
            if(yearRank<=3) b2.classList.add('gold'); else b2.classList.remove('gold');
        }

        // --- CHARTS ---
        try {
            Highcharts.chart('studentRadar', {
                chart: { polar: true, type: 'line', backgroundColor: 'transparent' }, title: { text: '' },
                xAxis: { categories: subjectNames, tickmarkPlacement: 'on', lineWidth: 0 },
                yAxis: { gridLineInterpolation: 'polygon', min: 0, max: 100 },
                series: [{ name: 'Student', data: subjectScores, pointPlacement: 'on', color: '#38bdf8', type: 'area', fillOpacity: 0.3 }, { name: 'Class Avg', data: subjectAvgs, pointPlacement: 'on', color: '#94a3b8', dashStyle: 'ShortDot' }]
            });
        } catch(e){}

        try {
            Highcharts.chart('gradePieChart', {
                chart: { type: 'pie', backgroundColor: 'transparent' }, title: { text: '' },
                plotOptions: { pie: { innerSize: '60%', showInLegend: true, dataLabels: { enabled: true, format: '{point.y} ({point.percentage:.1f}%)' } } },
                legend: { layout: 'vertical', align: 'right', verticalAlign: 'middle', labelFormat: '{name} <span style="color:#000000; font-size:12px; font-weight:normal">{range}</span>', itemStyle: { color: '#000000', fontSize: '14px', fontWeight: 'bold' } },
                colors: ['#10b981', '#3b82f6', '#fbbf24', '#f97316', '#ef4444'],
                series: [{ name: 'Grades', data: [ { name: 'A', y: grades['A'], range: '80-100' }, { name: 'B', y: grades['B'], range: '70-79' }, { name: 'C', y: grades['C'], range: '60-69' }, { name: 'D', y: grades['D'], range: '50-59' }, { name: 'F', y: grades['F'], range: '<50' } ]}]
            });
        } catch(e){}

        try {
            Highcharts.chart('bellCurveChart', {
                chart: { type: 'spline' }, title: { text: '' }, xAxis: { categories: xCategories }, yAxis: { visible: false }, legend: { enabled: false },
                series: [{ name: 'Dist', data: distributionData, color: '#2dd4bf', type: 'areaspline', fillOpacity: 0.2 }], 
                xAxis: { categories: xCategories, plotLines: [{ color: '#38bdf8', width: 3, value: Math.round(myScore/5), zIndex:5, label:{ text:'YOU', rotation:0, y:-10, style:{color:'#38bdf8',fontWeight:'bold'} } }] }
            });
        } catch(e){}

        try {
            Highcharts.chart('gpaChart', {
                chart: { type: 'spline' }, title: { text: '' },
                xAxis: { categories: semLabels }, yAxis: { min: 0, max: 4.0, title: { text: 'GPA' } },
                series: [
                    { name: 'Student GPA', data: gpaSeries, color: '#38bdf8', marker: {symbol:'circle'}, lineWidth: 4 },
                    { name: 'Student CGPA', data: cgpaSeries, color: '#34d399', dashStyle: 'Solid', lineWidth: 4 },
                    { name: 'Class Avg GPA', data: classGpaSeries, color: '#94a3b8', dashStyle: 'ShortDot', marker: {enabled:false}, lineWidth: 2 },
                    { name: 'Class Avg CGPA', data: classCgpaSeries, color: '#64748b', dashStyle: 'ShortDot', marker: {enabled:false}, lineWidth: 2 }
                ]
            });
        } catch(e){}

        const forecastCard = document.getElementById('forecastCard');
        if (myStatus === 'Active' && gpaData.length >= 2 && gpaData.length < 7) {
            forecastCard.style.display = 'block';
            let n = gpaData.length; let sumX = 0, sumY = 0, sumXY = 0, sumXX = 0;
            gpaData.forEach((p, i) => { let x = i + 1; let y = p.gpa; sumX += x; sumY += y; sumXY += (x * y); sumXX += (x * x); });
            let slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
            let intercept = (sumY - slope * sumX) / n;
            let projLabels = [...semLabels]; let projData = new Array(n).fill(null); projData[n-1] = gpaData[n-1].gpa;
            for (let j = 1; j <= 3; j++) { let nextSem = n + j; if (nextSem > 8) break; let val = (slope * nextSem) + intercept; val = Math.max(0, Math.min(4.0, val)); projLabels.push('Sem ' + nextSem + ' (Est)'); projData.push(Number(val.toFixed(2))); }
            Highcharts.chart('forecastChart', {
                chart: { type: 'spline' }, title: { text: '' }, xAxis: { categories: projLabels }, yAxis: { min: 0, max: 4.0, title: { text: 'GPA' } },
                series: [{ name: 'Actual History', data: gpaSeries, color: '#34d399', lineWidth: 4, marker: {radius: 6} }, { name: 'AI Forecast', data: [...new Array(n-1).fill(null), ...projData.slice(n-1)], color: '#a78bfa', dashStyle: 'ShortDot', marker:{symbol:'diamond', radius: 6}, lineWidth: 4 }]
            });
        } else { forecastCard.style.display = 'none'; }

        try {
            Highcharts.chart('creditChart', {
                chart: { type: 'bar', height: 100 }, title: { text: '' }, xAxis: { categories: ['Credits'], visible: false }, yAxis: { min: 0, max: totalCredits, visible: false }, legend: { enabled: false },
                plotOptions: { series: { stacking: 'normal', pointWidth: 25 } },
                series: [{ name: 'Earned', data: [creditsEarned], color: '#3b82f6' }, { name: 'Remaining', data: [totalCredits - creditsEarned], color: '#10b981' }]
            });
        } catch(e){}

        try {
            const deviationData = subjectScores.map((score, i) => score - subjectAvgs[i]);
            Highcharts.chart('deviationChart', {
                chart: { type: 'bar' }, title: { text: '' }, xAxis: { categories: subjectNames }, yAxis: { title: { text: 'Deviation' }, plotLines: [{ value: 0, width: 2, color: '#94a3b8' }] },
                legend: { enabled: false }, plotOptions: { series: { colorByPoint: false } }, series: [{ name: 'Variance', data: deviationData, zones: [{ value: 0, color: '#fb923c' }, { color: '#a78bfa' }] }]
            });
        } catch(e){}

        const container = document.getElementById('dynamicChartsContainer');
        container.innerHTML = ''; 
        mySubjects.forEach((sub, i) => {
            const score = sub.mark; const avg = iData.globalAvgs[sub.code] || 0;
            const card = document.createElement('div'); card.className = 'sub-card';
            const title = document.createElement('h4'); title.innerText = sub.code; title.style.cssText = "margin:0 0 5px 0; font-size:12px; text-align:center; color:var(--text-main);";
            const header = document.createElement('div'); header.className = 'sub-score-display';
            header.innerHTML = `${name.split(' ')[0]}: <span class="val-student">${score}</span> <span style="color:#555">|</span> Avg: <span class="val-avg">${avg}</span>`;
            const chartDiv = document.createElement('div'); chartDiv.id = 'subChart_' + i; chartDiv.style.height = '130px';
            const msg = document.createElement('div'); msg.className = 'sub-msg';
            if(score >= avg) { msg.innerText = "Exceeds Avg"; msg.className += " msg-good"; } else { msg.innerText = "Below Avg"; msg.className += " msg-bad"; }
            card.appendChild(title); card.appendChild(header); card.appendChild(chartDiv); card.appendChild(msg);
            container.appendChild(card);
            Highcharts.chart('subChart_' + i, {
                chart: { type: 'column', backgroundColor: 'transparent', margin:[2,2,5,2] }, title: { text: '' }, xAxis: { categories: [''], visible: false }, yAxis: { min: 0, max: 100, visible: false },
                legend: { enabled: false }, plotOptions: { column: { grouping: false, shadow: false, borderWidth: 0 } },
                series: [{ name: 'Avg', color: '#334155', data: [avg], pointPadding: 0 }, { name: 'Student', color: score >= avg ? '#34d399' : '#f87171', data: [score], pointPadding: 0.2 }]
            });
        });
    });
});
</script>