<?php
session_start();
require_once '../config/database.php';
require_once '../src/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = "Kelola Anggota - Sistem Perpustakaan";
$current_user = getCurrentUser();
include '../src/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Kelola Anggota</h1>
        <p>Manajemen akun anggota, petugas perpustakaan, dan administrator</p>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <?php if (hasRole('admin')): ?>
            <button class="btn btn-primary" onclick="openAddAccountModal()">
                <span class="icon">+</span> Tambah Akun
            </button>
        <?php endif; ?>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Cari nama atau email..." onkeyup="filterAccounts()">
        </div>
        <div class="filter-bar">
            <select id="permissionFilter" onchange="filterAccounts()">
                <option value="">Semua Level</option>
                <option value="member">Member</option>
                <option value="librarian">Librarian</option>
                <option value="admin">Admin</option>
            </select>
        </div>
    </div>

    <!-- Accounts Grid -->
    <div id="accountsGrid" class="accounts-grid">
        <div class="loading">Memuat data akun...</div>
    </div>
</div>

<!-- Account Detail Modal -->
<div id="accountDetailModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h2>Detail Akun</h2>
            <span class="close" onclick="closeModal('accountDetailModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="account-detail-container">
                <div class="account-profile">
                    <img id="detailProfilePicture" src="" alt="Profile Picture" class="profile-picture-large">
                    <div class="account-basic-info">
                        <h3 id="detailName"></h3>
                        <p id="detailEmail" class="email"></p>
                        <span id="detailPermissionBadge" class="permission-badge"></span>
                    </div>
                </div>
                
                <div class="account-details">
                    <div class="detail-section">
                        <h4>Informasi Personal</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Nama Lengkap:</label>
                                <span id="detailFullName"></span>
                            </div>
                            <div class="detail-item">
                                <label>Email:</label>
                                <span id="detailEmailFull"></span>
                            </div>
                            <div class="detail-item">
                                <label>Telepon:</label>
                                <span id="detailPhone"></span>
                            </div>
                            <div class="detail-item">
                                <label>Alamat:</label>
                                <span id="detailAddress"></span>
                            </div>
                            <div class="detail-item">
                                <label>Tanggal Bergabung:</label>
                                <span id="detailJoinDate"></span>
                            </div>
                            <div class="detail-item">
                                <label>Level Permission:</label>
                                <span id="detailPermissionLevel"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section" id="loanHistorySection">
                        <h4>Riwayat Peminjaman</h4>
                        <div id="loanHistoryTable" class="loan-history">
                            <div class="loading">Memuat riwayat peminjaman...</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <?php if (hasRole('admin')): ?>
                        <button class="btn btn-warning" onclick="openEditModal()">
                            <span class="icon">✏️</span> Edit
                        </button>
                        <button class="btn btn-danger" onclick="confirmDelete()">
                            <span class="icon">🗑️</span> Hapus
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="closeModal('accountDetailModal')">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<?php if (hasRole('admin')): ?>
<div id="addAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Tambah Akun Baru</h2>
            <span class="close" onclick="closeModal('addAccountModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addAccountForm">
                <div class="form-group">
                    <label for="addName">Nama Lengkap *</label>
                    <input type="text" id="addName" required>
                </div>
                <div class="form-group">
                    <label for="addEmail">Email *</label>
                    <input type="email" id="addEmail" required>
                </div>
                <div class="form-group">
                    <label for="addPhone">Telepon</label>
                    <input type="text" id="addPhone">
                </div>
                <div class="form-group">
                    <label for="addAddress">Alamat</label>
                    <textarea id="addAddress" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="addPermissionLevel">Level Permission *</label>
                    <select id="addPermissionLevel" required onchange="toggleStaffFields()">
                        <option value="">Pilih Level</option>
                        <option value="member">Member</option>
                        <option value="librarian">Librarian</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div id="staffFields" class="staff-fields" style="display: none;">
                    <div class="form-group">
                        <label for="addUsername">Username</label>
                        <input type="text" id="addUsername" placeholder="Otomatis dari nama jika kosong">
                    </div>
                    <div class="form-group">
                        <label for="addPassword">Password</label>
                        <input type="password" id="addPassword" placeholder="Default: password123">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="addAccount()">Simpan</button>
            <button class="btn btn-secondary" onclick="closeModal('addAccountModal')">Batal</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Account Modal -->
<?php if (hasRole('admin')): ?>
<div id="editAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Akun</h2>
            <span class="close" onclick="closeModal('editAccountModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editAccountForm">
                <input type="hidden" id="editAccountId">
                <input type="hidden" id="editAccountType">
                
                <div class="form-group">
                    <label for="editName">Nama Lengkap *</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email *</label>
                    <input type="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Telepon</label>
                    <input type="text" id="editPhone">
                </div>
                <div class="form-group">
                    <label for="editAddress">Alamat</label>
                    <textarea id="editAddress" rows="3"></textarea>
                </div>
                <div class="form-group" id="editPermissionGroup">
                    <label for="editPermissionLevel">Level Permission *</label>
                    <select id="editPermissionLevel" required>
                        <option value="member">Member</option>
                        <option value="librarian">Librarian</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group" id="changePasswordGroup">
                    <label for="newPassword">Password Baru</label>
                    <input type="password" id="newPassword" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="updateAccount()">Simpan</button>
            <button class="btn btn-secondary" onclick="closeModal('editAccountModal')">Batal</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content small">
        <div class="modal-header">
            <h2>Konfirmasi Hapus</h2>
            <span class="close" onclick="closeModal('deleteConfirmModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus akun ini?</p>
            <p><strong id="deleteAccountName"></strong></p>
            <p class="warning">Akun akan disembunyikan dari sistem dan tidak dapat diakses lagi.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="deleteAccount()">Ya, Hapus</button>
            <button class="btn btn-secondary" onclick="closeModal('deleteConfirmModal')">Batal</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.page-header p {
    color: #7f8c8d;
    font-size: 16px;
}

.action-bar {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-bar {
    flex: 1;
}

.search-bar input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.filter-bar select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.accounts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.account-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.account-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.account-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.profile-picture {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 2px solid #eee;
}

.profile-picture-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #eee;
}

.account-info h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    color: #2c3e50;
}

.account-info .email {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.permission-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    margin-top: 5px;
}

.permission-badge.admin {
    background: #e74c3c;
    color: white;
}

.permission-badge.librarian {
    background: #f39c12;
    color: white;
}

.permission-badge.member {
    background: #27ae60;
    color: white;
}

.account-details-preview {
    font-size: 14px;
    color: #7f8c8d;
}

.account-details-preview .detail-item {
    margin-bottom: 5px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content.large {
    max-width: 900px;
}

.modal-content.small {
    max-width: 400px;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #2c3e50;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #eee;
    text-align: right;
}

.account-detail-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.account-profile {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.account-basic-info h3 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: #2c3e50;
}

.account-basic-info .email {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #7f8c8d;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 5px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-item label {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}

.detail-item span {
    color: #7f8c8d;
}

.loan-history {
    max-height: 300px;
    overflow-y: auto;
}

.loan-history table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.loan-history th,
.loan-history td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.loan-history th {
    background: #f8f9fa;
    font-weight: bold;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.borrowed {
    background: #f39c12;
    color: white;
}

.status-badge.returned {
    background: #27ae60;
    color: white;
}

.status-badge.overdue {
    background: #e74c3c;
    color: white;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #2c3e50;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
}

.staff-fields {
    border: 1px solid #eee;
    border-radius: 4px;
    padding: 15px;
    background: #f8f9fa;
    margin-top: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: background-color 0.2s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.warning {
    color: #e74c3c;
    font-style: italic;
    font-size: 14px;
}

.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: #27ae60;
    color: white;
    border-radius: 4px;
    display: none;
    z-index: 1001;
}

.toast.error {
    background: #e74c3c;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

@media (max-width: 768px) {
    .accounts-grid {
        grid-template-columns: 1fr;
    }
    
    .action-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .modal-content {
        width: 95%;
        margin: 2% auto;
    }
    
    .account-profile {
        flex-direction: column;
        text-align: center;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let accounts = [];
let currentAccount = null;

// Load accounts when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadAccounts();
});

// Load all accounts
async function loadAccounts() {
    try {
        const response = await fetch('../src/api/accounts.php?action=all');
        const data = await response.json();
        
        if (data.accounts) {
            accounts = data.accounts;
            displayAccounts(accounts);
        } else {
            showToast('Error loading accounts', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error loading accounts', 'error');
    }
}

// Display accounts in grid
function displayAccounts(accountsToShow) {
    const grid = document.getElementById('accountsGrid');
    
    if (accountsToShow.length === 0) {
        grid.innerHTML = '<div class="loading">Tidak ada akun ditemukan</div>';
        return;
    }
    
    const html = accountsToShow.map(account => `
        <div class="account-card" onclick="showAccountDetail('${account.account_type}', ${account.id})">
            <div class="account-header">
                <img src="../assets/images/${account.profile_picture || 'default-avatar.jpg'}" 
                     alt="Profile" class="profile-picture" 
                     onerror="this.src='../assets/images/default-avatar.jpg'">
                <div class="account-info">
                    <h3>${account.name}</h3>
                    <p class="email">${account.email}</p>
                    <span class="permission-badge ${account.permission_level}">${getPermissionLabel(account.permission_level)}</span>
                </div>
            </div>
            <div class="account-details-preview">
                <div class="detail-item">📞 ${account.phone || 'Tidak ada'}</div>
                <div class="detail-item">📅 Bergabung: ${formatDate(account.join_date)}</div>
            </div>
        </div>
    `).join('');
    
    grid.innerHTML = html;
}

// Filter accounts
function filterAccounts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const permissionFilter = document.getElementById('permissionFilter').value;
    
    let filtered = accounts.filter(account => {
        const matchesSearch = account.name.toLowerCase().includes(searchTerm) || 
                            account.email.toLowerCase().includes(searchTerm);
        const matchesPermission = !permissionFilter || account.permission_level === permissionFilter;
        
        return matchesSearch && matchesPermission;
    });
    
    displayAccounts(filtered);
}

// Show account detail
async function showAccountDetail(accountType, accountId) {
    try {
        const response = await fetch(`../src/api/accounts.php?action=detail&type=${accountType}&id=${accountId}`);
        const data = await response.json();
        
        if (data.account) {
            currentAccount = data.account;
            populateAccountDetail(data.account);
            loadLoanHistory(accountType, accountId);
            document.getElementById('accountDetailModal').style.display = 'block';
        } else {
            showToast('Error loading account details', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error loading account details', 'error');
    }
}

// Populate account detail modal
function populateAccountDetail(account) {
    document.getElementById('detailProfilePicture').src = `../assets/images/${account.profile_picture || 'default-avatar.jpg'}`;
    document.getElementById('detailName').textContent = account.name;
    document.getElementById('detailEmail').textContent = account.email;
    document.getElementById('detailPermissionBadge').textContent = getPermissionLabel(account.permission_level);
    document.getElementById('detailPermissionBadge').className = `permission-badge ${account.permission_level}`;
    
    document.getElementById('detailFullName').textContent = account.name;
    document.getElementById('detailEmailFull').textContent = account.email;
    document.getElementById('detailPhone').textContent = account.phone || 'Tidak ada';
    document.getElementById('detailAddress').textContent = account.address || 'Tidak ada';
    document.getElementById('detailJoinDate').textContent = formatDate(account.join_date || account.membership_date);
    document.getElementById('detailPermissionLevel').textContent = getPermissionLabel(account.permission_level);
    
    // Hide loan history section for staff
    const loanSection = document.getElementById('loanHistorySection');
    if (account.account_type === 'staff') {
        loanSection.style.display = 'none';
    } else {
        loanSection.style.display = 'block';
    }
}

// Load loan history
async function loadLoanHistory(accountType, accountId) {
    const historyDiv = document.getElementById('loanHistoryTable');
    historyDiv.innerHTML = '<div class="loading">Memuat riwayat peminjaman...</div>';
    
    try {
        const response = await fetch(`../src/api/accounts.php?action=loan_history&type=${accountType}&id=${accountId}`);
        const data = await response.json();
        
        if (data.loan_history && data.loan_history.length > 0) {
            const html = `
                <table>
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.loan_history.map(loan => `
                            <tr>
                                <td>
                                    <strong>${loan.title}</strong><br>
                                    <small>oleh ${loan.author}</small>
                                </td>
                                <td>${formatDate(loan.loan_date)}</td>
                                <td>${formatDate(loan.due_date)}</td>
                                <td><span class="status-badge ${loan.status}">${getStatusLabel(loan.status)}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            historyDiv.innerHTML = html;
        } else {
            historyDiv.innerHTML = '<p>Tidak ada riwayat peminjaman</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        historyDiv.innerHTML = '<p class="error">Error memuat riwayat peminjaman</p>';
    }
}

// Open add account modal
function openAddAccountModal() {
    document.getElementById('addAccountForm').reset();
    document.getElementById('staffFields').style.display = 'none';
    document.getElementById('addAccountModal').style.display = 'block';
}

// Toggle staff fields based on permission level
function toggleStaffFields() {
    const permissionLevel = document.getElementById('addPermissionLevel').value;
    const staffFields = document.getElementById('staffFields');
    
    if (permissionLevel === 'librarian' || permissionLevel === 'admin') {
        staffFields.style.display = 'block';
    } else {
        staffFields.style.display = 'none';
    }
}

// Add new account
async function addAccount() {
    const formData = {
        name: document.getElementById('addName').value,
        email: document.getElementById('addEmail').value,
        phone: document.getElementById('addPhone').value,
        address: document.getElementById('addAddress').value,
        permission_level: document.getElementById('addPermissionLevel').value,
        username: document.getElementById('addUsername').value,
        password: document.getElementById('addPassword').value
    };
    
    if (!formData.name || !formData.email || !formData.permission_level) {
        showToast('Harap isi semua field yang wajib', 'error');
        return;
    }
    
    try {
        const response = await fetch('../src/api/accounts.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Akun berhasil ditambahkan');
            closeModal('addAccountModal');
            loadAccounts();
        } else {
            showToast(data.error || 'Error adding account', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error adding account', 'error');
    }
}

// Open edit modal
function openEditModal() {
    if (!currentAccount) return;
    
    document.getElementById('editAccountId').value = currentAccount.id;
    document.getElementById('editAccountType').value = currentAccount.account_type;
    document.getElementById('editName').value = currentAccount.name;
    document.getElementById('editEmail').value = currentAccount.email;
    document.getElementById('editPhone').value = currentAccount.phone || '';
    document.getElementById('editAddress').value = currentAccount.address || '';
    
    // Show/hide permission level field based on account type
    const permissionGroup = document.getElementById('editPermissionGroup');
    const passwordGroup = document.getElementById('changePasswordGroup');
    
    if (currentAccount.account_type === 'member') {
        permissionGroup.style.display = 'none';
        passwordGroup.style.display = 'none';
    } else {
        permissionGroup.style.display = 'block';
        passwordGroup.style.display = 'block';
        document.getElementById('editPermissionLevel').value = currentAccount.permission_level;
    }
    
    closeModal('accountDetailModal');
    document.getElementById('editAccountModal').style.display = 'block';
}

// Update account
async function updateAccount() {
    const formData = {
        id: document.getElementById('editAccountId').value,
        account_type: document.getElementById('editAccountType').value,
        name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        phone: document.getElementById('editPhone').value,
        address: document.getElementById('editAddress').value,
        permission_level: document.getElementById('editPermissionLevel').value
    };
    
    try {
        const response = await fetch('../src/api/accounts.php?action=update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Change password if provided
            const newPassword = document.getElementById('newPassword').value;
            if (newPassword && formData.account_type === 'staff') {
                await changePassword(formData.id, newPassword);
            }
            
            showToast('Akun berhasil diperbarui');
            closeModal('editAccountModal');
            loadAccounts();
        } else {
            showToast(data.error || 'Error updating account', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error updating account', 'error');
    }
}

// Change password
async function changePassword(accountId, newPassword) {
    try {
        const response = await fetch('../src/api/accounts.php?action=change_password', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: accountId,
                new_password: newPassword
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            showToast(data.error || 'Error changing password', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error changing password', 'error');
    }
}

// Confirm delete
function confirmDelete() {
    if (!currentAccount) return;
    
    document.getElementById('deleteAccountName').textContent = currentAccount.name;
    closeModal('accountDetailModal');
    document.getElementById('deleteConfirmModal').style.display = 'block';
}

// Delete account (soft delete)
async function deleteAccount() {
    if (!currentAccount) return;
    
    try {
        const response = await fetch(`../src/api/accounts.php?action=soft_delete&type=${currentAccount.account_type}&id=${currentAccount.id}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Akun berhasil dihapus');
            closeModal('deleteConfirmModal');
            loadAccounts();
            currentAccount = null;
        } else {
            showToast(data.error || 'Error deleting account', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error deleting account', 'error');
    }
}

// Utility functions
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

function getPermissionLabel(permission) {
    switch (permission) {
        case 'admin': return 'Administrator';
        case 'librarian': return 'Petugas';
        case 'member': return 'Anggota';
        default: return permission;
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'borrowed': return 'Dipinjam';
        case 'returned': return 'Dikembalikan';
        case 'overdue': return 'Terlambat';
        default: return status;
    }
}

function formatDate(dateString) {
    if (!dateString) return 'Tidak ada';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../src/footer.php'; ?>
