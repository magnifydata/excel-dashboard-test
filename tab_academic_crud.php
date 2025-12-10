<!-- 
    FILE: tab_academic_crud.php
    PURPOSE: Frontend for managing Academic Data (Students, Subjects, Results).
    CHANGES: 
    - CRITICAL FIX: Changed <input type="date"> to <input type="month"> to match the YYYYMM data model, preventing the day selection error.
    - Updated JS loading and submission logic to handle the YYYY-MM format from the new input type.
    - All previous fixes preserved.
-->
<style>
    /* CSS for the internal tabs */
    .crud-nav { 
        display: flex; border-bottom: 2px solid var(--border); margin-bottom: 20px; 
        padding-bottom: 5px; 
    }
    .crud-tab-btn {
        background: none; border: none; padding: 10px 15px; cursor: pointer;
        font-weight: 600; color: var(--text-muted); font-size: 14px;
        transition: color 0.2s, border-bottom 0.2s;
    }
    .crud-tab-btn.active {
        color: var(--accent); border-bottom: 3px solid var(--accent);
    }
    .crud-content-section {
        display: none;
    }
    .crud-content-section.active {
        display: block;
    }
    .crud-table-actions {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
    }
    .crud-table-wrapper table th, .crud-table-wrapper table td {
        padding: 10px 15px;
    }

    /* --- Filter Bar style for the search input --- */
    .crud-filter-bar {
        display: flex; gap: 10px; align-items: center; margin-bottom: 15px;
    }
    .crud-filter-bar .f-select {
        flex-grow: 1; /* Make the search input take up most of the space */
    }

    /* --- Modal Styles (Used by Create, Confirm, and Status) --- */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.7); display: none; z-index: 1000;
        align-items: center; justify-content: center;
    }
    .modal-content {
        background: var(--bg-card); padding: 30px; border-radius: 12px;
        width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    .modal-header {
        display: flex; justify-content: space-between; align-items: center; 
        margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;
    }
    .modal-header h3 { margin: 0; font-size: 18px; color: var(--text-main); }
    .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group-full { grid-column: 1 / 3; }

    /* Custom styles for post-submission message box */
    .message-box {
        padding: 20px; border-radius: 8px; text-align: center;
        margin-top: 15px;
    }
    .message-box.success {
        background: #d1e7dd; color: #0f5132; border: 1px solid #0f5132;
    }
    .message-box.error {
        background: #f8d7da; color: #842029; border: 1px solid #842029;
    }
    .message-box h4 { margin-top: 0; font-size: 16px; font-weight: 700; }
    
    /* Delete Confirmation Specifics */
    .delete-warning-icon { 
        font-size: 40px; color: #ef4444; margin-bottom: 15px; 
    }
</style>

<div id="academicCrudTab" class="tab-section active">
    
    <div class="card" style="margin-top: 20px;">
        <h2 style="color:var(--text-main); text-transform:uppercase;">Academic Data Management</h2>
        
        <!-- NAVIGATION FOR ENTITIES (STUDENTS, SUBJECTS, RESULTS) -->
        <div class="crud-nav">
            <button class="crud-tab-btn active" onclick="switchCrudEntity('students')">Student Records</button>
            <button class="crud-tab-btn" onclick="switchCrudEntity('subjects')">Subject Catalog</button>
            <button class="crud-tab-btn" onclick="switchCrudEntity('results')">Student Results</button>
        </div>

        <!-- CONTENT FOR STUDENT RECORDS -->
        <div id="crud-students" class="crud-content-section active">
            <p style="font-size:13px; color:var(--text-muted); margin-bottom: 15px;">
                Manage core student details (Name, ID, Program, etc.).
            </p>
            
            <!-- Search Filter Input -->
            <div class="crud-filter-bar">
                <input type="text" id="studentSearchInput" class="f-select" placeholder="Search by Student Name or ID...">
            </div>
            
            <div class="crud-table-actions">
                <button id="createStudentBtn" class="f-btn" style="background: #10b981;">+ Create New Student</button>
                <button id="refreshStudentBtn" class="f-btn" style="background: var(--text-muted);">↻ Refresh List</button>
            </div>
            
            <div id="studentMessage" style="padding:10px; border-radius:6px; margin-bottom:15px; display:none;"></div>

            <div class="table-wrapper crud-table-wrapper">
                <table style="width:100%;" id="studentTable">
                    <thead>
                        <tr style="background:var(--bg-input);">
                            <th>Name</th>
                            <th>ID</th>
                            <th>Program</th>
                            <th>Intake Year</th>
                            <th>Status</th>
                            <th>Nationality</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <tr><td colspan="7" style="text-align:center;">Loading student data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CONTENT FOR SUBJECT CATALOG (Placeholder) -->
        <div id="crud-subjects" class="crud-content-section">
            <p style="font-size:13px; color:var(--text-muted); margin-bottom: 15px;">
                Manage subject codes, credit hours, and default lecturer assignment. (Coming Soon)
            </p>
        </div>

        <!-- CONTENT FOR STUDENT RESULTS (Placeholder) -->
        <div id="crud-results" class="crud-content-section">
            <p style="font-size:13px; color:var(--text-muted); margin-bottom: 15px;">
                Manage individual subject results (Marks/Grades) for students. (Coming Soon)
            </p>
        </div>
    </div>
</div>

<!-- Create/Edit Student Modal HTML -->
<div id="createStudentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Student Record</h3>
            <button class="close-btn" onclick="closeStudentCreateModal()">&times;</button>
        </div>
        
        <!-- Form Content (Default view) -->
        <div id="studentFormContent">
            <form id="createStudentForm" data-is-editing="false" data-original-id="">
                <div class="form-grid">
                    
                    <div class="f-group form-group-full">
                        <label>Full Name <span style="color:red">*</span></label>
                        <input type="text" id="name" name="name" class="f-select" required>
                    </div>
                    
                    <div class="f-group">
                        <label>Student ID <span style="color:red">*</span></label>
                        <input type="text" id="student_id" name="student_id" class="f-select" required>
                    </div>
                    
                    <div class="f-group">
                        <label>Admission No</label>
                        <input type="text" id="admission_no" name="admission_no" class="f-select">
                    </div>
                    
                    <!-- Level Code Dropdown -->
                    <div class="f-group">
                        <label>Level Code <span style="color:red">*</span></label>
                        <select id="level_code" name="level_code" class="f-select" required>
                            <option value="" disabled selected>Select or enter new...</option>
                            <!-- Options populated by JavaScript -->
                        </select>
                    </div>
                    
                    <!-- Level Category Dropdown -->
                    <div class="f-group">
                        <label>Level Category</label>
                        <select id="level_category" name="level_category" class="f-select">
                            <option value="" selected>Select or enter new...</option>
                            <!-- Options populated by JavaScript -->
                        </select>
                    </div>
                    
                    <!-- Intake No Date Picker (type="month") 
                    *** CRITICAL FIX: Changed to type="month" and removed 'name' attribute. *** -->
                    <div class="f-group">
                        <label>Intake Date (YYYY-MM) <span style="color:red">*</span></label>
                        <input type="month" id="intake_no_date" class="f-select" required>
                    </div>
                    
                    <div class="f-group">
                        <label>Nationality</label>
                        <input type="text" id="nationality" name="nationality" class="f-select" placeholder="e.g., Malaysian">
                    </div>

                    <div class="f-group">
                        <label>Status</label>
                        <select id="status" name="status" class="f-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Graduated">Graduated</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-full" style="margin-top: 20px; text-align: right;">
                    <button type="button" class="f-btn" style="background: var(--text-muted); margin-right: 10px;" onclick="closeStudentCreateModal()">Cancel</button>
                    <button type="submit" class="f-btn" style="background: #10b981;" id="saveStudentBtn">Save Student</button>
                </div>
            </form>
        </div>

        <!-- Message Content (Post-submission view) -->
        <div id="studentMessageContent" style="display: none;">
            <!-- Message will be dynamically inserted here -->
            <div id="postSubmitMessageBox" class="message-box"></div>

            <div style="margin-top: 20px; text-align: center;">
                <button type="button" class="f-btn" style="background: var(--accent);" onclick="closeStudentCreateModal(true)">Back to Student List</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal HTML (Unchanged) -->
<div id="deleteConfirmModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div class="delete-warning-icon">
            <span style="font-size: 50px;">&#x26A0;</span> 
        </div>
        <h3 style="color: #ef4444; border-bottom: none; margin-bottom: 5px;">CONFIRM DELETE</h3>
        <p id="deleteConfirmText" style="margin-bottom: 20px;">Are you sure you want to delete this record? This action is **IRREVERSIBLE**.</p>

        <div style="display: flex; justify-content: space-around;">
            <button class="f-btn" style="background: var(--text-muted);" onclick="document.getElementById('deleteConfirmModal').style.display='none'">Cancel</button>
            <button class="f-btn" style="background: #ef4444;" id="confirmDeleteBtn">Yes, Delete It</button>
        </div>
    </div>
</div>

<!-- Operation Status Modal (For success/error after deletion) -->
<div id="statusModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <h3 id="statusModalTitle">Operation Status</h3>
        
        <div id="statusMessageBox" class="message-box">
            <!-- Dynamic message goes here -->
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <button type="button" class="f-btn" style="background: var(--accent);" onclick="closeStatusModal(true)">OK / Back to List</button>
        </div>
    </div>
</div>


<script>
// --- GLOBAL STATE ---
let currentEntity = 'students';
let studentOptions = {}; 
let searchTimeout; 

// --- UI CONTROL (Unchanged) ---
function switchCrudEntity(entity) {
    currentEntity = entity;
    
    document.querySelectorAll('.crud-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.innerText.toLowerCase().includes(entity.replace('s', ''))) {
            btn.classList.add('active');
        }
    });

    document.querySelectorAll('.crud-content-section').forEach(section => {
        section.classList.remove('active');
        if (section.id === 'crud-' + entity) {
            section.classList.add('active');
        }
    });

    if (entity === 'students') {
        const searchTerm = document.getElementById('studentSearchInput')?.value || '';
        fetchStudentList(searchTerm);
    }
}

// Utility to display messages (banner above table)
function showStudentMessage(type, message) {
    const msgDiv = document.getElementById('studentMessage');
    msgDiv.style.display = 'block';
    msgDiv.innerText = message;
    if (type === 'success') {
        msgDiv.style.background = '#d1e7dd'; 
        msgDiv.style.color = '#0f5132';
    } else {
        msgDiv.style.background = '#f8d7da'; 
        msgDiv.style.color = '#842029';
    }
}


// --- DATA FETCH & RENDERING (STUDENTS) ---

function fetchStudentList(searchTerm = '') {
    const tbody = document.getElementById('studentTableBody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading student data...</td></tr>';
    document.getElementById('studentMessage').style.display = 'none';

    const payload = { 
        task: 'read_students',
        search: searchTerm.trim() 
    };

    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderStudentTable(data.data);
        } else {
            showStudentMessage('error', data.message || 'Failed to load student data.');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">' + (data.message || 'Error loading data.') + '</td></tr>';
        }
    })
    .catch(error => {
        showStudentMessage('error', 'API Connection Error. See console for details.');
        console.error('API Error:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Connection error.</td></tr>';
    });
}

function renderStudentTable(students) {
    const tbody = document.getElementById('studentTableBody');
    tbody.innerHTML = ''; 

    if (students.length === 0) {
        const searchTerm = document.getElementById('studentSearchInput')?.value;
        const msg = searchTerm ? `No students found matching "${searchTerm}".` : 'No students found.';
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">${msg}</td></tr>`;
        return;
    }

    students.forEach(student => {
        const row = tbody.insertRow(); 
        
        row.insertCell().textContent = student.name;
        row.insertCell().textContent = student.student_id;
        row.insertCell().textContent = (student.level_category ? student.level_category + ' (' : '') + student.level_code + (student.level_category ? ')' : '');
        row.insertCell().textContent = student.year;
        row.insertCell().innerHTML = `<span class="badge" style="background:${student.status==='Active'?'#10b981':'#f59e0b'}; color:white;">${student.status}</span>`;
        row.insertCell().textContent = student.nationality;
        
        const actionCell = row.insertCell();
        actionCell.style.textAlign = 'center';
        actionCell.innerHTML = `
            <button class="f-btn" style="background:#f59e0b; padding: 5px 10px; font-size: 12px; margin-right: 5px;" 
                data-id="${student.student_id}" onclick="openStudentEditModal('${student.student_id}')">Edit</button>
            <button class="f-btn" style="background:#ef4444; padding: 5px 10px; font-size: 12px;" 
                data-id="${student.student_id}" onclick="deleteStudent('${student.student_id}', '${student.name}')">Delete</button>
        `;
    });
}
// -----------------------------------------------------------------------


// --- CREATE/EDIT MODAL FUNCTIONS ---

async function fetchStudentOptions() {
    if (Object.keys(studentOptions).length > 0) return; // Already fetched

    try {
        const response = await fetch('api_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task: 'read_student_options' })
        });
        const result = await response.json();

        if (result.success) {
            studentOptions = result.data;
            populateDropdowns(studentOptions);
        } else {
            console.error('Failed to fetch options:', result.message);
        }
    } catch (error) {
        console.error('API Connection Error while fetching options:', error);
    }
}

function populateDropdowns(options) {
    const levelCodeSelect = document.getElementById('level_code');
    const levelCategorySelect = document.getElementById('level_category');

    // Clear existing options (keep the default placeholder)
    levelCodeSelect.querySelectorAll('option:not([disabled]):not([selected])').forEach(o => o.remove());
    levelCategorySelect.querySelectorAll('option:not([selected])').forEach(o => o.remove());

    // Populate Level Codes
    options.level_codes.forEach(code => {
        const option = document.createElement('option');
        option.value = code;
        option.textContent = code;
        levelCodeSelect.appendChild(option);
    });

    // Populate Level Categories
    options.level_categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        levelCategorySelect.appendChild(option);
    });
}


async function openStudentCreateModal() {
    // 1. Fetch and populate options 
    await fetchStudentOptions();

    // 2. Set modal for CREATE mode
    document.getElementById('modalTitle').textContent = 'Add New Student Record';
    document.getElementById('createStudentForm').reset();
    document.getElementById('createStudentForm').dataset.isEditing = 'false'; // Reset to CREATE
    document.getElementById('createStudentForm').dataset.originalId = ''; // Clear original ID
    document.getElementById('student_id').disabled = false; // Re-enable ID for new record
    
    document.getElementById('studentFormContent').style.display = 'block';
    document.getElementById('studentMessageContent').style.display = 'none';
    document.getElementById('saveStudentBtn').disabled = false;
    document.getElementById('saveStudentBtn').textContent = 'Save Student';

    document.getElementById('createStudentModal').style.display = 'flex';
}

function closeStudentCreateModal(shouldReload = false) {
    document.getElementById('createStudentModal').style.display = 'none';
    if (shouldReload) {
        const searchTerm = document.getElementById('studentSearchInput')?.value || '';
        fetchStudentList(searchTerm); 
    }
}

// --- UPDATED: EDIT FETCH LOGIC (Date Fix for type="month") ---
async function openStudentEditModal(studentId) {
    // 1. Fetch dropdown options and set the modal to loading state
    await fetchStudentOptions();

    const modal = document.getElementById('createStudentModal');
    const form = document.getElementById('createStudentForm');
    const saveBtn = document.getElementById('saveStudentBtn');

    // Clear and set to loading view
    form.reset();
    document.getElementById('modalTitle').textContent = `Loading Data for ID: ${studentId}...`;
    document.getElementById('studentFormContent').style.display = 'block';
    document.getElementById('studentMessageContent').style.display = 'none';
    saveBtn.disabled = true;
    modal.style.display = 'flex';
    
    // 2. Fetch the existing student data from the API
    try {
        const response = await fetch('api_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task: 'read_single_student', student_id: studentId })
        });
        const result = await response.json();

        if (result.success && result.data) {
            const student = result.data;
            
            // 3. Populate the form fields and set Edit Mode flags
            document.getElementById('modalTitle').textContent = `Edit Record: ${student.name}`;
            
            // Set flags for the submission handler to know it's an UPDATE
            form.dataset.isEditing = 'true';
            form.dataset.originalId = student.student_id; 

            // Populate fields (IDs match form field names)
            document.getElementById('name').value = student.name;
            document.getElementById('student_id').value = student.student_id;
            document.getElementById('admission_no').value = student.admission_no || '';
            document.getElementById('level_code').value = student.level_code || '';
            document.getElementById('level_category').value = student.level_category || '';
            document.getElementById('status').value = student.status || 'Active';
            document.getElementById('nationality').value = student.nationality || '';
            
            // **FIX for Intake Date:** Convert YYYYMM (from DB) to YYYY-MM (for type="month" input)
            let intakeDateValue = '';
            if (student.intake_no && student.intake_no.length >= 6) {
                // Format YYYYMM to YYYY-MM
                const year = student.intake_no.substring(0, 4);
                const month = student.intake_no.substring(4, 6);
                if (year && month) {
                    intakeDateValue = `${year}-${month}`; // YYYY-MM format
                }
            }
            document.getElementById('intake_no_date').value = intakeDateValue;

            // Optional: Disable Student ID field to prevent PK change during edit (safe practice)
            document.getElementById('student_id').disabled = true;

            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';

        } else {
            document.getElementById('modalTitle').textContent = `Error Loading Record`;
            showStudentMessage('error', result.message || 'Failed to fetch student data for editing.');
            modal.style.display = 'none';
        }

    } catch (error) {
        document.getElementById('modalTitle').textContent = `API Error`;
        showStudentMessage('error', 'API Connection Error: Could not fetch data for edit.');
        modal.style.display = 'none';
        console.error('Fetch Edit Data Error:', error);
    }
}
// --- END UPDATED EDIT FETCH LOGIC ---


// --- UPDATED: FORM SUBMISSION HANDLER (Date Re-check for type="month") ---
document.getElementById('createStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const isEditing = form.dataset.isEditing === 'true'; 
    
    const data = { 
        task: isEditing ? 'update_student' : 'create_student',
        original_student_id: isEditing ? form.dataset.originalId : undefined 
    };

    const formData = new FormData(form);

    // 1. Special Handling for Intake Date (YYYY-MM -> YYYYMM)
    const intakeDate = document.getElementById('intake_no_date').value; // Reads YYYY-MM
    
    data['intake_no'] = ''; 

    // Convert YYYY-MM (from month input) to YYYYMM (for API/DB)
    if (intakeDate && intakeDate.length === 7) { // length 7 = YYYY-MM
        data['intake_no'] = intakeDate.substring(0, 4) + intakeDate.substring(5, 7);
    }
    
    // 2. Convert remaining FormData to JSON payload
    for (const [key, value] of formData.entries()) {
        // Skip the manually handled student_id (intake_no_date is already ignored as it has no 'name')
        if (key !== 'student_id') {
             data[key] = value;
        }
    }
    // Re-add student_id, which might be disabled in Edit mode
    data['student_id'] = document.getElementById('student_id').value;


    
    // Check if any REQUIRED field is empty (client-side backup)
    if (!data.name || !data.student_id || !data.level_code || !data.intake_no) {
        showStudentMessage('error', 'Client-side: Please fill in all required fields marked with (*).');
        return; 
    }

    const saveBtn = document.getElementById('saveStudentBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = isEditing ? 'Saving Changes...' : 'Saving...';
    document.getElementById('studentMessage').style.display = 'none';


    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        document.getElementById('studentFormContent').style.display = 'none';
        document.getElementById('studentMessageContent').style.display = 'block';
        
        const msgBox = document.getElementById('postSubmitMessageBox');
        
        const studentNameForMsg = data.name;
        const studentIdForMsg = data.student_id;
        const actionText = isEditing ? 'updated' : 'added';

        if (result.success) {
            msgBox.className = 'message-box success';
            const customMessage = `Student '${studentNameForMsg} (${studentIdForMsg})' has been successfully ${actionText}.`;

            msgBox.innerHTML = `<h4>Success!</h4><p>${customMessage}</p><p>You can now return to the list to see the changes.</p>`;
        } else {
            msgBox.className = 'message-box error';
            const msg = result.message.includes('exists') ? 'Error: Student ID already exists. Please check your data.' : result.message;
            msgBox.innerHTML = `<h4>Operation Failed!</h4><p>${msg}</p><p>Please close and re-open the form to try again.</p>`;
        }
    })
    .catch(error => {
        document.getElementById('studentFormContent').style.display = 'none';
        document.getElementById('studentMessageContent').style.display = 'block';
        const msgBox = document.getElementById('postSubmitMessageBox');
        msgBox.className = 'message-box error';
        msgBox.innerHTML = `<h4>Critical Error!</h4><p>API Connection Error: Could not save data. Check your server logs.</p>`;
        console.error('Submission Error:', error);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = isEditing ? 'Save Changes' : 'Save Student';
    });
});


// --- DELETE STUDENT FUNCTIONS (Unchanged) ---

function openStatusModal(type, title, message, reloadList = false) {
    const modal = document.getElementById('statusModal');
    const msgBox = document.getElementById('statusMessageBox');
    const titleElement = document.getElementById('statusModalTitle');
    const backBtn = modal.querySelector('button');

    // Set classes and content
    titleElement.textContent = title;
    msgBox.className = `message-box ${type}`;
    msgBox.innerHTML = `<h4>${title}</h4><p>${message}</p>`;
    
    // Set button to close modal and reload list
    backBtn.onclick = () => closeStatusModal(reloadList);

    modal.style.display = 'flex';
}

function closeStatusModal(shouldReload) {
    document.getElementById('statusModal').style.display = 'none';
    if (shouldReload) {
        const searchTerm = document.getElementById('studentSearchInput')?.value || '';
        fetchStudentList(searchTerm);
    }
}

// 1. Show Confirmation Modal
function deleteStudent(studentId, studentName) {
    const modal = document.getElementById('deleteConfirmModal');
    
    // Update confirmation text with student details
    document.getElementById('deleteConfirmText').innerHTML = `Are you sure you want to delete **${studentName}** (ID: ${studentId})? This action is **IRREVERSIBLE** and will remove all associated results.`;

    // Rebind the Confirm button to the specific ID
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.onclick = () => confirmDeletion(studentId, studentName);

    modal.style.display = 'flex';
}

// 2. Execute Deletion API Call
function confirmDeletion(studentId, studentName) {
    // Close the confirmation modal
    document.getElementById('deleteConfirmModal').style.display = 'none';
    
    // Disable all action buttons for this row visually
    document.querySelectorAll(`button[data-id="${studentId}"]`).forEach(btn => btn.disabled = true);
    showStudentMessage('warning', `Attempting to delete student ${studentId}...`);
    
    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task: 'delete_student', student_id: studentId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Success: Show the status modal and reload the list
            openStatusModal('success', 'Deletion Successful', `The record for **${studentName}** was permanently removed.`, true);
            showStudentMessage('success', result.message);
        } else {
            // Failure: Show the status modal and keep the row buttons enabled
            openStatusModal('error', 'Deletion Failed', result.message || `An error occurred while deleting ${studentName}.`, false);
            showStudentMessage('error', result.message || `Failed to delete student ${studentId}.`);
            document.querySelectorAll(`button[data-id="${studentId}"]`).forEach(btn => btn.disabled = false);
        }
    })
    .catch(error => {
        // Critical Error: Show the status modal
        openStatusModal('error', 'API Error', 'A connection error occurred. Check the console for details.', false);
        showStudentMessage('error', 'API Connection Error: Could not delete student.');
        document.querySelectorAll(`button[data-id="${studentId}"]`).forEach(btn => btn.disabled = false);
    });
}


// --- SEARCH LISTENER ---
document.getElementById('studentSearchInput').addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;
    
    if (searchTerm.length >= 2 || searchTerm.length === 0) {
        searchTimeout = setTimeout(() => {
            fetchStudentList(searchTerm);
        }, 300); 
    }
});


// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function() {
    fetchStudentList(); 

    document.getElementById('refreshStudentBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('studentSearchInput')?.value || '';
        fetchStudentList(searchTerm);
    });

    document.getElementById('createStudentBtn').addEventListener('click', openStudentCreateModal);
});
</script>