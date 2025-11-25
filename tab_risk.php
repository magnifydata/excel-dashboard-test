<!-- 
    File: tab_risk.php
    Purpose: Frontend for the High Risk Students Tab (Added Batch Download Button).
-->
<div id="risk" class="tab-section active">

    <div class="card" style="border-left: 5px solid #ef4444; margin-top: 20px;">
        <h2 style="color:#ef4444; text-transform:uppercase; margin-bottom: 5px;">
            🚨 Students At Risk (Overall Average Mark < 50%)
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom: 15px;">
            This list shows all students whose total average mark across all their subjects is less than 50%. Immediate intervention is recommended for students at the top of this list.
        </p>

        <!-- BATCH DOWNLOAD BUTTON -->
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="action_batch_report.php" 
               class="f-btn" style="padding: 8px 15px; font-size: 14px; text-decoration: none; background: var(--accent);" target="_blank">
                ⬇️ Download Full PDF Report
            </a>
        </div>
        
        <div class="table-wrapper">
            <table style="width:100%;">
                <thead>
                    <tr style="background:var(--bg-input);">
                        <th style="width: 20%;">Student Name</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Nationality</th>
                        <th>Overall Avg Mark</th>
                        <th>Latest CGPA</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($studentsAtRisk)): ?>
                    <?php foreach($studentsAtRisk as $student): 
                        $cgpa = round($student['latest_cgpa'] ?? 0, 2);
                        // CGPA Meter Logic (0-4.0 scale)
                        $cgpaWidth = ($cgpa / 4.0) * 100;
                        $cgpaColor = '#10b981'; // Green (3.0+)
                        if ($cgpa < 2.5) $cgpaColor = '#f59e0b'; // Amber (2.0-2.5)
                        if ($cgpa < 2.0) $cgpaColor = '#ef4444'; // Red (Under 2.0)
                    ?>
                    <tr>
                        <td style="font-weight:bold;"><?php echo htmlspecialchars($student['name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['status'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['nationality'] ?? 'N/A'); ?></td>
                        <td><strong style="color:#ef4444;"><?php echo htmlspecialchars(round($student['overall_avg_mark'], 2) ?? 'N/A'); ?>%</strong></td>
                        <td>
                            <!-- CGPA METER FORM -->
                            <div style="font-weight:bold; color:<?php echo $cgpaColor; ?>;">
                                <?php echo htmlspecialchars($cgpa); ?>
                            </div>
                            <div style="background:var(--bg-body); border-radius:4px; overflow:hidden; height:6px; width:70px; display:inline-block; vertical-align:middle;">
                                <div style="width:<?php echo $cgpaWidth; ?>%; height:100%; background:<?php echo $cgpaColor;?>;"></div>
                            </div>
                        </td>
                        <td style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                            <!-- 1. Generate Counselling Letter Button -->
                            <a href="action_generate_letter.php?student_id=<?php echo urlencode($student['student_id']); ?>" 
                               class="f-btn" style="padding: 5px 10px; font-size: 12px; text-decoration: none; background:#ef4444; width:100%;" target="_blank">
                                ✉️ Generate Letter
                            </a>
                            <!-- 2. View Profile Button (COLOR FIXED) -->
                            <a href="index.php?active_tab=indiv&student_id=<?php echo urlencode($student['student_id']); ?>" 
                               class="f-btn" style="padding: 5px 10px; font-size: 12px; text-decoration: none; background: var(--accent); width:100%;">
                                View Profile
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">🎉 No students currently below the 50% overall average risk threshold.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>