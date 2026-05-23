function openModal(action, sigId, sigName, pid, pname, pemail) {
    const modal = document.getElementById('formModal');
    const title = document.getElementById('modalTitle');
    const submitText = document.getElementById('btnSubmitText');

    document.getElementById('sigContextName').textContent = sigName;
    document.getElementById('sig_id').value = sigId;
    document.getElementById('sig_name').value = sigName;
    document.getElementById('action_type').value = action;

    // Reset form content config
    const selectElement = document.getElementById('penyelaras_id');
    let tempOpt = document.getElementById('temp_current_user');
    if (tempOpt) tempOpt.remove(); // Safely remove tempo
    // rary option from previous edits

    document.getElementById('penyelarasForm').reset();

    if (action === 'add') {
        title.textContent = "Tambah Penyelaras";
        submitText.textContent = "Daftar Penyelaras";
    } else if (action === 'edit') {
        title.textContent = "Kemaskini Penyelaras";
        submitText.textContent = "Simpan Kemaskini";

        // Re-inject the current user into the dropdown so it shows up
        let option = document.createElement("option");
        option.id = 'temp_current_user';
        option.value = pid;
        option.text = pname + ' (' + pid + ') - Semasa';
        option.selected = true;
        selectElement.add(option, selectElement.options[1]);
    }

    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('formModal').classList.remove('active');
}

let currentDeletePid = null;

function openDeleteModal(sigName, pid) {
    currentDeletePid = pid;
    document.getElementById('deleteSigContextName').textContent = sigName;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    currentDeletePid = null;
    document.getElementById('deleteModal').classList.remove('active');
}

function confirmDelete() {
    if (!currentDeletePid) return;

    fetch('/penyelarasSigRegistration/delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id: currentDeletePid })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload dashboard
            } else {
                location.reload(); // Reload to show error from session
            }
        })
        .catch(err => {
            alert('Terdapat ralat semasa memproses.');
        });
}

function submitForm() {
    const id = document.getElementById('penyelaras_id').value;
    const action = document.getElementById('action_type').value;
    const sigId = document.getElementById('sig_id').value;

    if (!id) {
        alert("Sila pilih seorang Penyelaras!");
        return;
    }

    let fetchUrl = action === 'add' ? '/penyelarasSigRegistration/store' : '/penyelarasSigRegistration/update';
    let fetchMethod = action === 'add' ? 'POST' : 'PUT';

    fetch(fetchUrl, {
        method: fetchMethod,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            id: id,
            sig_id: sigId
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload(); // Quickly update UI with new data
            } else {
                location.reload(); // Reload to show error from session
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terdapat ralat teknikal. Sila hubungi admin.');
        });
}
