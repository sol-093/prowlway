<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $data = [
                'academic_year' => $_POST['academic_year'] ?? '',
                'start_year' => $_POST['start_year'] ?? date('Y'),
                'end_year' => $_POST['end_year'] ?? date('Y') + 1,
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'display_order' => intval($_POST['display_order'] ?? 0),
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image'], 'images');
                if ($uploadResult['success']) {
                    // Delete old image if updating
                    if ($_POST['action'] === 'update' && !empty($_POST['old_image'])) {
                        deleteUploadedFile($_POST['old_image']);
                    }
                    $data['image'] = $uploadResult['path'];
                } else {
                    $message = 'Image upload failed: ' . $uploadResult['error'];
                    $messageType = 'error';
                }
            } elseif ($_POST['action'] === 'update' && !empty($_POST['old_image'])) {
                // Keep existing image if no new upload
                $data['image'] = $_POST['old_image'];
            }
            
            if (empty($message)) {
                if ($_POST['action'] === 'create') {
                    $result = dbInsert('batches', $data);
                    if ($result) {
                        $message = 'Batch created successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error creating batch.';
                        $messageType = 'error';
                    }
                } else {
                    $id = intval($_POST['id']);
                    $result = dbUpdate('batches', $data, 'id = :id', ['id' => $id]);
                    if ($result) {
                        $message = 'Batch updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error updating batch.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            // Get batch to delete image
            $batch = dbFetchOne("SELECT image FROM batches WHERE id = ?", [$id]);
            if ($batch && !empty($batch['image'])) {
                deleteUploadedFile($batch['image']);
            }
            $result = dbDelete('batches', 'id = :id', ['id' => $id]);
            if ($result) {
                $message = 'Batch deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting batch.';
                $messageType = 'error';
            }
        }
    }
}

// Fetch batches
$batches = dbFetchAll("SELECT * FROM batches ORDER BY display_order DESC, start_year DESC");

// Fetch single batch for editing
$editBatch = null;
if (isset($_GET['edit'])) {
    $editBatch = dbFetchOne("SELECT * FROM batches WHERE id = ?", [intval($_GET['edit'])]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Batches - PROWLWAY Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div class="header" style="margin-bottom: 2rem;">
            <h1>Manage Batches</h1>
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="btn-goto">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Create/Edit Form -->
        <div class="form-section" style="background: var(--color-card-dark); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h2><?php echo $editBatch ? 'Edit Batch' : 'Create New Batch'; ?></h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editBatch ? 'update' : 'create'; ?>">
                <?php if ($editBatch): ?>
                    <input type="hidden" name="id" value="<?php echo $editBatch['id']; ?>">
                <?php endif; ?>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Academic Year *</label>
                        <input type="text" name="academic_year" value="<?php echo htmlspecialchars($editBatch['academic_year'] ?? ''); ?>" required placeholder="A.Y. 2025-2026">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($editBatch['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo ($editBatch['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="archived" <?php echo ($editBatch['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Start Year *</label>
                        <input type="number" name="start_year" value="<?php echo $editBatch['start_year'] ?? date('Y'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>End Year *</label>
                        <input type="number" name="end_year" value="<?php echo $editBatch['end_year'] ?? date('Y') + 1; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Image</label>
                    <?php if ($editBatch && !empty($editBatch['image'])): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <img src="<?php echo getImageUrl($editBatch['image']); ?>" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editBatch['image']); ?>">
                        </div>
                        <small style="color: var(--color-text-muted); display: block; margin-bottom: 0.5rem;">Upload a new image to replace the current one</small>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small style="color: var(--color-text-muted); display: block; margin-top: 0.5rem;">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($editBatch['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="<?php echo $editBatch['display_order'] ?? 0; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary"><?php echo $editBatch ? 'Update Batch' : 'Create Batch'; ?></button>
                <?php if ($editBatch): ?>
                    <a href="<?php echo ADMIN_URL; ?>/batches.php" class="btn btn-toggle">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Batches List -->
        <div class="list-section">
            <h2>All Batches</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--color-card-dark);">
                        <th style="padding: 1rem; text-align: left;">Academic Year</th>
                        <th style="padding: 1rem; text-align: left;">Years</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Order</th>
                        <th style="padding: 1rem; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: var(--color-text-muted);">No batches found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($batch['academic_year']); ?></td>
                                <td style="padding: 1rem;"><?php echo $batch['start_year']; ?> - <?php echo $batch['end_year']; ?></td>
                                <td style="padding: 1rem;"><?php echo ucfirst($batch['status']); ?></td>
                                <td style="padding: 1rem;"><?php echo $batch['display_order']; ?></td>
                                <td style="padding: 1rem;">
                                    <a href="<?php echo ADMIN_URL; ?>/batches.php?edit=<?php echo $batch['id']; ?>" class="btn btn-small">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this batch?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $batch['id']; ?>">
                                        <button type="submit" class="btn btn-small" style="background: #dc3545;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

