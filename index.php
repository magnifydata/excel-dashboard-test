<?php 
// 1. Load Common Logic (DB Connection & Dropdowns)
require 'logic_common.php'; 

// 2. Load Specific Logic based on Active Tab
switch($activeTab) {
    case 'list':      require 'logic_list.php'; break;
    // REMOVED: case 'charts': require 'logic_indicators.php'; break; 
    case 'subs':      require 'logic_subjects.php'; break;
    case 'lecturers': require 'logic_subjects.php'; break; 
    case 'indiv':     require 'logic_individual.php'; break;
    case 'ai':        /* AI Logic usually happens via AJAX/API */ break; 
    default:          require 'logic_list.php';
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS Dashboard</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --bg-body: #f1f5f9; --bg-card: #ffffff; --bg-input: #ffffff;
            --text-main: #334155; --text-muted: #64748b; --border: #e2e8f0;
            --sidebar-bg: #0f172a; --accent: #3b82f6; --bg-red: #fee2e2;
        }
        [data-theme="dark"] {
            --bg-body: #0f172a; --bg-card: #1e293b; --bg-input: #334155;
            --text-main: #f1f5f9; --text-muted: #94a3b8; --border: #334155;
            --sidebar-bg: #020617; --accent: #38bdf8; --bg-red: rgba(239, 68, 68, 0.2);
        }

        * { box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background-color: var(--bg-body); margin: 0; color: var(--text-main); height: 100vh; overflow: hidden; transition: background 0.3s, color 0.3s; }
        
        .dashboard-layout { display: flex; height: 100%; }
        .sidebar { width: 260px; background: var(--sidebar-bg); padding: 20px; display: flex; flex-direction: column; z-index: 10; transition: 0.3s; border-right: 1px solid var(--border); }
        .brand { font-size: 20px; font-weight: 700; margin-bottom: 40px; color: var(--accent); }
        
        /* Navigation Links */
        .nav-btn { 
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: #94a3b8; 
            padding: 12px; margin-bottom: 5px; border-radius: 8px; 
            font-size: 15px; font-weight: 500; transition: 0.2s; 
        }
        .nav-btn:hover { background-color: rgba(255,255,255,0.1); color: white; }
        .nav-btn.active { background: var(--accent); color: white; font-weight: 600; }
        
        .theme-toggle { margin-top: auto; background: rgba(255,255,255,0.1); color: #94a3b8; border: none; padding: 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .theme-toggle:hover { background: rgba(255,255,255,0.2); color: white; }

        .main-content { flex: 1; padding: 30px; overflow-y: auto; display:flex; flex-direction:column; }
        
        /* Global Filter Bar */
        .global-filter-bar { background: var(--bg-card); padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border); flex-shrink:0; }
        .filter-form { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
        .f-group { flex: 1; min-width: 120px; }
        .f-group label { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; display: block; }
        .f-select { width: 100%; padding: 8px 12px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 6px; font-size: 14px; color: var(--text-main); }
        .f-btn { background: var(--accent); color: white; border: none; padding: 9px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }

        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; }
        h2 { margin-top: 0; font-size: 16px; color: var(--text-muted); margin-bottom: 15px; text-transform: uppercase; font-weight: 700; }
        
        .table-wrapper { overflow-x: auto; border: 1px solid var(--border); border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; font-size: 14px; }
        thead { background: var(--bg-body); }
        th { text-align: left; padding: 14px 18px; border-bottom: 1px solid var(--border); color: var(--text-muted); }
        td { padding: 14px 18px; border-bottom: 1px solid var(--border); color: var(--text-main); }
        
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px; }
        .chart-container { height: 350px; width: 100%; }
        
        .tab-section { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from {opacity: 0; transform: translateY(5px);} to {opacity: 1; transform: translateY(0);} }
        
        .badge { padding: 4px 12px; border-radius: 30px; font-size: 12px; font-weight: 700; }
        .bg-green { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .bg-red { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .bg-blue { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="brand">MagnifyData 🚀</div>
        
        <!-- NAVIGATION LINKS (REMOVED MAIN INDICATORS) -->
        <a href="?active_tab=list" class="nav-btn <?php echo ($activeTab=='list')?'active':''; ?>"><span>📄</span> Student List</a>
        <a href="?active_tab=subs" class="nav-btn <?php echo ($activeTab=='subs')?'active':''; ?>"><span>📚</span> Subject Performance</a>
        <a href="?active_tab=lecturers" class="nav-btn <?php echo ($activeTab=='lecturers')?'active':''; ?>"><span>🎓</span> Lecturer Perf.</a>
        <a href="?active_tab=indiv" class="nav-btn <?php echo ($activeTab=='indiv')?'active':''; ?>"><span>👤</span> Individual Perf.</a>
        <a href="?active_tab=ai" class="nav-btn <?php echo ($activeTab=='ai')?'active':''; ?>"><span>✨</span> Talk with AI</a>
        
        <button class="theme-toggle" onclick="toggleTheme()">
            <span id="themeIcon">🌙</span> <span id="themeText">Dark Mode</span>
        </button>
    </nav>

    <main class="main-content">
        <?php if($error): ?><div style="background:var(--bg-red); padding:15px; border-radius:8px; margin-bottom:20px;"><?php echo $error; ?></div><?php endif; ?>

        <!-- GLOBAL FILTERS (Hidden for Indiv/AI) -->
        <?php if($activeTab != 'indiv' && $activeTab != 'ai'): ?>
        <div id="globalFilters" class="global-filter-bar">
            <form method="GET" class="filter-form">
                <input type="hidden" name="active_tab" value="<?php echo $activeTab; ?>">
                
                <div class="f-group"><label>Category</label><select name="cat" class="f-select"><option value="">All</option><?php foreach($uniqueProgs as $c) echo "<option value='$c' ".($filterProg==$c?'selected':'').">$c</option>"; ?></select></div>
                <div class="f-group"><label>Code</label><select name="code" class="f-select"><option value="">All</option><?php foreach($uniqueCodes as $c) echo "<option value='$c' ".($filterCode==$c?'selected':'').">$c</option>"; ?></select></div>
                <div class="f-group"><label>Status</label><select name="status" class="f-select"><option value="">All</option><?php foreach($uniqueStatus as $s) echo "<option value='$s' ".($filterStatus==$s?'selected':'').">$s</option>"; ?></select></div>
                <div class="f-group"><label>Year</label><select name="year" class="f-select"><option value="">All</option><?php foreach($uniqueYears as $y) echo "<option value='$y' ".($filterYear==$y?'selected':'').">$y</option>"; ?></select></div>
                <div class="f-group"><label>Sort</label><select name="sort" class="f-select"><option value="asc" <?php if($sortOrder=='asc') echo 'selected';?>>A-Z</option><option value="desc" <?php if($sortOrder=='desc') echo 'selected';?>>Z-A</option></select></div>
                <button type="submit" class="f-btn">Apply</button>
                <a href="index.php?active_tab=<?php echo $activeTab; ?>" style="align-self:center; color:var(--text-muted); font-size:13px; text-decoration:none; margin-left:5px;">Reset</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- TAB CONTENT LOADER -->
        <?php 
            switch($activeTab) {
                case 'list': 
                    ?>
                    <div id="list" class="tab-section active">
                        <div class="card">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                <h3 style="margin:0; color:var(--text-main);">Student Records</h3>
                                <span class="badge bg-green" style="align-self:center;"><?php echo count($tableRows); ?> Found</span>
                            </div>
                            <div class="table-wrapper">
                                <table>
                                    <thead><tr><th>Name</th><th>ID</th><th>Code</th><th>Cat</th><th>Year</th><th>Status</th><th>Avg</th></tr></thead>
                                    <tbody>
                                        <?php foreach($tableRows as $row): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row[0]); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row[1]); ?></td>
                                            <td><span class="badge bg-blue"><?php echo htmlspecialchars($row[2]); ?></span></td>
                                            <td><?php echo htmlspecialchars($row[4]); ?></td>
                                            <td><?php echo htmlspecialchars($row[5]); ?></td>
                                            <td><?php echo htmlspecialchars($row[12]); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row[11]); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php
                    break;
                
                // REMOVED MAIN INDICATORS CASE
                case 'subs':      if(file_exists('tab_subjects.php')) include 'tab_subjects.php'; break;
                case 'lecturers': if(file_exists('tab_lecturers.php')) include 'tab_lecturers.php'; break;
                case 'indiv':     if(file_exists('tab_individual.php')) include 'tab_individual.php'; break;
                case 'ai':        if(file_exists('tab_ai.php')) include 'tab_ai.php'; break;
            }
        ?>

        <script>
            // Handle Theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeUI(savedTheme);

            function toggleTheme() {
                const current = document.documentElement.getAttribute('data-theme');
                const newTheme = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeUI(newTheme);
                if(window.Highcharts) { location.reload(); } 
            }

            function updateThemeUI(theme) {
                const icon = document.getElementById('themeIcon');
                const text = document.getElementById('themeText');
                if(theme === 'dark') { icon.innerText = '☀️'; text.innerText = 'Light Mode'; }
                else { icon.innerText = '🌙'; text.innerText = 'Dark Mode'; }
            }
            
            Highcharts.setOptions({
                chart: { backgroundColor: 'transparent', style: { fontFamily: 'Inter' } },
                title: { style: { color: '#f1f5f9' } },
                legend: { itemStyle: { color: '#94a3b8' }, itemHoverStyle: { color: '#fff' } },
                xAxis: { gridLineColor: '#334155', labels: { style: { color: '#94a3b8' } }, lineColor: '#334155', tickColor: '#334155' },
                yAxis: { gridLineColor: '#334155', labels: { style: { color: '#94a3b8' } }, title: { style: { color: '#94a3b8' } } }
            });
        </script>

    </main>
</div>

</body>
</html>