<?php require 'logic.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS Dashboard</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background-color: #f1f5f9; margin: 0; color: #334155; height: 100vh; overflow: hidden; }
        .dashboard-layout { display: flex; height: 100%; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 20px; display: flex; flex-direction: column; z-index: 10; }
        .brand { font-size: 20px; font-weight: 700; margin-bottom: 40px; color: #38bdf8; }
        .nav-btn { background: transparent; border: none; color: #94a3b8; text-align: left; padding: 12px; cursor: pointer; font-size: 15px; border-radius: 8px; margin-bottom: 5px; width: 100%; font-weight: 500; }
        .nav-btn:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .nav-btn.active { background: #3b82f6; color: white; font-weight: 600; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; display:flex; flex-direction:column; }
        
        .global-filter-bar { background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; flex-shrink:0; transition: 0.3s; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; }
        .f-group label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; }
        .f-select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; min-width: 140px; color: #334155; }
        .f-btn { background: #3b82f6; color: white; border: none; padding: 9px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }
        
        .tab-section { display: none; }
        .tab-section.active { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from {opacity: 0; transform: translateY(5px);} to {opacity: 1; transform: translateY(0);} }
        
        .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px; }
        .chart-container { height: 350px; width: 100%; }
        
        /* Helpers */
        .student-select { width: 100%; padding: 12px; font-size: 16px; border-radius: 8px; border: 2px solid #e2e8f0; margin-bottom: 20px; }
        .profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        .sub-subject-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
        .sub-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; }
        .table-wrapper { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; font-size: 14px; }
        thead { background: #f8fafc; }
        th, td { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .badge { padding: 4px 12px; border-radius: 30px; font-size: 12px; font-weight: 700; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        
        .profile-stat { margin-bottom: 15px; }
        .profile-stat label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; }
        .profile-stat span { font-size: 18px; font-weight: 600; color: #0f172a; }
        .sub-msg { font-size: 10px; text-align: center; margin-top: 8px; padding: 4px; border-radius: 4px; font-weight: 700; }
        .msg-good { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .msg-bad { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="brand">MagnifyData 🚀</div>
        <button id="btn-list" class="nav-btn <?php echo ($activeTab=='list')?'active':''; ?>" onclick="switchTab('list')">📄 Student List</button>
        <button id="btn-charts" class="nav-btn <?php echo ($activeTab=='charts')?'active':''; ?>" onclick="switchTab('charts')">📊 Main Indicators</button>
        <button id="btn-indiv" class="nav-btn <?php echo ($activeTab=='indiv')?'active':''; ?>" onclick="switchTab('indiv')">👤 Individual Performance</button>
    </nav>

    <main class="main-content">
        <?php if($error): ?><div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div><?php endif; ?>

        <!-- GLOBAL FILTER BAR (Added ID for JS control) -->
        <div id="globalFilters" class="global-filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="active_tab" id="activeTabInput" value="<?php echo $activeTab; ?>">
                <div class="f-group">
                    <label>Programme</label>
                    <select name="prog" class="f-select">
                        <option value="">All Programmes</option>
                        <option value="Computing" <?php if($filterProg=='Computing') echo 'selected';?>>Computing</option>
                        <option value="Business" <?php if($filterProg=='Business') echo 'selected';?>>Business</option>
                    </select>
                </div>
                <div class="f-group">
                    <label>Year</label>
                    <select name="year" class="f-select">
                        <option value="">All Years</option>
                        <?php foreach($uniqueYears as $y): ?>
                            <option value="<?php echo $y; ?>" <?php if($filterYear==$y) echo 'selected';?>><?php echo $y; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="f-group">
                    <label>Sort (List Only)</label>
                    <select name="sort" class="f-select">
                        <option value="asc" <?php if($sortOrder=='asc') echo 'selected';?>>Name A-Z</option>
                        <option value="desc" <?php if($sortOrder=='desc') echo 'selected';?>>Name Z-A</option>
                    </select>
                </div>
                <button type="submit" class="f-btn">Apply Global Filters</button>
                <a href="index.php?active_tab=<?php echo $activeTab; ?>" style="align-self:center; color:#64748b; font-size:13px; text-decoration:none;">Reset</a>
            </form>
        </div>

        <!-- TAB 1: LIST -->
        <div id="list" class="tab-section <?php echo ($activeTab=='list')?'active':''; ?>">
            <div class="card">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <h3>Student Records</h3>
                    <span class="badge bg-green" style="align-self:center;"><?php echo count($tableRows); ?> Found</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><?php foreach($headers as $h): ?><th><?php echo htmlspecialchars($h); ?></th><?php endforeach; ?></tr></thead>
                        <tbody>
                            <?php foreach($tableRows as $row): ?>
                            <tr><?php foreach($row as $idx => $cell): $isAvg=($idx==count($row)-1); $isYear=($idx==5); if($isAvg){ $cls=($cell>=80)?'badge bg-green':(($cell<60)?'badge bg-red':''); echo "<td><span class='$cls'>$cell</span></td>"; } elseif($isYear){ echo "<td><strong>$cell</strong></td>"; } else { echo "<td>".htmlspecialchars($cell)."</td>"; } endforeach; ?></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2 & 3 -->
        <?php include 'tab_indicators.php'; ?>
        <?php include 'tab_individual.php'; ?>
        
        <!-- LOAD TAB LOGIC -->
        <script>
            // Initial Load Check
            if("<?php echo $activeTab; ?>" === "indiv") {
                document.getElementById('indiv').classList.add('active');
                document.getElementById('globalFilters').style.display = 'none'; // Hide filters on load
            }
        </script>

    </main>
</div>

<script>
function switchTab(tabName) {
    // 1. Reset Tabs & Buttons
    document.querySelectorAll('.tab-section').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
    
    const tab = document.getElementById(tabName);
    const btn = document.getElementById('btn-'+tabName);
    const filters = document.getElementById('globalFilters');

    if(tab && btn) {
        tab.classList.add('active');
        btn.classList.add('active');
        
        // 2. TOGGLE GLOBAL FILTER VISIBILITY
        if(tabName === 'indiv') {
            filters.style.display = 'none'; // Hide for Individual Tab
        } else {
            filters.style.display = 'block'; // Show for others
        }

        // 3. Update Hidden Input
        document.getElementById('activeTabInput').value = tabName;

        // 4. Resize Charts
        if(window.Highcharts) {
            setTimeout(() => { Highcharts.charts.forEach(c => { if(c) c.reflow(); }); }, 100);
        }
    }
}
</script>

</body>
</html>