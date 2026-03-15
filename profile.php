<?php
include "config/db.php";
include "includes/auth_check.php";

$user_id = $_SESSION["user_id"];

// --- 1. PHP BACKEND FOR UPDATES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile_field'])) {
    ob_clean();
    header('Content-Type: application/json');
    $field = $_POST['field'];
    $value = trim($_POST['value']);
    
    $allowed_fields = ['name', 'profession', 'bio', 'phone'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        exit;
    }

    $isValid = true;
    if ($field === 'name') $isValid = preg_match("/^[a-zA-Z\s]+$/", $value);
    if ($field === 'phone') $isValid = empty($value) || preg_match("/^[0-9]{10}$/", $value);
    if ($field === 'profession') $isValid = empty($value) || preg_match("/^[a-zA-Z0-9\s\.\-]+$/", $value);

    if (!$isValid) {
        echo json_encode(['success' => false, 'message' => 'Validation failed']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $user_id);
    if ($stmt->execute()) {
        if ($field === 'name') $_SESSION['user_name'] = $value;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

$pageTitle = "Account Profile | StoXVision AI";
$currentPage = "profile";

$stmt = $conn->prepare("SELECT name, email, profile_pic, profession, bio, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include "includes/header.php";
?>

<div class="max-w-4xl mx-auto py-10 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <div class="glass-panel rounded-[48px] overflow-hidden border-white/5 shadow-2xl">
        
        <!-- Premium Header Area -->
        <div class="relative h-48 bg-gradient-to-r from-primary/20 via-blue-600/10 to-transparent">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
            <div class="absolute -bottom-16 left-12 flex items-end gap-8">
                <div class="relative group">
                    <?php 
                    $default_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=0ea5e9&color=fff';
                    $profile_pic_src = (!empty($user['profile_pic']) && strpos($user['profile_pic'], 'via.placeholder.com') === false) ? $user['profile_pic'] : $default_avatar;
                    ?>
                    <img id="mainAvatar" src="<?php echo $profile_pic_src; ?>" 
                         alt="Profile" class="w-36 h-36 rounded-[40px] border-4 border-dark object-cover shadow-2xl group-hover:scale-105 transition-transform duration-500">
                    <button onclick="document.getElementById('picUpload').click()" class="absolute inset-0 bg-black/40 rounded-[40px] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-camera text-white text-2xl"></i>
                    </button>
                    <input type="file" id="picUpload" hidden accept="image/*">
                </div>
                <div class="mb-2">
                    <h1 id="displayName" class="text-4xl font-black text-white tracking-tighter profile-field" data-field="name" data-validate="letters"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p id="displayProfession" class="text-primary font-bold uppercase tracking-widest text-xs mt-1 profile-field" data-field="profession" data-validate="alphanumeric"><?php echo htmlspecialchars($user['profession'] ?: 'Strategic Investor'); ?></p>
                </div>
            </div>

            <div class="absolute top-8 right-12 flex gap-4">
                <button id="editBtn" class="bg-white/10 hover:bg-white/20 text-white font-black px-6 py-3 rounded-2xl backdrop-blur-md border border-white/10 transition-all flex items-center gap-2">
                    <i class="fas fa-pen-nib text-xs"></i> Customize
                </button>
                <div id="editActions" class="hidden gap-3">
                    <button id="saveBtn" class="bg-primary hover:bg-primary/90 text-dark font-black px-8 py-3 rounded-2xl transition-all shadow-lg shadow-primary/20">Save</button>
                    <button id="cancelBtn" class="bg-white/5 hover:bg-white/10 text-white font-bold px-6 py-3 rounded-2xl border border-white/5">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Main Info Grid -->
        <div class="pt-24 pb-12 px-12 grid grid-cols-1 md:grid-cols-2 gap-10">
            
            <div class="space-y-8">
                <div class="group">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <i class="fas fa-envelope text-primary/50"></i> Verified Email
                    </label>
                    <div class="text-lg font-bold text-white/50 bg-white/[0.02] p-4 rounded-2xl border border-white/5">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <p class="text-[9px] text-slate-600 mt-2 italic">* Identity-linked. Cannot be changed.</p>
                </div>

                <div class="group">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <i class="fas fa-phone text-primary/50"></i> Secure Phone
                    </label>
                    <div id="displayPhone" class="text-lg font-bold text-white bg-white/[0.02] p-4 rounded-2xl border border-white/5 group-hover:border-white/10 transition-colors profile-field" data-field="phone" data-validate="numbers">
                        <?php echo htmlspecialchars($user['phone'] ?: 'Add digits'); ?>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="group h-full">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <i class="fas fa-quote-left text-primary/50"></i> Investor Biography
                    </label>
                    <div id="displayBio" class="text-sm leading-relaxed text-slate-300 bg-white/[0.02] p-6 rounded-3xl border border-white/5 group-hover:border-white/10 transition-colors h-[calc(100%-24px)] profile-field" data-field="bio" data-validate="all">
                        <?php echo nl2br(htmlspecialchars($user['bio'] ?: 'Define your market philosophy here...')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Footer -->
        <div class="px-12 py-8 bg-white/[0.02] border-t border-white/5 flex flex-wrap gap-12">
            <div>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</div>
                <div class="flex items-center gap-2 text-secondary font-black text-sm uppercase">
                    <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                    Verified Analyst
                </div>
            </div>
            <div>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Region</div>
                <div class="text-white font-black text-sm uppercase italic">India (Stock Market Hub)</div>
            </div>
            <div>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Cohort</div>
                <div class="text-white font-black text-sm uppercase">Early Adopter • 2024</div>
            </div>
        </div>
    </div>
</div>

<div id="statusToast" class="fixed bottom-10 right-10 z-[2000] px-8 py-4 rounded-2xl bg-dark border border-primary text-white font-bold transform translate-y-20 opacity-0 transition-all duration-300 hidden"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const editActions = document.getElementById('editActions');
    const profileFields = document.querySelectorAll('.profile-field');
    const toast = document.getElementById('statusToast');
    let originalValues = {};

    function toggleEditMode(isEditing) {
        editBtn.classList.toggle('hidden', isEditing);
        editActions.classList.toggle('hidden', !isEditing);
        editActions.classList.toggle('flex', isEditing);

        profileFields.forEach(field => {
            if (isEditing) {
                const key = field.dataset.field;
                originalValues[key] = field.innerText.trim();
                let currentValue = originalValues[key];

                if (currentValue.includes('Add digits') || currentValue.includes('Define your market philosophy')) {
                    currentValue = '';
                }

                const validateType = field.dataset.validate;
                let input;
                if (key === 'bio') {
                    input = document.createElement('textarea');
                    input.rows = 4;
                } else {
                    input = document.createElement('input');
                    input.type = 'text';
                }
                
                input.value = currentValue;
                input.className = 'w-full bg-white/5 border border-primary/30 rounded-xl px-4 py-2 text-white font-bold focus:outline-none focus:border-primary transition-colors';
                
                input.oninput = (e) => {
                    let val = e.target.value;
                    if (validateType === 'letters') val = val.replace(/[^a-zA-Z\s]/g, '');
                    if (validateType === 'numbers') val = val.replace(/[^0-9]/g, '').substring(0, 10);
                    if (validateType === 'alphanumeric') val = val.replace(/[^a-zA-Z0-9\s\.\-]/g, '');
                    e.target.value = val;
                };

                field.innerHTML = '';
                field.appendChild(input);
                field.classList.add('p-0');
                field.classList.remove('p-4', 'p-6');
            }
        });
    }

    editBtn.addEventListener('click', () => toggleEditMode(true));

    cancelBtn.addEventListener('click', () => {
        profileFields.forEach(field => {
            field.innerHTML = originalValues[field.dataset.field];
            field.classList.remove('p-0');
            if(field.dataset.field === 'bio') field.classList.add('p-6');
            else field.classList.add('p-4');
        });
        toggleEditMode(false);
    });

    saveBtn.addEventListener('click', async () => {
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';
        
        let hasError = false;
        const updates = [];
        
        profileFields.forEach(field => {
            const input = field.querySelector('input, textarea');
            if (input) {
                const newVal = input.value.trim();
                const fieldName = field.dataset.field;
                
                if (newVal !== originalValues[fieldName]) {
                    updates.push({ field: fieldName, value: newVal, element: field });
                } else {
                    field.innerHTML = originalValues[fieldName];
                }
            }
        });

        for (const update of updates) {
            try {
                const formData = new FormData();
                formData.append('field', update.field);
                formData.append('value', update.value);
                formData.append('update_profile_field', '1');

                const resp = await fetch('profile.php', { method: 'POST', body: formData });
                const data = await resp.json();

                if (data.success) {
                    update.element.innerHTML = update.value || (update.field === 'phone' ? 'Add digits' : 'Define philosophy...');
                } else {
                    hasError = true;
                    update.element.innerHTML = originalValues[update.field];
                }
            } catch (e) {
                hasError = true;
                update.element.innerHTML = originalValues[update.field];
            }
            update.element.classList.remove('p-0');
            if(update.field === 'bio') update.element.classList.add('p-6');
            else update.element.classList.add('p-4');
        }

        showToast(hasError ? "⚠️ Connection issue" : "✅ Profile synchronized");
        saveBtn.disabled = false;
        saveBtn.innerText = 'Save';
        toggleEditMode(false);
    });

    // Image Upload
    document.getElementById('picUpload').addEventListener('change', async function() {
        if (!this.files?.[0]) return;
        const file = this.files[0];
        
        document.getElementById('mainAvatar').classList.add('animate-pulse', 'opacity-50');
        
        const formData = new FormData();
        formData.append('profile_pic', file);

        try {
            const resp = await fetch('api/upload_profile_pic.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.success) {
                document.getElementById('mainAvatar').src = data.path + '?v=' + Date.now();
                showToast("✅ Identity updated");
            } else {
                showToast("❌ " + data.message);
            }
        } catch (e) {
            showToast("❌ Uplink failed");
        } finally {
            document.getElementById('mainAvatar').classList.remove('animate-pulse', 'opacity-50');
        }
    });

    function showToast(msg) {
        toast.innerText = msg;
        toast.classList.remove('hidden', 'translate-y-20', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    }
});
</script>

<?php include "includes/footer.php"; ?>
