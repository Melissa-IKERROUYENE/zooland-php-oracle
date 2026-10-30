// ================================================
// assets/js/main.js — Zoo'land 2026
// ================================================

// ---- Confirmation avant suppression ----
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Confirmer la suppression ?')) {
            e.preventDefault();
        }
    });
});

// ---- Fermeture automatique des alertes ----
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity .5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});

// ---- Recherche en direct sur les tableaux ----
const searchInputs = document.querySelectorAll('[data-search-table]');
searchInputs.forEach(input => {
    const tableId = input.dataset.searchTable;
    const table = document.getElementById(tableId);
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});

// ---- Toggle mot de passe ----
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? '👁️' : '🙈';
    });
});

// ---- Validation force mot de passe ----
const pwdInput = document.getElementById('mot_de_passe');
const pwdStrength = document.getElementById('pwd-strength');
if (pwdInput && pwdStrength) {
    pwdInput.addEventListener('input', () => {
        const v = pwdInput.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        const labels = ['', 'Faible', 'Moyen', 'Bon', 'Fort'];
        const colors = ['', '#c0392b', '#e67e22', '#f39c12', '#27ae60'];
        pwdStrength.textContent = v ? labels[score] : '';
        pwdStrength.style.color = v ? colors[score] : '';
    });
}

// ---- Confirmation changement de mot de passe ----
const pwdForm = document.getElementById('change-pwd-form');
if (pwdForm) {
    pwdForm.addEventListener('submit', e => {
        const p1 = document.getElementById('new_password').value;
        const p2 = document.getElementById('confirm_password').value;
        if (p1 !== p2) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
        }
    });
}
