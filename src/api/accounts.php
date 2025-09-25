<?php
session_start();
require_once '../../config/database.php';
require_once '../auth.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($action);
            break;
        case 'POST':
            handlePost($action);
            break;
        case 'PUT':
            handlePut($action);
            break;
        case 'DELETE':
            handleDelete($action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($action) {
    $db = getDB();
    
    switch ($action) {
        case 'all':
            getAllAccounts();
            break;
        case 'detail':
            getAccountDetail();
            break;
        case 'loan_history':
            getLoanHistory();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function getAllAccounts() {
    $db = getDB();
    
    // Get all staff accounts
    $staffQuery = "SELECT 
        id,
        username,
        name,
        email,
        phone,
        address,
        join_date,
        profile_picture,
        role as permission_level,
        'staff' as account_type,
        visibility,
        created_at
    FROM staff 
    WHERE visibility = 1 
    ORDER BY name ASC";
    
    // Get all member accounts  
    $memberQuery = "SELECT 
        id,
        member_id as username,
        name,
        email,
        phone,
        address,
        membership_date as join_date,
        profile_picture,
        'member' as permission_level,
        'member' as account_type,
        visibility,
        created_at
    FROM members 
    WHERE visibility = 1 
    ORDER BY name ASC";
    
    $staffStmt = $db->query($staffQuery);
    $memberStmt = $db->query($memberQuery);
    
    $accounts = array_merge($staffStmt->fetchAll(), $memberStmt->fetchAll());
    
    // Sort by name
    usort($accounts, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    echo json_encode(['accounts' => $accounts]);
}

function getAccountDetail() {
    $accountType = $_GET['type'] ?? '';
    $accountId = $_GET['id'] ?? '';
    
    if (!$accountType || !$accountId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $db = getDB();
    
    if ($accountType === 'staff') {
        $stmt = $db->prepare("SELECT * FROM staff WHERE id = ? AND visibility = 1");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();
        
        if ($account) {
            $account['account_type'] = 'staff';
            $account['permission_level'] = $account['role'];
        }
    } else {
        $stmt = $db->prepare("SELECT * FROM members WHERE id = ? AND visibility = 1");
        $stmt->execute([$accountId]);
        $account = $stmt->fetch();
        
        if ($account) {
            $account['account_type'] = 'member';
            $account['permission_level'] = 'member';
        }
    }
    
    if (!$account) {
        http_response_code(404);
        echo json_encode(['error' => 'Account not found']);
        return;
    }
    
    echo json_encode(['account' => $account]);
}

function getLoanHistory() {
    $accountType = $_GET['type'] ?? '';
    $accountId = $_GET['id'] ?? '';
    
    if (!$accountType || !$accountId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $db = getDB();
    
    if ($accountType === 'member') {
        $stmt = $db->prepare("
            SELECT l.*, b.title, b.author, b.isbn,
                   s.name as processed_by
            FROM loans l
            JOIN books b ON l.book_id = b.id
            JOIN staff s ON l.staff_id = s.id
            WHERE l.member_id = ?
            ORDER BY l.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$accountId]);
        $loans = $stmt->fetchAll();
        
        echo json_encode(['loan_history' => $loans]);
    } else {
        // Staff don't have loan history as borrowers
        echo json_encode(['loan_history' => []]);
    }
}

function handlePost($action) {
    // Only admin can create accounts
    if (!hasRole('admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Admin only']);
        return;
    }
    
    switch ($action) {
        case 'create':
            createAccount();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function createAccount() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['name', 'email', 'permission_level'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    $db = getDB();
    
    try {
        if ($input['permission_level'] === 'member') {
            // Generate member ID
            $stmt = $db->query("SELECT COUNT(*) as count FROM members");
            $count = $stmt->fetch()['count'];
            $memberId = 'M' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            
            $stmt = $db->prepare("
                INSERT INTO members (member_id, name, email, phone, address, membership_date, profile_picture, visibility)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $memberId,
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['address'] ?? '',
                date('Y-m-d'),
                $input['profile_picture'] ?? 'uploads/profiles/member_default.jpg'
            ]);
            
            $accountId = $db->lastInsertId();
            echo json_encode(['success' => true, 'account_id' => $accountId, 'member_id' => $memberId]);
            
        } else {
            // Create staff account
            $username = $input['username'] ?? strtolower(str_replace(' ', '', $input['name']));
            $password = $input['password'] ?? 'password123';
            
            $stmt = $db->prepare("
                INSERT INTO staff (username, password, name, email, phone, address, role, join_date, profile_picture, visibility)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $username,
                md5($password),
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['address'] ?? '',
                $input['permission_level'],
                date('Y-m-d'),
                $input['profile_picture'] ?? 'uploads/profiles/staff_default.jpg'
            ]);
            
            $accountId = $db->lastInsertId();
            echo json_encode(['success' => true, 'account_id' => $accountId, 'username' => $username, 'password' => $password]);
        }
        
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($action) {
    // Only admin can edit accounts (or users can edit their own)
    $currentUser = getCurrentUser();
    
    switch ($action) {
        case 'update':
            updateAccount();
            break;
        case 'change_password':
            changePassword();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function updateAccount() {
    if (!hasRole('admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Admin only']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $accountType = $input['account_type'] ?? '';
    $accountId = $input['id'] ?? '';
    
    if (!$accountType || !$accountId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $db = getDB();
    
    try {
        if ($accountType === 'staff') {
            $stmt = $db->prepare("
                UPDATE staff 
                SET name = ?, email = ?, phone = ?, address = ?, role = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['address'] ?? '',
                $input['permission_level'],
                $accountId
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE members 
                SET name = ?, email = ?, phone = ?, address = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['address'] ?? '',
                $accountId
            ]);
        }
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function changePassword() {
    $input = json_decode(file_get_contents('php://input'), true);
    $accountId = $input['id'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    
    if (!$accountId || !$newPassword) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    // Only admin or the user themselves can change password
    $currentUser = getCurrentUser();
    if (!hasRole('admin') && $currentUser['id'] != $accountId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        return;
    }
    
    $db = getDB();
    
    try {
        $stmt = $db->prepare("UPDATE staff SET password = ? WHERE id = ?");
        $stmt->execute([md5($newPassword), $accountId]);
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($action) {
    // Only admin can delete accounts
    if (!hasRole('admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Admin only']);
        return;
    }
    
    switch ($action) {
        case 'soft_delete':
            softDeleteAccount();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function softDeleteAccount() {
    $accountType = $_GET['type'] ?? '';
    $accountId = $_GET['id'] ?? '';
    
    if (!$accountType || !$accountId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    $db = getDB();
    
    try {
        if ($accountType === 'staff') {
            $stmt = $db->prepare("UPDATE staff SET visibility = 0 WHERE id = ?");
        } else {
            $stmt = $db->prepare("UPDATE members SET visibility = 0 WHERE id = ?");
        }
        
        $stmt->execute([$accountId]);
        
        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
