<?php
include "config/db.php";
include "includes/auth_check.php";

$pageTitle = "My Profile | StoXVision";
$currentPage = "profile";
$user_id = $_SESSION["user_id"];

// Fetch Current Data
$stmt = $conn->prepare("SELECT name, email, profile_pic, profession, country, bio, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Server-side Validation Helper
function validateUpdate($field, $value) {
    switch ($field) {
        case 'name':
            return preg_match("/^[a-zA-Z\s]+$/", $value); // Letters and spaces only
        case 'phone':
            return empty($value) || preg_match("/^\+?[0-9\s\-]+$/", $value); // Numbers, +, -, spaces
        case 'profession':
        case 'country':
            return preg_match("/^[a-zA-Z0-9\s\.\-]+$/", $value); // Alphanumeric basically
        default:
            return true;
    }
}

include "includes/header.php";
?>

<div class="profile-container-premium">
    <div class="glass-bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="profile-card-premium">
        <!-- Top Section: Avatar & Hero Info -->
        <div class="profile-hero">
            <div class="avatar-wrapper">
                <input type="file" id="picUpload" hidden accept="image/*">
                <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=0ea5e9&color=fff&size=256'; ?>" 
                     alt="Profile" id="mainAvatar" class="premium-avatar">
                <div class="avatar-overlay" onclick="document.getElementById('picUpload').click()">
                    <i class="fas fa-camera"></i>
                </div>
                <div id="uploadSpinner" class="spinner" style="display: none;"></div>
            </div>

            <div class="hero-text">
                <h1 id="displayName" class="editable" data-field="name" data-validate="letters"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p id="displayProfession" class="editable" data-field="profession" data-validate="alphanumeric"><?php echo htmlspecialchars($user['profession'] ?: 'Investor'); ?></p>
                <div class="account-badge">
                    <i class="fas fa-shield-halved"></i> Verified Analyst
                </div>
            </div>
        </div>

        <div class="profile-divider"></div>

        <!-- Details Grid -->
        <div class="details-grid-premium">
            <div class="detail-item">
                <label><i class="fas fa-envelope"></i> Email Address</label>
                <div class="value-static"><?php echo htmlspecialchars($user['email']); ?></div>
                <small>Immutable for security</small>
            </div>

            <div class="detail-item">
                <label><i class="fas fa-globe"></i> Country</label>
                <div id="displayCountry" class="value-editable editable" data-field="country" data-validate="alphanumeric">
                    <?php echo htmlspecialchars($user['country'] ?: 'India'); ?>
                </div>
            </div>

            <div class="detail-item">
                <label><i class="fas fa-phone"></i> Phone Number</label>
                <div id="displayPhone" class="value-editable editable" data-field="phone" data-validate="numbers">
                    <?php echo htmlspecialchars($user['phone'] ?: 'Add Phone'); ?>
                </div>
            </div>

            <div class="detail-item">
                <label><i class="fas fa-calendar-alt"></i> Member Since</label>
                <div class="value-static">March 2026</div>
            </div>

            <div class="detail-item full-width">
                <label><i class="fas fa-quote-left"></i> About Me / Bio</label>
                <div id="displayBio" class="value-editable editable" data-field="bio" data-validate="all">
                    <?php echo nl2br(htmlspecialchars($user['bio'] ?: 'Share your trading philosophy...')); ?>
                </div>
            </div>
        </div>
        
        <div id="statusToast" class="toast"></div>
    </div>
</div>

<style>
:root {
    --p-card-bg: rgba(15, 23, 42, 0.6);
    --p-border: rgba(255, 255, 255, 0.08);
}

.profile-container-premium {
    position: relative;
    padding: 40px 0;
    min-height: 80vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.glass-bg-blobs {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: -1;
    overflow: hidden;
    top: 0;
}

.blob {
    position: absolute;
    width: 400px;
    height: 400px;
    filter: blur(100px);
    opacity: 0.15;
    border-radius: 50%;
}

.blob-1 { background: var(--primary); top: -100px; left: -100px; }
.blob-2 { background: var(--secondary); bottom: -100px; right: -100px; }

.profile-card-premium {
    background: var(--p-card-bg);
    backdrop-filter: blur(25px);
    border: 1px solid var(--p-border);
    border-radius: 40px;
    width: 100%;
    max-width: 850px;
    padding: 60px;
    box-shadow: 0 40px 100px rgba(0,0,0,0.5);
    position: relative;
}

.profile-hero {
    display: flex;
    align-items: center;
    gap: 40px;
    margin-bottom: 50px;
}

.avatar-wrapper {
    position: relative;
    width: 180px;
    height: 180px;
    flex-shrink: 0;
    aspect-ratio: 1/1;
}

.premium-avatar {
    width: 180px;
    height: 180px;
    border-radius: 50% !important;
    object-fit: cover;
    display: block;
    border: 5px solid var(--primary);
    box-shadow: 0 0 40px rgba(14, 165, 233, 0.3);
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 2rem;
    color: white;
}

.avatar-wrapper:hover .avatar-overlay {
    opacity: 1;
}

.hero-text h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 5px;
    letter-spacing: -1px;
}

.hero-text p {
    font-size: 1.1rem;
    color: var(--primary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 15px;
}

.account-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.15);
    color: var(--secondary);
    padding: 8px 16px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.85rem;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.profile-divider {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--p-border), transparent);
    margin: 40px 0;
}

.details-grid-premium {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
}

.detail-item {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--p-border);
    padding: 25px;
    border-radius: 24px;
    transition: transform 0.3s, border-color 0.3s;
}

.detail-item:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
}

.detail-item label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-secondary);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 12px;
    font-weight: 700;
}

.value-static, .value-editable {
    font-size: 1.2rem;
    font-weight: 600;
}

.full-width { grid-column: span 2; }

/* INLINE EDIT STYLES */
.editable {
    cursor: pointer;
    position: relative;
    border-bottom: 1px dashed transparent;
    transition: all 0.2s;
}

.editable:hover {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.editing-input {
    background: rgba(255,255,255,0.08);
    border: 1px solid var(--primary);
    border-radius: 8px;
    color: white;
    padding: 8px 12px;
    font-size: inherit;
    font-family: inherit;
    font-weight: inherit;
    width: 100%;
}

/* Validation hints */
.invalid-input {
    border-color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.05) !important;
}

/* Toast */
.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 15px 30px;
    border-radius: 12px;
    background: var(--p-card-bg);
    border: 1px solid var(--primary);
    color: white;
    font-weight: 600;
    transform: translateY(100px);
    transition: transform 0.5s cubic-bezier(0.18, 0.89, 0.32, 1.28);
    z-index: 2000;
}

.toast.show { transform: translateY(0); }

.spinner {
    position: absolute;
    top: calc(50% - 20px);
    left: calc(50% - 20px);
    border: 4px solid rgba(255,255,255,0.1);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Handle Inline Editing
    document.querySelectorAll('.editable').forEach(el => {
        el.addEventListener('click', function() {
            if (this.querySelector('input') || this.querySelector('textarea')) return;

            const field = this.dataset.field;
            const validateType = this.dataset.validate;
            const originalVal = this.innerText.trim();
            
            let input;
            if (field === 'bio') {
                input = document.createElement('textarea');
                input.rows = 4;
            } else {
                input = document.createElement('input');
                input.type = 'text';
            }

            input.value = originalVal;
            input.className = 'editing-input';
            
            // AGGRESSIVE REAL-TIME FILTERING
            const doFilter = (target) => {
                let val = target.value;
                if (validateType === 'letters') val = val.replace(/[^a-zA-Z\s]/g, '');
                if (validateType === 'numbers') val = val.replace(/[^\+0-9\s\-]/g, '');
                if (validateType === 'alphanumeric') val = val.replace(/[^a-zA-Z0-9\s\.\-]/g, '');
                if (target.value !== val) target.value = val;
            };

            input.oninput = () => doFilter(input);
            input.onkeyup = () => doFilter(input);
            input.onchange = () => doFilter(input);
            input.addEventListener('paste', (e) => {
                setTimeout(() => doFilter(input), 0);
            });

            const originalContent = this.innerHTML;
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();

            const finishEdit = async () => {
                if (!this.contains(input)) return;
                
                doFilter(input); // One last filter
                const newVal = input.value.trim();
                
                if (newVal === "" && field !== 'bio') {
                    this.innerHTML = originalContent;
                    return;
                }

                if (newVal === originalVal) {
                    this.innerHTML = originalContent;
                    return;
                }

                // Show saving state
                this.style.opacity = '0.5';
                
                try {
                    const formData = new FormData();
                    formData.append('field', field);
                    formData.append('value', newVal);
                    formData.append('update_profile_field', '1');

                    const resp = await fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();

                    if (data.success) {
                        this.innerHTML = newVal;
                        showToast("✅ Field updated successfully!");
                    } else {
                        this.innerHTML = originalContent;
                        showToast("❌ " + (data.message || "Update failed"), true);
                    }
                } catch (e) {
                    this.innerHTML = originalContent;
                    showToast("❌ Connection error", true);
                }
                this.style.opacity = '1';
            };

            input.addEventListener('blur', () => {
                setTimeout(() => {
                    if (this.contains(input)) finishEdit();
                }, 150);
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && field !== 'bio') finishEdit();
                if (e.key === 'Escape') this.innerHTML = originalContent;
            });
        });
    });

    // 2. Handle Image Upload
    const picUpload = document.getElementById('picUpload');
    const mainAvatar = document.getElementById('mainAvatar');
    const spinner = document.getElementById('uploadSpinner');

    picUpload.addEventListener('change', async function() {
        if (!this.files || !this.files[0]) return;

        const file = this.files[0];
        if (file.size > 2 * 1024 * 1024) {
            showToast("❌ File too large (Max 2MB)", true);
            return;
        }

        spinner.style.display = 'block';
        mainAvatar.style.opacity = '0.3';

        const formData = new FormData();
        formData.append('profile_pic', file);

        try {
            const resp = await fetch('api/upload_profile_pic.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();

            if (data.success) {
                mainAvatar.src = data.path + '?v=' + Date.now();
                showToast("✅ Profile picture updated!");
            } else {
                showToast("❌ " + data.message, true);
            }
        } catch (e) {
            showToast("❌ Upload failed", true);
        } finally {
            spinner.style.display = 'none';
            mainAvatar.style.opacity = '1';
        }
    });

    function showToast(msg, isError = false) {
        const toast = document.getElementById('statusToast');
        toast.innerText = msg;
        toast.style.borderColor = isError ? '#ef4444' : 'var(--primary)';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
});
</script>

<?php
// PHP BACKEND FOR INLINE UPDATES
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile_field'])) {
    ob_clean();
    header('Content-Type: application/json');
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    // Server-side safety
    if (!validateUpdate($field, $value)) {
        echo json_encode(['success' => false, 'message' => 'Validation failed.']);
        exit;
    }

    $allowed_fields = ['name', 'profession', 'country', 'bio', 'phone'];
    if (in_array($field, $allowed_fields)) {
        $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $user_id);
        if ($stmt->execute()) {
            if ($field === 'name') $_SESSION['user_name'] = $value;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    exit;
}
?>

<?php include "includes/footer.php"; ?>
