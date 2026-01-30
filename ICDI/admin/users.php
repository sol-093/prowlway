<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireSuperAdmin(false);

$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validate CSRF token
    requireCSRFToken(false);
    
    if ($_POST['action'] === 'update_role') {
        $id = intval($_POST['id'] ?? 0);
        $role = $_POST['role'] ?? '';
        if ($id && in_array($role, ['editor', 'admin', 'super_admin'], true)) {
            $result = dbUpdate('admins', ['role' => $role], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('role_assign', "Role set to {$role} for admin #{$id}", 'admin', $id);
                $message = 'Role updated.';
                $messageType = 'success';
            } else {
                $message = 'Update failed.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid role or user ID.';
            $messageType = 'error';
        }
    } elseif ($_POST['action'] === 'create') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? 'editor';
        if (empty($email) || empty($password) || empty($name)) {
            $message = 'Email, password, and name are required.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
            $messageType = 'error';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif (!in_array($role, ['editor', 'admin', 'super_admin'], true)) {
            $message = 'Invalid role.';
            $messageType = 'error';
        } else {
            $existing = dbFetchOne("SELECT id FROM admins WHERE email = ?", [$email]);
            if ($existing) {
                $message = 'Email already exists.';
                $messageType = 'error';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $id = dbInsert('admins', [
                    'email' => $email,
                    'password' => $hashedPassword,
                    'name' => $name,
                    'role' => $role,
                    'status' => 'active'
                ]);
                if ($id) {
                    auditLog('user_create', "Created {$role} user: {$name} ({$email})", 'admin', $id);
                    $message = 'User created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to create user.';
                    $messageType = 'error';
                }
            }
        }
    } elseif ($_POST['action'] === 'archive') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === (int)($_SESSION['admin_id'] ?? 0)) {
            $message = 'Cannot archive your own account.';
            $messageType = 'error';
        } elseif ($id > 0) {
            $result = dbUpdate('admins', ['status' => 'archived'], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('user_archive', "Archived admin #{$id}", 'admin', $id);
                $message = 'User archived successfully.';
                $messageType = 'success';
            } else {
                $message = 'Archive failed.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid user ID.';
            $messageType = 'error';
        }
    } elseif ($_POST['action'] === 'change_password') {
        $id = intval($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($id <= 0) {
            $message = 'Invalid user ID.';
            $messageType = 'error';
        } elseif (empty($newPassword)) {
            $message = 'Password is required.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $result = dbUpdate('admins', ['password' => $hashedPassword], 'id = :id', ['id' => $id]);
            if ($result) {
                auditLog('password_change', "Password changed for admin #{$id}", 'admin', $id);
                $message = 'Password changed successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to change password.';
                $messageType = 'error';
            }
        }
    }
}

$admins = dbFetchAll("SELECT id, email, name, role, created_at FROM admins WHERE status = 'active' OR status IS NULL ORDER BY id ASC");

$pageTitle = 'User roles - PROWLWAY Admin';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Roles</h1>
                    <p class="text-gray-600 mt-2">Manage admin users and their roles</p>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-colors shadow-sm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
            
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl border-2 <?php echo $messageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?> flex items-center gap-3">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
                    <?php if ($messageType === 'success'): ?>
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    <?php else: ?>
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    <?php endif; ?>
                </svg>
                <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Create User Form -->
        <div class="bg-white rounded-xl shadow-sm border-2 border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                    Create New User
                </h2>
                <p class="text-sm text-gray-500 mt-2 ml-13">Add a new admin user to the system</p>
            </div>
            <div class="p-6">
                <form method="post" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <input type="hidden" name="action" value="create">
                    <?php if (function_exists('generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" required placeholder="e.g., ICDI Admin" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" required placeholder="user@kld.edu.ph" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required placeholder="Min 6 characters" minlength="6" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role</label>
                        <select name="role" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-medium">
                            <option value="editor">Editor</option>
                            <option value="admin">Administrator</option>
                            <option value="super_admin">Super Administrator</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border-2 border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl flex items-center justify-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    All Users (<?php echo count($admins); ?>)
                </h2>
                <p class="text-sm text-gray-500 mt-2 ml-13">Editor: draft only. Admin: approve/publish/archive. Super Admin: full access.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="text-left px-6 py-3 font-bold text-gray-700">Email</th>
                            <th class="text-left px-6 py-3 font-bold text-gray-700">Name</th>
                            <th class="text-left px-6 py-3 font-bold text-gray-700">Role</th>
                            <th class="text-left px-6 py-3 font-bold text-gray-700">Created</th>
                            <th class="text-left px-6 py-3 font-bold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($admins as $a): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">
                                <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($a['email']); ?></span>
                            </td>
                            <td class="px-6 py-3 text-gray-700 font-medium"><?php echo htmlspecialchars($a['name'] ?? '-'); ?></td>
                            <td class="px-6 py-3">
                                <form method="post" class="inline-flex items-center gap-2">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                    <?php if (function_exists('generateCSRFToken')): ?>
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php endif; ?>
                                    <select name="role" onchange="this.form.submit()" class="px-4 py-2 bg-white border-2 rounded-lg text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer min-w-[160px] <?php 
                                        echo $a['role'] === 'super_admin' ? 'border-purple-500 hover:border-purple-600' : 
                                        ($a['role'] === 'admin' ? 'border-indigo-500 hover:border-indigo-600' : 'border-gray-300 hover:border-gray-400');
                                    ?>">
                                        <option value="editor" <?php echo $a['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                        <option value="admin" <?php echo $a['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                        <option value="super_admin" <?php echo $a['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700 whitespace-nowrap">
                                <?php echo date('M j, Y', strtotime($a['created_at'])); ?>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <button 
                                        onclick="openPasswordModal(<?php echo (int)$a['id']; ?>, '<?php echo htmlspecialchars($a['email'], ENT_QUOTES); ?>')" 
                                        class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-sm hover:shadow-md whitespace-nowrap h-[36px] flex items-center justify-center"
                                    >
                                        Change Password
                                    </button>
                                    <?php if ($a['id'] !== (int)($_SESSION['admin_id'] ?? 0)): ?>
                                    <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to archive this user? They will be moved to archive management.');">
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                        <?php if (function_exists('generateCSRFToken')): ?>
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-800 border-2 border-gray-300 font-semibold rounded-lg hover:bg-gray-300 transition-colors text-sm shadow-sm hover:shadow-md min-w-[80px] h-[36px]">
                                            Archive
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 border-2 border-gray-300 font-semibold rounded-lg hover:bg-gray-300 transition-colors text-sm shadow-sm hover:shadow-md min-w-[80px] h-[36px] flex items-center justify-center" disabled>
                                        User
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 border-2 border-gray-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Change Password</h3>
        </div>
        <form id="passwordForm" method="post" class="space-y-5">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="id" id="password-user-id">
            <?php if (function_exists('generateCSRFToken')): ?>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <?php endif; ?>
            <div>
                <label for="password-user-email" class="block text-sm font-bold text-gray-700 mb-2">User</label>
                <input type="text" id="password-user-email" readonly class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label for="new_password" class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                <p class="mt-2 text-xs text-gray-500">Minimum 6 characters</p>
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">Change Password</button>
                <button type="button" onclick="closePasswordModal()" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPasswordModal(userId, userEmail) {
    document.getElementById('password-user-id').value = userId;
    document.getElementById('password-user-email').value = userEmail;
    document.getElementById('passwordModal').classList.remove('hidden');
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('passwordModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});

// Validate password match before submit
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
