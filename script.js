// ===== Exercice 11 : JavaScript =====
document.addEventListener('DOMContentLoaded', () => {

    // Confirmation avant suppression
    document.querySelectorAll('.lien-suppr').forEach(lien => {
        lien.addEventListener('click', e => {
            if (!confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
                e.preventDefault();
            }
        });
    });

    // Recherche instantanée (sans recharger la page)
    const champ = document.querySelector('#recherche');
    if (champ) {
        champ.addEventListener('input', () => {
            const texte = champ.value.toLowerCase();
            document.querySelectorAll('tbody tr[data-nom]').forEach(ligne => {
                const nom = ligne.dataset.nom.toLowerCase();
                ligne.style.display = nom.includes(texte) ? '' : 'none';
            });
        });
    }
});
