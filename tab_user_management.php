<!-- 
    FILE: tab_user_management.php
    PURPOSE: Frontend for managing user accounts (CRUD: Create, Read, Update, Delete).
    NOTE: This UI calls the api_crud.php endpoint.
    CHANGES: FINAL FIX: Academic Permission Checkbox is now fully optional/selectable.
-->

<div id="userManagementTab" class="tab-section active">
    
    <div class="card" style="margin-top: 20px;">
        <h2 style="color:var(--text-main); text-transform:uppercase;">User Account Management</h2>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom: 15px;">
            Manage user roles and credentials for dashboard access. *Warning: Changes are permanent.*
        </p>

        <div style="margin-bottom: 15px;">
            <button id="createUserBtn" class="f-btn" style="background: #10b981;" onclick="openCreateModal()">+ Create New User</button>
            <button id="refreshBtn" class="f-btn" style="background: var(--text-muted);">↻ Refresh List</button>
        </div>

        <div id="userMessage" style="padding:10px; border-radius:6px; margin-bottom:15px; display:none;"></div>
        
        <!-- User Table -->
        <div class="table-wrapper">
            <table style="width:100%;" id="userTable">
                <thead>
                    <tr style="background:var(--bg-input);">
                        <th style="width: 30%;">User Name</th>
                        <th style="width: 25%;">Display Name</th>
                        <th style="width: 15%;">Role</th>
                        <th style="width: 30%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Data will be populated by JavaScript -->
                    <tr><td colspan="4" style="text-align:center;">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- NEW: CREATE/EDIT USER MODAL STRUCTURE -->
<div id="userModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3 id="modalTitle">Create New User</h3>
        <span class="modal-close" onclick="closeModal()">&times;</span>
        
        <form id="userForm">
            <input type="hidden" id="formTask" name="task" value="create_user_with_perms">
            <input type="hidden" id="originalUsername" name="originalUsername" value=""> 

            <div class="form-group">
                <label for="username">Username (Login ID)</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="name">Display Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user">User (Standard Access)</option>
                    <option value="admin">Admin (Full Access)</option>
                </select>
            </div>

            <!-- PERMISSIONS CHECKBOXES -->
            <div class="form-group" id="permissionsGroup">
                <label>Access Permissions (For 'User' Role Only)</label>
                <div style="padding: 10px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-body);">
                    <!-- FIX: Academic is now a fully optional checkbox -->
                    <label style="display:block; font-weight:normal;">
                        <input type="checkbox" name="permission[]" value="view_academic" id="perm_academic"> Academic Data
                    </label>
                    <label style="display:block; font-weight:normal;">
                        <input type="checkbox" name="permission[]" value="view_marketing" id="perm_marketing"> Marketing Data
                    </label>
                    <label style="display:block; font-weight:normal;">
                        <input type="checkbox" name="permission[]" value="view_finance" id="perm_finance"> Finance Data
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password (Min 8 chars, leave blank to keep current)</label>
                <input type="password" id="password" name="password" minlength="8">
            </div>

            <div id="formMessage" class="error-msg" style="display:none; margin-bottom:15px;"></div>
            
            <button type="submit" id="modalSubmitBtn" class="f-btn">Create User</button>
        </form>
    </div>
</div>

<!-- NEW: MODAL STYLES -->
<style>
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0, 0, 0, 0.7); display: flex; justify-content: center; 
        align-items: center; z-index: 1000;
    }
    .modal-content {
        background: var(--bg-card); padding: 30px; border-radius: 12px; 
        width: 400px; max-height: 90vh; overflow-y: auto; position: relative;
    }
    .modal-close { position: absolute; top: 10px; right: 20px; font-size: 30px; 
        cursor: pointer; color: var(--text-muted); }
    .form-group { margin-bottom: 15px; }
    .form-group label { margin-bottom: 5px; font-weight: 600; color: var(--text-main); font-size: 14px; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border); 
        border-radius: 6px; background: var(--bg-input); color: var(--text-main); }
    .error-msg { color: #842029; background: #f8d7da; padding: 10px; border-radius: 6px; text-align: center; }
</style>


<!-- JAVASCRIPT LOGIC -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchUserList();
    document.getElementById('refreshBtn').addEventListener('click', fetchUserList);
    document.getElementById('createUserBtn').addEventListener('click', openCreateModal); 
    
    // Add listener to role dropdown to toggle permission checkboxes
    document.getElementById('role').addEventListener('change', togglePermissionChecks);

    // Main form submission handler for both Create and Update
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const task = document.getElementById('formTask').value;
        if (task === 'create_user_with_perms') {
            createUser();
        } else if (task === 'update_user_with_perms') {
            updateUser(); 
        }
    });
});

// Function to control which permissions are editable based on the Role dropdown
function togglePermissionChecks() {
    const role = document.getElementById('role').value;
    const isUserRole = (role === 'user');
    
    document.getElementById('permissionsGroup').style.display = isUserRole ? 'block' : 'none';
    
    // Academic is now fully optional, so no special disabling is needed here.
    document.getElementById('perm_marketing').disabled = !isUserRole;
    document.getElementById('perm_finance').disabled = !isUserRole;
}


// Utility functions
function showMessage(type, message) {
    const msgDiv = document.getElementById('userMessage');
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

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Function to handle the modal display for CREATE
function openCreateModal() {
    document.getElementById('modalTitle').innerText = 'Create New User';
    document.getElementById('formTask').value = 'create_user_with_perms';
    document.getElementById('modalSubmitBtn').innerText = 'Create User';
    
    document.getElementById('userForm').reset();
    document.getElementById('originalUsername').value = ''; 
    document.getElementById('formMessage').style.display = 'none';
    document.getElementById('username').readOnly = false;
    document.getElementById('password').required = true;
    document.getElementById('password').placeholder = ''; 

    // Reset all permissions to unchecked for a clean start
    document.getElementById('perm_academic').checked = false; 
    document.getElementById('perm_marketing').checked = false;
    document.getElementById('perm_finance').checked = false;
    
    togglePermissionChecks();
    
    document.getElementById('userModal').style.display = 'flex';
}

// --- EDIT/UPDATE USER LOGIC ---
function openEditModal(username) {
    document.getElementById('modalTitle').innerText = `Edit User: ${username}`;
    document.getElementById('formTask').value = 'update_user_with_perms';
    document.getElementById('modalSubmitBtn').innerText = 'Update User';
    document.getElementById('formMessage').style.display = 'none';
    
    document.getElementById('username').value = username;
    document.getElementById('username').readOnly = true;
    document.getElementById('originalUsername').value = username;

    document.getElementById('password').value = ''; 
    document.getElementById('password').required = false; 
    document.getElementById('password').placeholder = 'Leave blank to keep current password'; 

    // Reset permissions first
    document.getElementById('perm_academic').checked = false;
    document.getElementById('perm_marketing').checked = false;
    document.getElementById('perm_finance').checked = false;
    
    // Fetch existing user data to populate Name, Role, and Permissions
    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task: 'read_single_user_with_perms', username: username })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            document.getElementById('name').value = data.data.name;
            document.getElementById('role').value = data.data.role;
            
            togglePermissionChecks(); // Adjust visibility based on fetched role

            // Set Checkboxes based on Permissions (Only relevant if role is 'user')
            if (data.data.role === 'user') {
                document.getElementById('perm_academic').checked = data.data.permissions.includes('view_academic');
                document.getElementById('perm_marketing').checked = data.data.permissions.includes('view_marketing');
                document.getElementById('perm_finance').checked = data.data.permissions.includes('view_finance');
            }
            
            document.getElementById('userModal').style.display = 'flex';
        } else {
            showMessage('error', data.message || 'Could not fetch user details for editing.');
        }
    })
    .catch(error => {
        showMessage('error', 'API Connection Error fetching user data.');
        console.error('API Error:', error);
    });
}

function updateUser() {
    const form = document.getElementById('userForm');
    const formMessage = document.getElementById('formMessage');
    const formData = new FormData(form);

    const newPassword = formData.get('password');
    
    if (newPassword && newPassword.length > 0 && newPassword.length < 8) {
        formMessage.innerText = 'New password must be at least 8 characters long.';
        formMessage.style.display = 'block';
        return;
    }
    
    const data = Object.fromEntries(formData.entries());
    data.task = 'update_user_with_perms'; 

    // Handle Checkbox Array (Only checked boxes are in formData.entries())
    data.permissions = formData.getAll('permission[]'); 
    
    // If Admin, permissions are irrelevant as the API grants all
    if (data.role === 'admin') {
        data.permissions = ['is_admin']; 
    }

    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showMessage('success', data.message);
            fetchUserList(); 
        } else {
            formMessage.innerText = data.message || 'Update failed due to server error.';
            formMessage.style.display = 'block';
        }
    })
    .catch(error => {
        formMessage.innerText = 'API Connection Error. See console for details.';
        formMessage.style.display = 'block';
        console.error('API Error:', error);
    });
}
// ------------------------------------


// --- CREATE USER LOGIC ---
function createUser() {
    const form = document.getElementById('userForm');
    const formMessage = document.getElementById('formMessage');
    const formData = new FormData(form);

    if (formData.get('password').length < 8) {
        formMessage.innerText = 'Password must be at least 8 characters long.';
        formMessage.style.display = 'block';
        return;
    }
    
    const data = Object.fromEntries(formData.entries());
    data.task = 'create_user_with_perms'; 

    // Handle Checkbox Array (Only checked boxes are in formData.entries())
    data.permissions = formData.getAll('permission[]'); 
    
    // If Admin, permissions are irrelevant as the API grants all
    if (data.role === 'admin') {
        data.permissions = ['is_admin']; 
    }

    fetch('api_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showMessage('success', data.message);
            fetchUserList(); // Refresh the list
        } else {
            formMessage.innerText = data.message || 'Creation failed due to server error.';
            formMessage.style.display = 'block';
        }
    })
    .catch(error => {
        formMessage.innerText = 'API Connection Error. See console for details.';
        formMessage.style.display = 'block';
        console.error('API Error:', error);
    });
}
// -------------------------


// Function to fetch the user list from the API
function fetchUserList() {
    const tbody = document.getElementById('userTableBody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading users...</td></tr>';
    document.getElementById('userMessage').style.display = 'none';

    fetch('api_crud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ task: 'read_users' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderUserTable(data.data);
        } else {
            showMessage('error', 'Failed to load users: ' + (data.message || 'Server returned an error.'));
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Error loading data.</td></tr>';
        }
    })
    .catch(error => {
        showMessage('error', 'API Connection Error. See console for details.');
        console.error('API Error:', error);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Connection error.</td></td>';
    });
}

// Function to render the table rows
function renderUserTable(users) {
    const tbody = document.getElementById('userTableBody');
    tbody.innerHTML = ''; 

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No users found.</td></tr>';
        return;
    }

    const currentUsername = "<?php echo $_SESSION['username'] ?? ''; ?>";

    users.forEach(user => {
        const row = tbody.insertRow();
        const isCurrent = user.username === currentUsername;
        
        row.insertCell().textContent = user.username;
        row.insertCell().textContent = user.name;
        row.insertCell().innerHTML = `<span class="badge" style="background:${user.role==='admin'?'#ef4444':'#3b82f6'}; color:white;">${user.role.toUpperCase()}</span>`;
        
        const actionCell = row.insertCell();
        actionCell.style.textAlign = 'center';
        
        let deleteBtn = `<button class="f-btn" style="background:#ef4444; padding: 5px 10px; font-size: 12px;" 
                            data-user="${user.username}" onclick="deleteUser('${user.username}')">Delete</button>`;
        let editBtn = `<button class="f-btn" style="background:#f59e0b; padding: 5px 10px; font-size: 12px; margin-right: 5px;" 
                        data-user="${user.username}" onclick="openEditModal('${user.username}')">Edit</button>`;

        if (isCurrent) {
            deleteBtn = `<button class="f-btn" disabled style="background:#64748b; padding: 5px 10px; font-size: 12px; cursor: not-allowed;" title="Cannot delete the currently logged-in user">Delete</button>`;
        }
        
        actionCell.innerHTML = editBtn + deleteBtn;
    });
}

// Function to handle user deletion
function deleteUser(username) {
    if (document.getElementById('userTableBody').dataset.deleting === 'true') return;
    
    if (confirm(`Are you sure you want to permanently delete the user ${username}? THIS ACTION CANNOT BE UNDONE.`)) {
        
        document.getElementById('userTableBody').dataset.deleting = 'true'; 

        fetch('api_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ task: 'delete_user', username: username })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
                fetchUserList(); 
            } else {
                showMessage('error', data.message || 'Deletion failed due to a server error.');
            }
        })
        .catch(error => {
            showMessage('error', 'API Deletion Error. See console for details.');
            console.error('API Error:', error);
        })
        .finally(() => {
            document.getElementById('userTableBody').dataset.deleting = 'false';
        });
    }
}
</script>