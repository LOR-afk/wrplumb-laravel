document.addEventListener('DOMContentLoaded', function () {
    const photoInput = document.getElementById('hrProfilePhotoInput');
    const preview = document.getElementById('hrProfilePhotoPreview');
    const removeButton = document.getElementById('hrRemoveProfilePhotoButton');
    const removeInput = document.getElementById('hrRemoveProfilePhoto');

    if (photoInput && preview) {
        photoInput.addEventListener('change', function () {
            const file = photoInput.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.addEventListener('load', function () {
                preview.innerHTML = `<img src="${reader.result}" alt="Profile preview">`;
                if (removeInput) removeInput.value = '0';
            });
            reader.readAsDataURL(file);
        });
    }

    if (removeButton && preview && removeInput) {
        removeButton.addEventListener('click', function () {
            removeInput.value = '1';
            if (photoInput) photoInput.value = '';

            const initials = preview.dataset.initials || 'HR';
            preview.innerHTML = `<span>${initials}</span>`;
        });
    }

    const settingsModal = document.getElementById('hrAccountSettingsModal');
    if (settingsModal && settingsModal.dataset.openOnError === '1') {
        bootstrap.Modal.getOrCreateInstance(settingsModal).show();
    }
});
