<?php 
require_once 'db.php';

// --- CONFIGURATION: Add any folders you want to scan here ---
$target_dirs = ['assets/images/', 'users_img/']; 

// --- HELPER: Validate if file is inside one of our allowed folders ---
function is_valid_path($file_path, $allowed_dirs) {
    $real_path = realpath($file_path);
    if (!$real_path || !file_exists($real_path)) return false;

    foreach ($allowed_dirs as $dir) {
        $real_dir = realpath($dir);
        if ($real_dir && strpos($real_path, $real_dir) === 0) {
            return true;
        }
    }
    return false;
}

// --- HANDLE ACTIONS (DELETE / RENAME) ---
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Handle Delete
    if (isset($_POST['delete_file'])) {
        $file_path = $_POST['file_path'];
        
        if (is_valid_path($file_path, $target_dirs)) {
            if (unlink($file_path)) {
                $msg = "<div class='alert alert-success'>Image deleted successfully.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Error deleting image.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Invalid file or permission denied.</div>";
        }
    }

    // 2. Handle Rename (Edit)
    if (isset($_POST['rename_file'])) {
        $old_path = $_POST['old_path'];
        $new_name = $_POST['new_name'];
        $path_info = pathinfo($old_path);
        
        // Construct new path
        $new_path = $path_info['dirname'] . '/' . $new_name . '.' . $path_info['extension'];

        if (is_valid_path($old_path, $target_dirs)) {
            if (!file_exists($new_path)) {
                if (rename($old_path, $new_path)) {
                    $msg = "<div class='alert alert-success'>Image renamed successfully.</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Error renaming file.</div>";
                }
            } else {
                $msg = "<div class='alert alert-warning'>A file with that name already exists.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Invalid file path.</div>";
        }
    }
}

// --- SCAN MULTIPLE DIRECTORIES FOR IMAGES ---
$images = [];
foreach ($target_dirs as $dir) {
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $images[] = $file->getPathname();
                }
            }
        }
    }
}

include 'inc/header.php';
?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Website Gallery Manager</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.html" class="hover-text-primary">Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Gallery Grid</li>
        </ul>
    </div>

    <?php if($msg) echo $msg; ?>

    <div class="card h-100 p-0 radius-12 overflow-hidden gallery-scale">
        <div class="card-body p-24">
            
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="badge bg-primary-50 text-primary-600"><?php echo count($images); ?> Images Found</span>
            </div>

            <div class="row gy-4">
                
                <?php if (empty($images)): ?>
                    <div class="col-12 py-5 text-center">
                        <iconify-icon icon="solar:gallery-remove-bold" class="text-secondary display-4 mb-3"></iconify-icon>
                        <h6>No images found in the specified directories.</h6>
                    </div>
                <?php else: ?>
                    <?php foreach ($images as $img_path): 
                        $img_name = basename($img_path);
                        $folder_name = basename(dirname($img_path)); // Get folder name (e.g., users_img)
                        $img_id = md5($img_path); 
                    ?>
                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="hover-scale-img border radius-16 overflow-hidden p-8 position-relative bg-light">
                            
                            <span class="badge bg-white text-dark shadow-sm position-absolute top-0 start-0 m-3 z-1" style="font-size: 10px;">
                                <iconify-icon icon="solar:folder-bold" class="me-1"></iconify-icon> <?php echo $folder_name; ?>
                            </span>

                            <a href="<?php echo $img_path; ?>" class="popup-img w-100 h-100 d-flex radius-12 overflow-hidden bg-white border" style="height: 200px !important;">
                                <img src="<?php echo $img_path; ?>" alt="Image" class="hover-scale-img__img w-100 h-100 object-fit-contain">
                            </a>

                            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                <span class="text-secondary text-truncate" style="max-width: 55%; font-size: 12px; font-weight: 500;" title="<?php echo $img_name; ?>">
                                    <?php echo $img_name; ?>
                                </span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary p-1 lh-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $img_id; ?>" title="Rename">
                                        <iconify-icon icon="solar:pen-bold" style="font-size: 16px;"></iconify-icon>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Permanently delete <?php echo $img_name; ?>?');" class="d-inline">
                                        <input type="hidden" name="file_path" value="<?php echo $img_path; ?>">
                                        <button type="submit" name="delete_file" class="btn btn-sm btn-outline-danger p-1 lh-1" title="Delete">
                                            <iconify-icon icon="solar:trash-bin-trash-bold" style="font-size: 16px;"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal fade" id="editModal<?php echo $img_id; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Rename Image</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <p class="small text-secondary mb-3">Location: <code><?php echo $img_path; ?></code></p>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">New Filename</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="new_name" value="<?php echo pathinfo($img_name, PATHINFO_FILENAME); ?>" required>
                                                <span class="input-group-text">.<?php echo pathinfo($img_name, PATHINFO_EXTENSION); ?></span>
                                            </div>
                                            <input type="hidden" name="old_path" value="<?php echo $img_path; ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="rename_file" class="btn btn-primary btn-sm">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    if (typeof $ !== 'undefined') {
        $('.popup-img').magnificPopup({ type: 'image', gallery: { enabled: true } });
    }
</script>

<?php include 'inc/footer.php'; ?>